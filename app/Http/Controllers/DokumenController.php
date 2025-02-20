<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DokumenController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'dokumen' => 'required|file|mimes:pdf,doc,docx,txt',
        ]);

        $path = $request->file('dokumen')->store('dokumen-teknik', 'public');

        // Simpan path file ke dalam database jika perlu
        // $yourModel->update(['file_path' => $path]);

        return response()->json([
            'message' => 'Dokumen berhasil diupload.',
            'file_path' => $path
        ]);
    }
}