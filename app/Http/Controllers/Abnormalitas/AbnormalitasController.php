<?php

namespace App\Http\Controllers\Abnormalitas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification;
use App\Models\Abnormal;

class AbnormalitasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sortOrder = $request->input('sortOrder', 'latest');
        $entries = $request->input('entries', 10);

        $query = Notification::where('user_id', auth()->id())
            ->with('abnormal');

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('notification_number', 'like', "%{$search}%")
                      ->orWhere('job_name', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', $sortOrder === 'latest' ? 'desc' : 'asc');
        $abnormalitas = $query->paginate($entries);

        return view('abnormalitas.index', compact('abnormalitas', 'search', 'sortOrder', 'entries'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'notification_number' => 'required|string',
            'abnormal_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:4096',
        ]);

        $path = $request->file('abnormal_file')->store('abnormalitas', 'public');

        $abnormal = Abnormal::where('notification_number', $request->notification_number)->first();

        if ($abnormal) {
            $files = json_decode($abnormal->files, true) ?? [];
            $files[] = [
                'file_path' => $path,
                'keterangan' => 'Upload manual',
            ];
            $abnormal->update([
                'files' => json_encode($files),
            ]);
        } else {
            Abnormal::create([
                'notification_number' => $request->notification_number,
                'files' => json_encode([[
                    'file_path' => $path,
                    'keterangan' => 'Upload manual',
                ]]),
            ]);
        }

        return redirect()->route('abnormalitas.index')
            ->with('success', 'File Abnormalitas berhasil diupload.');
    }

    /**
     * Lihat semua file abnormalitas dari notifikasi
     */
    public function viewFiles($notificationNumber)
    {
        $abnormal = Abnormal::where('notification_number', $notificationNumber)->first();

        if (!$abnormal || empty($abnormal->files)) {
            return redirect()->route('abnormalitas.index')
                ->with('error', 'File tidak ditemukan.');
        }

        $files = json_decode($abnormal->files, true) ?? [];

        return view('abnormalitas.files', compact('abnormal', 'files'));
    }

    /**
     * Download file berdasarkan index
     */
    public function downloadFile($notificationNumber, $index)
    {
        $abnormal = Abnormal::where('notification_number', $notificationNumber)->firstOrFail();
        $files = json_decode($abnormal->files, true);

        if (!isset($files[$index])) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        $filePath = $files[$index]['file_path'];

        if (!Storage::disk('public')->exists($filePath)) {
            return back()->with('error', 'File sudah tidak tersedia di storage.');
        }

        return Storage::disk('public')->download($filePath);
    }

    /**
     * Hapus file per item
     */
    public function destroyFile($notificationNumber, $index)
    {
        $abnormal = Abnormal::where('notification_number', $notificationNumber)->firstOrFail();
        $files = json_decode($abnormal->files, true);

        if (!isset($files[$index])) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        $filePath = $files[$index]['file_path'];

        // Hapus fisik file
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        // Hapus dari array
        unset($files[$index]);
        $abnormal->update([
            'files' => json_encode(array_values($files)), // reindex array
        ]);

        return back()->with('success', 'File berhasil dihapus.');
    }
}
