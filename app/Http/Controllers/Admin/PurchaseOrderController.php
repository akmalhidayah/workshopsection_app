<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Notification;
use App\Models\Abnormal;
use App\Models\ScopeOfWork;
use App\Models\GambarTeknik;
use App\Models\Hpp1;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        // Ambil data notifikasi dengan urutan terbaru
        $notifications = Notification::orderBy('created_at', 'desc')->get();
    
        // Iterasi untuk mengecek dokumen abnormalitas, scope of work, gambar teknik, dan HPP
        foreach ($notifications as $notification) {
            // Cek apakah abnormalitas tersedia
            $notification->isAbnormalAvailable = Abnormal::where('notification_number', $notification->notification_number)->exists();
            
            // Cek apakah scope of work tersedia
            $notification->isScopeOfWorkAvailable = ScopeOfWork::where('notification_number', $notification->notification_number)->exists();
            
            // Cek apakah gambar teknik tersedia
            $notification->isGambarTeknikAvailable = GambarTeknik::where('notification_number', $notification->notification_number)->exists();
            
            // Cek apakah HPP tersedia
            $hpp = Hpp1::where('notification_number', $notification->notification_number)->first();
            if ($hpp) {
                $notification->isHppAvailable = true;
                $notification->source_form = $hpp->source_form;
            } else {
                $notification->isHppAvailable = false;
            }
    
            // Status Approval berdasarkan approval yang sudah diisi
            if ($notification->purchaseOrder) {
                $po = $notification->purchaseOrder;
                $allApproved = true;
    
                // Cek approval berdasarkan source_form
                if ($notification->source_form === 'createhpp1') {
                    // Jika createhpp1, butuh 4 approval
                    $approvalStatuses = [
                        $po->approve_manager,
                        $po->approve_senior_manager,
                        $po->approve_general_manager,
                        $po->approve_direktur_operasional
                    ];
                } else {
                    // Jika bukan createhpp1, hanya butuh 3 approval
                    $approvalStatuses = [
                        $po->approve_manager,
                        $po->approve_senior_manager,
                        $po->approve_general_manager
                    ];
                }
    
                // Cek jika semua approval sudah tercentang
                foreach ($approvalStatuses as $status) {
                    if (!$status) {
                        $allApproved = false;
                        break;
                    }
                }
    
                // Tentukan status approval
                if ($allApproved) {
                    $notification->status_approval = 'Approved';
                } else {
                    $notification->status_approval = 'Pending';
                    $approvalWaitList = [];
    
                    if (!$po->approve_manager) {
                        $approvalWaitList[] = 'Manager';
                    }
                    if (!$po->approve_senior_manager) {
                        $approvalWaitList[] = 'Senior Manager';
                    }
                    if (!$po->approve_general_manager) {
                        $approvalWaitList[] = 'General Manager';
                    }
                    if ($notification->source_form === 'createhpp1' && !$po->approve_direktur_operasional) {
                        $approvalWaitList[] = 'Direktur Operasional';
                    }
    
                    $notification->waiting_for = implode(', ', $approvalWaitList);
                }
            } else {
                $notification->status_approval = 'Pending';
            }
        }
    
        // Tampilkan view dengan data notifikasi
        return view('admin.purchaseorder', compact('notifications'));
    }
    
    public function update(Request $request, $notification_number)
    {
        $request->validate([
            'purchase_order_number' => 'required|string|max:255',
            'po_document' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240', // Batasan ukuran file menjadi 10MB
            'approval_target' => 'required|string|in:setuju,tidak_setuju', // Validasi nilai "setuju" atau "tidak_setuju"
        ]);
    
        // Cari data Purchase Order berdasarkan nomor notifikasi
        $purchaseOrder = PurchaseOrder::firstOrNew(['notification_number' => $notification_number]);
    
        // Update data purchase order
        $purchaseOrder->purchase_order_number = $request->purchase_order_number;
    
        // Update nilai checkbox
        $purchaseOrder->approve_manager = $request->has('approve_manager');
        $purchaseOrder->approve_senior_manager = $request->has('approve_senior_manager');
        $purchaseOrder->approve_general_manager = $request->has('approve_general_manager');
        if ($request->source_form === 'createhpp1') {
            $purchaseOrder->approve_direktur_operasional = $request->has('approve_direktur_operasional');
        }
    
        // Jika ada dokumen yang diupload, simpan dokumen PO
        if ($request->hasFile('po_document')) {
            // Hapus dokumen PO sebelumnya jika ada
            if ($purchaseOrder->po_document_path) {
                Storage::delete('public/' . $purchaseOrder->po_document_path);
            }
    
            // Atur nama file berdasarkan nomor notifikasi dan timestamp
            $filename = $notification_number . '_' . time() . '.' . $request->file('po_document')->getClientOriginalExtension();
            
            // Simpan file baru di folder storage/app/public/po_documents
            $path = $request->file('po_document')->storeAs('public/po_documents', $filename);
            $purchaseOrder->po_document_path = str_replace('public/', '', $path);
        }
    
        // Simpan nilai approval_target (setuju/tidak_setuju)
        $purchaseOrder->approval_target = $request->approval_target;
    
        // Simpan tanggal update terakhir
        $purchaseOrder->update_date = now();
    
        // Simpan data Purchase Order
        $purchaseOrder->save();
    
        return redirect()->back()->with('success', 'Purchase Order berhasil diperbarui.');
    }
}    