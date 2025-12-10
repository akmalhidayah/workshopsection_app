<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Lpj;
use App\Models\Garansi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GaransiController extends Controller
{
    /**
     * Tampilkan daftar garansi.
     * Search hanya berdasarkan notification_number sesuai permintaan.
     */
    public function index(Request $request)
    {
        try {
            // 1) Eager load LHPP, filter pencarian hanya pada notification_number
            $query = Notification::whereHas('lhpp')->with('lhpp');

            if ($request->filled('search')) {
                $search = trim($request->input('search'));
                $query->where('notification_number', 'like', "%{$search}%");
            }

            // ambil koleksi (paginate jika diperlukan, sekarang get())
            $notifications = $query->orderBy('created_at', 'desc')->get();

            // 2) Batch-load LPJ dan Garansi untuk menghindari N+1
            $notificationNumbers = $notifications->pluck('notification_number')->unique()->toArray();

            $lpjMap = Lpj::whereIn('notification_number', $notificationNumbers)
                        ->get()
                        ->keyBy('notification_number');

            $garansiMap = Garansi::whereIn('notification_number', $notificationNumbers)
                        ->get()
                        ->keyBy('notification_number');

            // 3) Build hasil list garansi
            $garansiList = $notifications->map(function ($notif) use ($lpjMap, $garansiMap) {
                $lhpp = $notif->lhpp;
                $lpj  = $lpjMap[$notif->notification_number] ?? null;
                $garansi = $garansiMap[$notif->notification_number] ?? null;

                // --- gambar handling (bisa string JSON, array of strings, or array of objects with 'path') ---
                $gambarList = [];
                if (!empty($lhpp->images)) {
                    $imagesData = $lhpp->images;

                    // jika string encoded JSON -> decode
                    if (is_string($imagesData)) {
                        $decoded = json_decode($imagesData, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $imagesData = $decoded;
                        }
                    }

                    if (is_array($imagesData)) {
                        foreach ($imagesData as $img) {
                            if (is_string($img) && $img !== '') {
                                $gambarList[] = str_replace(['\\', '//'], '/', ltrim($img, '/'));
                            } elseif (is_array($img) && !empty($img['path'])) {
                                $gambarList[] = str_replace(['\\', '//'], '/', ltrim($img['path'], '/'));
                            } elseif (is_object($img) && !empty($img->path)) {
                                $gambarList[] = str_replace(['\\', '//'], '/', ltrim($img->path, '/'));
                            }
                        }
                    }
                }

                // --- tentukan apakah LHPP memiliki 3 tanda tangan ---
                $has3Signatures = false;
                if ($lhpp) {
                    if (method_exists($lhpp, 'hasAllSignatures')) {
                        try {
                            $has3Signatures = (bool) $lhpp->hasAllSignatures();
                        } catch (\Throwable $e) {
                            $has3Signatures = false;
                        }
                    } else {
                        $a = !empty($lhpp->manager_signature) || !empty($lhpp->manager_signature_user_id);
                        $b = !empty($lhpp->manager_signature_requesting) || !empty($lhpp->manager_signature_requesting_user_id);
                        $c = !empty($lhpp->manager_pkm_signature) || !empty($lhpp->manager_pkm_signature_user_id);
                        $has3Signatures = ($a && $b && $c);
                    }
                }

                // --- start date (garansi hanya mulai jika 3 TTD lengkap) ---
                $startDate = null;
                if ($lhpp && $has3Signatures && !empty($lhpp->tanggal_selesai)) {
                    try {
                        $startDate = Carbon::parse($lhpp->tanggal_selesai)->startOfDay();
                    } catch (\Throwable $e) {
                        $startDate = null;
                    }
                }

                // --- ambil jumlah bulan garansi dari tabel garansis (jika ada)
                // treat 0 as valid value (user requested 0 months allowed)
                $garansiMonths = null;
                $garansiLabel = null;
                if ($garansi) {
                    // keep null vs 0 distinction
                    $garansiMonths = ($garansi->garansi_months === null) ? null : (int) $garansi->garansi_months;
                    $garansiLabel = $garansi->garansi_label ?? null;
                }

                // --- hitung end date dan status ---
                $endDate = null;
                $status  = '-';

                if ($garansiMonths !== null) {
                    // ada pengaturan garansi (termasuk 0)
                    if ($startDate !== null) {
                        if ($garansiMonths === 0) {
                            // 0 => tanpa garansi (langsung habis)
                            $endDate = (clone $startDate);
                            $now = Carbon::now()->startOfDay();
                            $status = $now->lessThanOrEqualTo($endDate) ? 'Masih Berlaku' : 'Habis';
                            // but semantik: user ingin 0 => "tanpa garansi", kita tampilkan 'Habis'
                            $status = 'Habis';
                        } else {
                            // months > 0 => hitung endDate
                            try {
                                $endDate = (clone $startDate)->addMonthsNoOverflow($garansiMonths);
                            } catch (\Throwable $e) {
                                try {
                                    $endDate = (clone $startDate)->addMonths($garansiMonths);
                                } catch (\Throwable $e2) {
                                    $endDate = null;
                                }
                            }

                            if ($endDate !== null) {
                                $now = Carbon::now()->startOfDay();
                                $status = $now->lessThanOrEqualTo($endDate) ? 'Masih Berlaku' : 'Habis';
                            } else {
                                $status = '-';
                            }
                        }
                    } else {
                        // ada nilai garansi, tapi startDate belum tersedia karena TTD belum lengkap
                        // jangan langsung tetapkan 'Habis' — tampilkan '-' agar admin tahu TTD belum lengkap
                        $status = '-';
                    }
                } else {
                    // tidak ada info garansi sama sekali -> '-'
                    $status = '-';
                }

                return [
                    'order_number'   => $notif->notification_number,
                    'ttd_date'       => $startDate ? $startDate->format('d-m-Y') : '-',
                    'start_date_raw' => $startDate ? $startDate->toDateString() : null,
                    'end_date'       => $endDate ? $endDate->format('d-m-Y') : ($garansiMonths !== null ? '-' : '-'),
                    'end_date_raw'   => $endDate ? $endDate->toDateString() : null,
                    'status'         => $status,
                    'gambar'         => $gambarList,
                    // include garansi_months even if 0 so blade can recognise 0
                    'garansi_months' => $garansiMonths,
                    'garansi_label'  => $garansiLabel,
                    'garansi_present'=> (bool) $garansi,
                    'lpj_present'    => (bool) $lpj,
                    'has_3_ttd'      => $has3Signatures,
                ];
            });

            // optional: sort by start date desc (most recent ttd first)
            $garansiList = $garansiList->sortByDesc(function ($i) {
                return $i['start_date_raw'] ?? '0000-00-00';
            })->values();

            // Render view
            return view('admin.garansi.index', compact('garansiList'));
        } catch (\Throwable $e) {
            // Log lengkap untuk debugging (file laravel.log)
            Log::error('GaransiController@index failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            // Tampilkan view error 500 (bisa disesuaikan dengan view error projectmu)
            return response()->view('errors.500', [
                'message' => 'Terjadi kesalahan saat memuat daftar garansi. Silakan cek log untuk detail.'
            ], 500);
        }
    }
}
