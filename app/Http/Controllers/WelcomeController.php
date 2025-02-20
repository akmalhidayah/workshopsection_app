<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class WelcomeController extends Controller
{
    public function index()
    {
        // Ambil file berdasarkan kategori
        $caraKerjaFiles = Storage::disk('public')->files('uploads/info/cara_kerja');
        $flowchartFiles = Storage::disk('public')->files('uploads/info/flowchart_aplikasi');
        $kontrakFiles = Storage::disk('public')->files('uploads/info/kontrak_pkm');
        
        // Kirim data ke view
        return view('welcome', compact('caraKerjaFiles', 'flowchartFiles', 'kontrakFiles'));
    }
}
