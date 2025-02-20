<?php

namespace App\Http\Controllers;

use App\Models\GambarTeknik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GambarTeknikController extends Controller
{
    /**
     * Mengunggah dokumen Gambar Teknik.
     */
    public function uploadDokumen(Request $request)
    {
        // Validasi input request
        $request->validate([
            'dokumen' => 'required|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'notification_number' => 'required|exists:notifications,notification_number',
        ]);

        try {
            $file = $request->file('dokumen');
            $filename = basename($file->store('public/gambarteknik'));

            // Cari record berdasarkan notification_number
            $gambarTeknik = GambarTeknik::where('notification_number', $request->notification_number)->first();

            if ($gambarTeknik) {
                // Jika ada file lama, hapus dari storage
                if (Storage::exists('public/gambarteknik/' . $gambarTeknik->file_path)) {
                    Storage::delete('public/gambarteknik/' . $gambarTeknik->file_path);
                }

                // Update file_path dengan nama file baru
                $gambarTeknik->file_path = $filename;
                $gambarTeknik->save();
            } else {
                // Jika record belum ada, buat baru
                GambarTeknik::create([
                    'notification_number' => $request->notification_number,
                    'file_path' => $filename,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Dokumen berhasil diunggah', 'file_path' => $filename]);
        } catch (\Exception $e) {
            \Log::error('Upload Dokumen Gagal: ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }

    /**
     * Melihat dokumen Gambar Teknik berdasarkan nomor notifikasi.
     */
    public function viewDokumen($notificationNumber)
    {
        try {
            $gambarTeknik = GambarTeknik::where('notification_number', $notificationNumber)->firstOrFail();
            $path = storage_path('app/public/gambarteknik/' . $gambarTeknik->file_path);

            if (!file_exists($path)) {
                abort(404, 'File not found.');
            }

            return response()->file($path);
        } catch (\Exception $e) {
            \Log::error('Error fetching dokumen: ' . $e->getMessage());
            return abort(500, 'Terjadi kesalahan dalam menampilkan dokumen.');
        }
    }

    /**
     * Menghapus dokumen Gambar Teknik berdasarkan nomor notifikasi.
     */
    public function hapusDokumen($notificationNumber)
    {
        try {
            // Cari data gambar teknik berdasarkan nomor notifikasi
            $gambarTeknik = GambarTeknik::where('notification_number', $notificationNumber)->firstOrFail();

            // Hapus file dari storage
            if (Storage::exists('public/gambarteknik/' . $gambarTeknik->file_path)) {
                Storage::delete('public/gambarteknik/' . $gambarTeknik->file_path);
            }

            // Hapus data dari database
            $gambarTeknik->delete();

            return back()->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Exception $e) {
            \Log::error('Gagal menghapus dokumen: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus dokumen.');
        }
    }
}
