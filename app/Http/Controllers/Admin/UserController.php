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
            'whatsapp_number' => 'nullable|regex:/^[0-9]{10,15}$/',
        ]);
    
        $user = User::findOrFail($id);
    
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
