<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UnitWork;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Fungsi untuk menampilkan daftar pengguna berdasarkan usertype
    public function index(Request $request)
    {
        $usertype = $request->get('usertype', 'user');
        $jabatan = $request->get('jabatan');
    
        $query = User::where('usertype', $usertype);
    
        if ($usertype === 'approval' && $jabatan) {
            $query->where('jabatan', $jabatan);
        }
    
        $users = $query->get();
    $units = UnitWork::orderBy('name')->get();

        return view('admin.user.index', compact('users', 'usertype', 'units'));
    }
    
    // Fungsi untuk menampilkan form edit user
    public function edit($id)
    {
        // Mengambil data user berdasarkan ID
        $user = User::findOrFail($id);
        
        // Mengembalikan data dalam format JSON
        return response()->json($user);
    }

 public function update(Request $request, $id)
{
    // Debug data yang diterima
    logger($request->all());

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'usertype' => 'required|string',
        'departemen' => 'nullable|string',
        'unit_work' => 'nullable|string',
        'seksi' => 'nullable|string',
        'jabatan' => 'nullable|string',
        // Validasi fleksibel agar mutator bisa bekerja
        'whatsapp_number' => [
            'nullable',
            'string',
            'max:30',
            function ($attribute, $value, $fail) {
                $digits = preg_replace('/\D+/', '', (string) $value);
                if (strlen($digits) < 8 || strlen($digits) > 15) {
                    $fail('Nomor WhatsApp tidak valid.');
                }
            },
        ],
    ]);

    $user = User::findOrFail($id);

    // Log nomor lama (sebelum perubahan)
    $oldWhatsapp = $user->whatsapp_number;

    $user->update([
        'name'            => $request->input('name'),
        'email'           => $request->input('email'),
        'usertype'        => $request->input('usertype'),
        'departemen'      => $request->input('departemen'),
        'unit_work'       => $request->input('unit_work'),
        'seksi'           => $request->input('seksi'),
        'jabatan'         => $request->input('jabatan'),
        'whatsapp_number' => $request->input('whatsapp_number'),
        'initials'        => $request->input('initials'),
        'related_units'   => $request->filled('related_units')
            ? (is_array($request->input('related_units'))
                ? $request->input('related_units')
                : json_decode($request->input('related_units'), true))
            : [],
    ]);

    // Log nomor baru setelah kena mutator normalisasi
    logger()->info('[User Update] WhatsApp number changed', [
        'user_id'     => $user->id,
        'old_number'  => $oldWhatsapp,
        'new_number'  => $user->whatsapp_number,
    ]);

    return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
}


    
    // Fungsi untuk menghapus user
    public function destroy($id)
    {
        // Cari user berdasarkan ID
        $user = User::findOrFail($id);

        // Hapus user dari database
        $user->delete();

        // Redirect kembali ke halaman daftar user dengan pesan sukses
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
