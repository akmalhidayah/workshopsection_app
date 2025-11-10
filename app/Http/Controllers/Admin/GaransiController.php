<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Lpj;
use Carbon\Carbon;

class GaransiController extends Controller
{
    /**
     * Tampilkan daftar garansi.
     * Search hanya berdasarkan notification_number sesuai permintaan.
     */
    public function index(Request $request)
    {
        // 1) Eager load LHPP, filter pencarian hanya pada notification_number
        $query = Notification::whereHas('lhpp')->with('lhpp');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('notification_number', 'like', "%{$search}%");
        }

        // ambil koleksi (paginate jika diperlukan, sekarang get())
        $notifications = $query->orderBy('created_at', 'desc')->get();

        // 2) Batch-load LPJ untuk menghindari N+1
        $notificationNumbers = $notifications->pluck('notification_number')->unique()->toArray();
        $lpjMap = Lpj::whereIn('notification_number', $notificationNumbers)
                    ->get()
                    ->keyBy('notification_number');

        // 3) Build hasil list garansi
        $garansiList = $notifications->map(function ($notif) use ($lpjMap) {
            $lhpp = $notif->lhpp;
            $lpj  = $lpjMap[$notif->notification_number] ?? null;

            // --- gambar handling (bisa string JSON, array of strings, or array of objects with 'path') ---
            $gambarList = [];
            if (!empty($lhpp->images)) {
                $imagesData = $lhpp->images;

                if (is_string($imagesData)) {
                    $decoded = json_decode($imagesData, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $imagesData = $decoded;
                    }
                }

                if (is_array($imagesData)) {
                    foreach ($imagesData as $img) {
                        if (is_string($img) && $img !== '') {
                            $gambarList[] = str_replace(['\\', '//'], '/', ltrim($img, '/'));
                        } elseif (is_array($img) && !empty($img['path'])) {
                            $gambarList[] = str_replace(['\\', '//'], '/', ltrim($img['path'], '/'));
                        }
                    }
                }
            }

            // --- tentukan apakah LHPP memiliki 3 tanda tangan ---
            $has3Signatures = false;
            if ($lhpp) {
                // prefer method on model jika tersedia (lebih bersih)
                if (method_exists($lhpp, 'hasAllSignatures')) {
                    try {
                        $has3Signatures = (bool) $lhpp->hasAllSignatures();
                    } catch (\Throwable $e) {
                        $has3Signatures = false;
                    }
                } else {
                    // fallback: cek tiga kolom signature atau user_id
                    $a = !empty($lhpp->manager_signature) || !empty($lhpp->manager_signature_user_id);
                    $b = !empty($lhpp->manager_signature_requesting) || !empty($lhpp->manager_signature_requesting_user_id);
                    $c = !empty($lhpp->manager_pkm_signature) || !empty($lhpp->manager_pkm_signature_user_id);
                    $has3Signatures = $a && $b && $c;
                }
            }

          // --- start date (garansi hanya mulai jika 3 TTD lengkap) ---
$startDate = null;
if ($lhpp) {
    if ($has3Signatures) {
        // 3 tanda tangan lengkap → ambil tanggal_selesai jika ada
        if (!empty($lhpp->tanggal_selesai)) {
            try {
                $startDate = Carbon::parse($lhpp->tanggal_selesai);
            } catch (\Throwable $e) {
                $startDate = null;
            }
        }
    } else {
        // kalau 3 tanda tangan belum lengkap, jangan pakai tanggal_selesai
        $startDate = null;
    }
}

            // --- ambil jumlah bulan garansi dari LPJ (jika ada) ---
            $garansiMonths = null;
            if ($lpj && isset($lpj->garansi_months)) {
                $garansiMonths = (int) $lpj->garansi_months;
                if ($garansiMonths <= 0) $garansiMonths = null;
            }

            // --- hitung end date dan status ---
            $endDate = null;
            $status  = '-';
            if ($startDate && $garansiMonths) {
                // gunakan addMonthsNoOverflow supaya aman untuk tanggal akhir bulan
                $endDate = (clone $startDate)->addMonthsNoOverflow($garansiMonths);
                $now = Carbon::now();
                $status = $now->lessThanOrEqualTo($endDate) ? 'Masih Berlaku' : 'Habis';
            }

            return [
                'order_number'   => $notif->notification_number,
                'ttd_date'       => $startDate ? $startDate->format('d-m-Y') : '-',
                'start_date_raw' => $startDate ? $startDate->toDateString() : null,
                'end_date'       => $endDate ? $endDate->format('d-m-Y') : ($garansiMonths ? '-' : '-'),
                'end_date_raw'   => $endDate ? $endDate->toDateString() : null,
                'status'         => $status,
                'gambar'         => $gambarList,
                'garansi_months' => $garansiMonths,
                'garansi_label'  => $lpj->garansi_label ?? null,
                'lpj_present'    => (bool) $lpj,
                'has_3_ttd'      => $has3Signatures,
            ];
        });

        // optional: sort by start date desc (most recent ttd first)
        $garansiList = $garansiList->sortByDesc(function ($i) {
            return $i['start_date_raw'] ?? '0000-00-00';
        })->values();

        return view('admin.garansi.index', compact('garansiList'));
    }
}
