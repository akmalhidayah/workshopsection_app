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
        $search = $request->get('search');
    
        $query = User::where('usertype', $usertype);
    
        if ($usertype === 'approval' && $jabatan) {
            $query->where('jabatan', $jabatan);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('departemen', 'like', '%'.$search.'%')
                    ->orWhere('unit_work', 'like', '%'.$search.'%')
                    ->orWhere('seksi', 'like', '%'.$search.'%')
                    ->orWhere('jabatan', 'like', '%'.$search.'%');
            });
        }
    
        $users = $query->orderBy('name')->get();
        $units = UnitWork::orderBy('name')->get();

        return view('admin.user.index', compact('users', 'usertype', 'units', 'search'));
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

    $redirectParams = [
        'usertype' => $request->input('redirect_usertype', 'user'),
        'jabatan' => $request->input('redirect_jabatan'),
        'search' => $request->input('redirect_search'),
    ];

    return redirect()
        ->route('admin.users.index', array_filter($redirectParams, fn ($v) => $v !== null && $v !== ''))
        ->with('success', 'User berhasil diperbarui.');
}


    
    // Fungsi untuk menghapus user
    public function destroy(Request $request, $id)
    {
        // Cari user berdasarkan ID
        $user = User::findOrFail($id);

        // Hapus user dari database
        $user->delete();

        // Redirect kembali ke halaman daftar user dengan pesan sukses
        $redirectParams = [
            'usertype' => $request->input('redirect_usertype', 'user'),
            'jabatan' => $request->input('redirect_jabatan'),
            'search' => $request->input('redirect_search'),
        ];

        return redirect()
            ->route('admin.users.index', array_filter($redirectParams, fn ($v) => $v !== null && $v !== ''))
            ->with('success', 'User berhasil dihapus.');
    }
}
