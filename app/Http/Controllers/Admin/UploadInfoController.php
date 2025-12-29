<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UploadInfoController extends Controller
{
    private const BASE_PATH = 'uploads/info';

    /**
     * =========================
     * INDEX
     * =========================
     */
    public function index()
    {
        $data = [
            // Cara Kerja per Role
            'caraKerja' => [
                'pns'      => Storage::disk('public')->files(self::BASE_PATH . '/cara_kerja/pns'),
                'pkm'      => Storage::disk('public')->files(self::BASE_PATH . '/cara_kerja/pkm'),
                'approval' => Storage::disk('public')->files(self::BASE_PATH . '/cara_kerja/approval'),
            ],

            // Dokumen lain
            'flowchartFiles' => Storage::disk('public')->files(self::BASE_PATH . '/flowchart_aplikasi'),
            'kontrakFiles'   => Storage::disk('public')->files(self::BASE_PATH . '/kontrak_pkm'),
        ];

        return view('admin.uploadinfo.index', $data)
            ->with('status_code', Response::HTTP_OK);
    }

    /**
     * =========================
     * UPLOAD FILE
     * =========================
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'files.*' => 'required|file|max:10240',
            'category' => 'required|in:cara_kerja,flowchart_aplikasi,kontrak_pkm',
            'role' => 'nullable|required_if:category,cara_kerja|in:pns,pkm,approval',
        ]);

        if (!$request->hasFile('files')) {
            Log::warning('[UPLOAD INFO] Tidak ada file yang dikirim');
            return back()->withErrors('File tidak ditemukan')
                         ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        foreach ($request->file('files') as $file) {
            $filename = time() . '_' . $file->getClientOriginalName();

            $path = self::BASE_PATH . '/' . $validated['category'];

            if ($validated['category'] === 'cara_kerja') {
                $path .= '/' . $validated['role'];
            }

            $file->storeAs($path, $filename, 'public');

            Log::info('[UPLOAD INFO] File berhasil diupload', [
                'category' => $validated['category'],
                'role'     => $validated['role'] ?? null,
                'filename' => $filename,
                'path'     => $path,
                'user_id'  => auth()->id(),
            ]);
        }

        return back()
            ->with('success', 'Dokumen berhasil diunggah')
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * =========================
     * DELETE FILE
     * =========================
     */
    public function delete(Request $request, string $filename)
    {
        $validated = $request->validate([
            'category' => 'required|in:cara_kerja,flowchart_aplikasi,kontrak_pkm',
            'role' => 'nullable|required_if:category,cara_kerja|in:pns,pkm,approval',
        ]);

        $path = self::BASE_PATH . '/' . $validated['category'];

        if ($validated['category'] === 'cara_kerja') {
            $path .= '/' . $validated['role'];
        }

        $fullPath = $path . '/' . $filename;

        if (!Storage::disk('public')->exists($fullPath)) {
            Log::warning('[UPLOAD INFO] File tidak ditemukan saat delete', [
                'path' => $fullPath
            ]);

            return back()
                ->withErrors('File tidak ditemukan')
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        Storage::disk('public')->delete($fullPath);

        Log::info('[UPLOAD INFO] File dihapus', [
            'filename' => $filename,
            'path'     => $fullPath,
            'user_id'  => auth()->id(),
        ]);

        return back()
            ->with('success', 'Dokumen berhasil dihapus')
            ->setStatusCode(Response::HTTP_OK);
    }
}
