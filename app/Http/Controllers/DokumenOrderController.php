<?php

namespace App\Http\Controllers;

use App\Models\DokumenOrder;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DokumenOrderController extends Controller
{
    /**
     * Tampilkan semua dokumen per notification_number
     */
    public function index(Request $request)
    {
        try {
            $query = Notification::with('dokumenOrders')->latest();

            // Kalau bukan admin → hanya notifikasi miliknya
            if (auth()->user()->usertype !== 'admin') {
                $query->where('user_id', auth()->id());
            }

            // support optional entries per page (default 10) tanpa mengubah logic
            $perPage = (int) $request->input('entries', 10);
            $perPage = $perPage > 0 ? $perPage : 10;

            $notifications = $query->paginate($perPage)->withQueryString();

            return view('dokumen_orders.index', compact('notifications'));
        } catch (\Exception $e) {
            Log::error('DokumenOrderController@index Exception: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'user_id' => auth()->id() ?? null,
                'request' => $request->all(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Gagal memuat daftar dokumen.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return redirect()->back()->with('error', 'Gagal memuat daftar dokumen.');
        }
    }

    /**
     * Upload dokumen (abnormalitas / gambar teknik)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'notification_number' => 'required|string|exists:notifications,notification_number',
            'jenis_dokumen'       => 'required|in:abnormalitas,gambar_teknik',
            'dokumen_file'        => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:4096',
            'keterangan'          => 'nullable|string|max:255',
        ]);

        try {
            // handle file store
            $file = $request->file('dokumen_file');
            $path = $file->store('dokumen_orders', 'public');

            // jika sudah ada record sebelumnya, hapus file lama (safely)
            $existing = DokumenOrder::where('notification_number', $request->notification_number)
                ->where('jenis_dokumen', $request->jenis_dokumen)
                ->first();

            if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
                try {
                    Storage::disk('public')->delete($existing->file_path);
                } catch (\Exception $e) {
                    // log dan lanjutkan (tidak menggagalkan upload)
                    Log::warning('DokumenOrderController@upload: gagal hapus file lama', [
                        'notification_number' => $request->notification_number,
                        'jenis_dokumen' => $request->jenis_dokumen,
                        'file' => $existing->file_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // update atau create record
            $dok = DokumenOrder::updateOrCreate(
                [
                    'notification_number' => $request->notification_number,
                    'jenis_dokumen'       => $request->jenis_dokumen,
                ],
                [
                    'file_path'  => $path,
                    'keterangan' => $request->keterangan ?? ucfirst($request->jenis_dokumen),
                ]
            );

            // If AJAX/fetch request => return JSON so frontend can show SweetAlert
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Dokumen berhasil diupload.',
                    'data' => [
                        'notification_number' => $dok->notification_number,
                        'jenis_dokumen' => $dok->jenis_dokumen,
                        'file_path' => $dok->file_path,
                    ],
                ], Response::HTTP_OK);
            }

            return back()->with('success', 'Dokumen berhasil diupload.');
        } catch (\Exception $e) {
            Log::error('DokumenOrderController@upload Exception: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'input' => $request->except('dokumen_file'),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat mengupload dokumen.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return back()->with('error', 'Terjadi kesalahan saat mengupload dokumen.');
        }
    }

    /**
     * Lihat dokumen
     */
    public function view($notificationNumber, $jenis)
    {
        try {
            $dokumen = DokumenOrder::where('notification_number', $notificationNumber)
                ->where('jenis_dokumen', $jenis)
                ->first();

            if (!$dokumen) {
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json(['error' => 'Dokumen tidak ditemukan.'], Response::HTTP_NOT_FOUND);
                }
                return back()->with('error', 'Dokumen tidak ditemukan.');
            }

            if (in_array($jenis, ['abnormalitas', 'gambar_teknik'])) {
                if (!$dokumen->file_path || !Storage::disk('public')->exists($dokumen->file_path)) {
                    if (request()->wantsJson() || request()->ajax()) {
                        return response()->json(['error' => 'File tidak tersedia.'], Response::HTTP_NOT_FOUND);
                    }
                    return back()->with('error', 'File tidak tersedia.');
                }

                return response()->file(storage_path('app/public/' . $dokumen->file_path));
            }

            // Scope of Work → arahkan ke ScopeOfWorkController (tetap sama)
            return redirect()->route('scopeofwork.view', $notificationNumber);
        } catch (\Exception $e) {
            Log::error('DokumenOrderController@view Exception: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'notification' => $notificationNumber,
                'jenis' => $jenis,
            ]);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat mengambil dokumen.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return back()->with('error', 'Terjadi kesalahan saat mengambil dokumen.');
        }
    }

    /**
     * Hapus dokumen
     */
    public function destroy($notificationNumber, $jenis)
    {
        try {
            $dokumen = DokumenOrder::where('notification_number', $notificationNumber)
                ->where('jenis_dokumen', $jenis)
                ->firstOrFail();

            // hapus file fisik jika ada
            if (in_array($jenis, ['abnormalitas', 'gambar_teknik']) && $dokumen->file_path) {
                if (Storage::disk('public')->exists($dokumen->file_path)) {
                    Storage::disk('public')->delete($dokumen->file_path);
                } else {
                    Log::warning('DokumenOrderController@destroy: file not found when deleting', [
                        'file' => $dokumen->file_path,
                        'notification_number' => $notificationNumber,
                        'jenis' => $jenis,
                    ]);
                }
            }

            $dokumen->delete();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['message' => 'Dokumen berhasil dihapus.'], Response::HTTP_OK);
            }

            return back()->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('DokumenOrderController@destroy NotFound: ' . $notificationNumber . ' / ' . $jenis);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'Dokumen tidak ditemukan.'], Response::HTTP_NOT_FOUND);
            }

            return back()->with('error', 'Dokumen tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('DokumenOrderController@destroy Exception: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'notification' => $notificationNumber,
                'jenis' => $jenis,
            ]);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat menghapus dokumen.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return back()->with('error', 'Terjadi kesalahan saat menghapus dokumen.');
        }
    }
}
