<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadInfoController extends Controller
{
    public function index()
    {
        // Ambil file berdasarkan kategori
        $caraKerjaFiles = Storage::disk('public')->files('uploads/info/cara_kerja');
        $flowchartFiles = Storage::disk('public')->files('uploads/info/flowchart_aplikasi');
        $kontrakFiles = Storage::disk('public')->files('uploads/info/kontrak_pkm');
    
        return view('admin.uploadinfo.index', compact('caraKerjaFiles', 'flowchartFiles', 'kontrakFiles'));
    }
    
    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'file|max:10240',
            'category' => 'required|in:cara_kerja,flowchart_aplikasi,kontrak_pkm',
        ]);
    
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $file->storeAs('uploads/info/' . $request->category, $filename, 'public');
            }
        }
    
        return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
    }
    
    public function delete(Request $request, $filename)
    {
        $request->validate([
            'category' => 'required|in:cara_kerja,flowchart_aplikasi,kontrak_pkm',
        ]);
    
        Storage::disk('public')->delete('uploads/info/' . $request->category . '/' . $filename);
        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }
    public function welcome()
{
    $caraKerjaFiles = Storage::files('cara_kerja');
    $flowchartFiles = Storage::files('flowchart_aplikasi');
    $kontrakFiles = Storage::files('kontrak_pkm');

    return view('welcome', compact('caraKerjaFiles', 'flowchartFiles', 'kontrakFiles'));
}

}
