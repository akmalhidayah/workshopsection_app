<?php

namespace App\Http\Controllers\ScopeOfWork;

use App\Http\Controllers\Controller;
use App\Models\ScopeOfWork;
use App\Models\Notification;
use Illuminate\Http\Request;

class ScopeOfWorkController extends Controller
{
    public function index()
    {
        // Ambil semua data Scope of Work dari database
        $scopeOfWorks = ScopeOfWork::all();

        // Tampilkan view index dengan data Scope of Work
        return view('abnormalitas.index', compact('scopeOfWorks'));
    }

    public function create($notificationNumber)
    {
        // Ambil data notifikasi berdasarkan nomor notifikasi
        $notification = Notification::where('notification_number', $notificationNumber)->firstOrFail();

        // Kirim data notifikasi ke view create
        return view('scopeofwork.create', compact('notification'));
    }

    // Store Function
    public function store(Request $request)
    {
        $request->validate([
            'notification_number' => 'required|string|max:255|unique:scope_of_works,notification_number',
            'nama_pekerjaan' => 'required|string|max:255',
            'unit_kerja' => 'required|string|max:255',
            'tanggal_pemakaian' => 'nullable|date',
            'tanggal_dokumen' => 'required|date',
            'scope_pekerjaan' => 'required|array',
            'scope_pekerjaan.*' => 'string|max:255',
            'qty' => 'required|array',
            'qty.*' => 'string|max:255',
            'satuan' => 'required|array',
            'satuan.*' => 'string|max:255',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'string|max:255',
            'catatan' => 'required|string',
            'nama_penginput' => 'nullable|string|max:255',
            'tanda_tangan' => 'nullable|string',
        ]);

        // Simpan data ke database
        ScopeOfWork::create([
            'notification_number' => $request->notification_number,
            'nama_pekerjaan' => $request->nama_pekerjaan,
            'unit_kerja' => $request->unit_kerja,
            'tanggal_pemakaian' => $request->tanggal_pemakaian,
            'tanggal_dokumen' => $request->tanggal_dokumen,
            'scope_pekerjaan' => json_encode($request->scope_pekerjaan),
            'qty' => json_encode($request->qty),
            'satuan' => json_encode($request->satuan),
            'keterangan' => json_encode($request->keterangan),
            'catatan' => $request->catatan,
            'nama_penginput' => $request->nama_penginput,
            'tanda_tangan' => $request->tanda_tangan,
        ]);

        return redirect()->route('abnormalitas.index')->with('success', 'Scope of Work berhasil disimpan!');
    }

    // Edit Function
    public function edit($notificationNumber)
    {
        // Temukan Scope of Work berdasarkan nomor notifikasi
        $scopeOfWork = ScopeOfWork::where('notification_number', $notificationNumber)->firstOrFail();

        // Temukan notifikasi terkait berdasarkan nomor notifikasi
        $notification = Notification::where('notification_number', $scopeOfWork->notification_number)->first();

        // Decode field JSON kembali menjadi array
        $scopeOfWork->scope_pekerjaan = json_decode($scopeOfWork->scope_pekerjaan, true);
        $scopeOfWork->qty = json_decode($scopeOfWork->qty, true);
        $scopeOfWork->satuan = json_decode($scopeOfWork->satuan, true);
        $scopeOfWork->keterangan = json_decode($scopeOfWork->keterangan, true);

        // Jika notifikasi tidak ditemukan, redirect dengan pesan error
        if (!$notification) {
            return redirect()->route('abnormalitas.index')->with('error', 'Notifikasi terkait tidak ditemukan.');
        }

        // Tampilkan view edit dengan data scopeOfWork dan notification
        return view('scopeofwork.edit', compact('scopeOfWork', 'notification'));
    }

    // Update Function
    public function update(Request $request, $notificationNumber)
    {
        $request->validate([
            'notification_number' => 'required|string|max:255',
            'nama_pekerjaan' => 'required|string|max:255',
            'unit_kerja' => 'required|string|max:255',
            'tanggal_pemakaian' => 'nullable|date',
            'tanggal_dokumen' => 'required|date',
            'scope_pekerjaan' => 'required|array',
            'scope_pekerjaan.*' => 'string|max:255',
            'qty' => 'required|array',
            'qty.*' => 'string|max:255',
            'satuan' => 'required|array',
            'satuan.*' => 'string|max:255',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'string|max:255',
            'catatan' => 'required|string',
            'nama_penginput' => 'nullable|string|max:255',
            'tanda_tangan' => 'nullable|string',
        ]);

        $scopeOfWork = ScopeOfWork::where('notification_number', $notificationNumber)->firstOrFail();

        $scopeOfWork->update([
            'notification_number' => $request->notification_number,
            'nama_pekerjaan' => $request->nama_pekerjaan,
            'unit_kerja' => $request->unit_kerja,
            'tanggal_pemakaian' => $request->tanggal_pemakaian,
            'tanggal_dokumen' => $request->tanggal_dokumen,
            'scope_pekerjaan' => json_encode($request->scope_pekerjaan),
            'qty' => json_encode($request->qty),
            'satuan' => json_encode($request->satuan),
            'keterangan' => json_encode($request->keterangan),
            'catatan' => $request->catatan,
            'nama_penginput' => $request->nama_penginput,
            'tanda_tangan' => $request->tanda_tangan,
        ]);

        return redirect()->route('abnormalitas.index')->with('success', 'Scope of Work berhasil diupdate!');
    }

    // Save Signature Function
    public function saveSignature(Request $request)
    {
        $request->validate([
            'scope_of_work_id' => 'required|exists:scope_of_works,notification_number',
            'tanda_tangan' => 'required',
        ]);

        $scopeOfWork = ScopeOfWork::where('notification_number', $request->scope_of_work_id)->firstOrFail();
        $scopeOfWork->tanda_tangan = $request->tanda_tangan;
        $scopeOfWork->save();

        return response()->json(['message' => 'Signature saved successfully!']);
    }

    // Show Function
    public function show($notificationNumber)
    {
        $scopeOfWork = ScopeOfWork::where('notification_number', $notificationNumber)->firstOrFail();

        // Decode JSON fields
        $scopeOfWork->scope_pekerjaan = json_decode($scopeOfWork->scope_pekerjaan, true);
        $scopeOfWork->qty = json_decode($scopeOfWork->qty, true);
        $scopeOfWork->satuan = json_decode($scopeOfWork->satuan, true);
        $scopeOfWork->keterangan = json_decode($scopeOfWork->keterangan, true);

        return view('scopeofwork.view', compact('scopeOfWork'));
    }
}



