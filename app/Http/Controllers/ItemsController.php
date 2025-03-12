<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Abnormal;
use App\Models\Hpp1;
use App\Models\User;



class ItemsController extends Controller
{
    public function index()
    {
        $user = auth()->user(); // Ambil data user yang sedang login
    
        \Log::info('User Data:', [
            'name' => $user->name,
            'jabatan' => $user->jabatan,
            'departemen' => $user->departemen
        ]);
    
        // Ambil semua item
        $items = Item::all();
    
        // Kirim data user ke Blade agar bisa digunakan di tampilan
        return view('pkm.items.index', compact('items', 'user'));
    }
    

    public function getItemData($notification_number)
    {
        // Ambil data Notification berdasarkan notification_number
        $notification = Notification::with('abnormal')->where('notification_number', $notification_number)->first();
        
        // Ambil data HPP berdasarkan notification_number
        $hpp = Hpp1::where('notification_number', $notification_number)->first();

        if (!$notification) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'deskripsi_pekerjaan' => $notification->abnormal->abnormal_title ?? 'Tidak tersedia',
            'total_hpp' => (int) ($hpp->total_amount ?? 0), // Mengubah menjadi integer agar menghilangkan .00
        ]);               
    }

    // Menampilkan form tambah item
    public function create()
    {
        // Ambil notification_number yang sudah ada di tabel items
        $existingItems = Item::pluck('notification_number');
    
        // Ambil notifikasi yang sudah memiliki dokumen PO dan PO number
        $notifications = Notification::whereNotIn('notification_number', $existingItems)
            ->whereHas('purchaseOrder', function ($query) {
                $query->whereNotNull('purchase_order_number'); // Perbaikan kolom
            })
            ->get();
    
        return view('pkm.items.create', compact('notifications'));
    }
    
    public function store(Request $request)
    {
        $notification = Notification::findOrFail($request->notification_number);
        $abnormal = Abnormal::where('notification_number', $notification->notification_number)->first();
        $hpp = Hpp1::where('notification_number', $notification->notification_number)->first();
    
        $total_hpp = $hpp->total_amount ?? 0;
        $materials = $request->input('material', []);
        $harga = array_map('floatval', $request->input('harga', []));
    
        $total = array_sum($harga);
        $total_margin = $total_hpp - $total;
    
        // Simpan Item ke database
        $item = Item::create([
            'notification_number' => $notification->notification_number,
            'deskripsi_pekerjaan' => $abnormal->abnormal_title ?? 'Tidak Ada Data',
            'total_hpp' => $total_hpp,
            'materials' => $materials, // Laravel otomatis menyimpan sebagai JSON
            'harga' => $harga, // Laravel otomatis menyimpan sebagai JSON
            'total' => $total,
            'total_margin' => $total_margin,
            'approved_by' => 'Pending',
        ]);
    
        // 🔍 Cari user Asrizal Mirzal berdasarkan Jabatan & Departemen
        $user = User::where('jabatan', 'Operation Directorate')
                    ->where('departemen', 'PT. PRIMA KARYA MANUNGGAL')
                    ->first();
    
        // Kirim notifikasi WA jika user ditemukan dan memiliki nomor WhatsApp
        if ($user && $user->whatsapp_number) {
            Http::withHeaders([
                'Authorization' => 'KBTe2RszCgc6aWhYapcv', // API key Fonnte Anda
            ])->post('https://api.fonnte.com/send', [
                'target' => $user->whatsapp_number,
                'message' => "📢 *Notifikasi Permintaan Approval Item Kebutuhan Pekerjaan* \n\n"
                    . "📌 *Nomor Order*: {$item->notification_number}\n"
                    . "📝 *Nama Pekerjaan*: {$item->deskripsi_pekerjaan}\n"
                    . "💰 *Total HPP*: Rp " . number_format($item->total_hpp, 0, ',', '.') . "\n"
                    . "🛠 *Total Kebutuhan*: Rp " . number_format($item->total, 0, ',', '.') . "\n"
                    . "🔗 Silakan login dan lakukan approval:\n"
                    . "👉 https://sectionofworkshop.com/pkm/items",
            ]);
        }
    
        return redirect()->route('pkm.items.index')->with('success', 'Item berhasil ditambahkan dan notifikasi telah dikirim.');
    }
    
    // Menampilkan detail item
    public function show($notification_number)
    {
        $item = Item::where('notification_number', $notification_number)->firstOrFail();
        return view('pkm.items.show', compact('item'));
    }

    // Menampilkan form edit item
    public function edit($notification_number)
    {
        $item = Item::where('notification_number', $notification_number)->firstOrFail();
        return view('pkm.items.edit', compact('item'));
    }

    public function updateApproval(Request $request, $notification_number)
    {
        $request->validate([
            'approved_by' => 'required|string|in:Approved,Rejected,Pending'
        ]);
    
        $item = Item::where('notification_number', $notification_number)->firstOrFail();
    
        $item->update([
            'approved_by' => $request->approved_by
        ]);
    
        return response()->json([
            'message' => 'Status berhasil diperbarui',
            'status' => $request->approved_by
        ], 200);
    }
    
    
    // Menghapus item
    public function destroy($notification_number)
    {
        Item::where('notification_number', $notification_number)->delete();
        return redirect()->route('pkm.items.index')->with('success', 'Item berhasil dihapus.');
    }
}
