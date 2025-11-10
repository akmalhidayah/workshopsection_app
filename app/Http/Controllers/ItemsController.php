<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Hpp1;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ItemsController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();

            Log::info('User Data:', [
                'name' => $user->name ?? null,
                'jabatan' => $user->jabatan ?? null,
                'departemen' => $user->departemen ?? null
            ]);

            $items = Item::all();

            return view('pkm.items.index', compact('items', 'user'));
        } catch (\Exception $e) {
            Log::error("Error ItemsController@index: " . $e->getMessage());
            return abort(500, 'Terjadi kesalahan saat memuat data item.');
        }
    }

    public function getItemData($notification_number)
    {
        try {
            $notification = Notification::where('notification_number', $notification_number)->first();
            $hpp = Hpp1::where('notification_number', $notification_number)->first();

            if (!$notification) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            return response()->json([
                'deskripsi_pekerjaan' => $notification->job_name ?? 'Tidak tersedia',
                'total_hpp' => (int) ($hpp->total_amount ?? 0),
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error ItemsController@getItemData: " . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil data item'], 500);
        }
    }

    public function create()
    {
        try {
            $existingItems = Item::pluck('notification_number');

            $notifications = Notification::whereNotIn('notification_number', $existingItems)
                ->whereHas('purchaseOrder', function ($query) {
                    $query->whereNotNull('purchase_order_number');
                })
                ->get();

            return view('pkm.items.create', compact('notifications'));
        } catch (\Exception $e) {
            Log::error("Error ItemsController@create: " . $e->getMessage());
            return abort(500, 'Terjadi kesalahan saat memuat form tambah item.');
        }
    }

    public function store(Request $request)
    {
        try {
            $notification = Notification::findOrFail($request->notification_number);
            $hpp = Hpp1::where('notification_number', $notification->notification_number)->first();

            $total_hpp = $hpp->total_amount ?? 0;
            $materials = $request->input('material', []);
            $harga = array_map('floatval', $request->input('harga', []));

            $total = array_sum($harga);
            $total_margin = $total_hpp - $total;

            $item = Item::create([
                'notification_number' => $notification->notification_number,
                'deskripsi_pekerjaan' => $notification->job_name ?? 'Tidak Ada Data',
                'total_hpp' => $total_hpp,
                'materials' => $materials,
                'harga' => $harga,
                'total' => $total,
                'total_margin' => $total_margin,
                'approved_by' => 'Pending',
            ]);

            // 🔍 Cari user target (misalnya Asrizal Mirzal)
            $user = User::where('jabatan', 'Operation Directorate')
                ->where('departemen', 'PT. PRIMA KARYA MANUNGGAL')
                ->first();

            if ($user && $user->whatsapp_number) {
                try {
                    Http::withHeaders([
                        'Authorization' => 'KBTe2RszCgc6aWhYapcv',
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
                } catch (\Exception $e) {
                    Log::error("Gagal kirim notifikasi WA: " . $e->getMessage());
                }
            }

            return redirect()->route('pkm.items.index')
                ->with('success', 'Item berhasil ditambahkan dan notifikasi telah dikirim.');
        } catch (\Exception $e) {
            Log::error("Error ItemsController@store: " . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan item.')->withInput();
        }
    }

    public function show($notification_number)
    {
        try {
            $item = Item::where('notification_number', $notification_number)->firstOrFail();
            return view('pkm.items.show', compact('item'));
        } catch (\Exception $e) {
            Log::error("Error ItemsController@show: " . $e->getMessage());
            return abort(404, 'Item tidak ditemukan.');
        }
    }

    public function edit($notification_number)
    {
        try {
            $item = Item::where('notification_number', $notification_number)->firstOrFail();
            return view('pkm.items.edit', compact('item'));
        } catch (\Exception $e) {
            Log::error("Error ItemsController@edit: " . $e->getMessage());
            return abort(404, 'Item tidak ditemukan.');
        }
    }

    public function updateApproval(Request $request, $notification_number)
    {
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Error ItemsController@updateApproval: " . $e->getMessage());
            return response()->json(['error' => 'Gagal memperbarui status'], 500);
        }
    }

    public function destroy($notification_number)
    {
        try {
            Item::where('notification_number', $notification_number)->delete();
            return redirect()->route('pkm.items.index')->with('success', 'Item berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error("Error ItemsController@destroy: " . $e->getMessage());
            return back()->with('error', 'Gagal menghapus item.');
        }
    }
}
