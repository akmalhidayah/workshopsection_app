<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $entries = $request->input('entries', 10);
            $search  = $request->input('search');
            $status  = $request->input('status'); // setuju / tidak_setuju
            $unit    = $request->input('unit');
            $from    = $request->input('from');
            $to      = $request->input('to');

            // ✅ Query Notification + eager load relasi
            $query = Notification::with(['dokumenOrders', 'scopeOfWork', 'hpp1', 'purchaseOrder']);

            // 🔎 Filter: Search berdasarkan nomor notifikasi / nama pekerjaan
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('notification_number', 'LIKE', "%{$search}%")
                      ->orWhere('job_name', 'LIKE', "%{$search}%");
                });
            }

            // 🔎 Filter: Unit kerja
            if ($unit) {
                $query->where('unit_work', $unit);
            }

            // 🔎 Filter: Rentang tanggal pembuatan notifikasi
            if ($from && $to) {
                $query->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $query->whereDate('created_at', '>=', $from);
            } elseif ($to) {
                $query->whereDate('created_at', '<=', $to);
            }

            // 🔎 Filter: Status persetujuan PO (setuju/tidak_setuju)
            if ($status) {
                $query->whereHas('purchaseOrder', function ($poQuery) use ($status) {
                    $poQuery->where('approval_target', $status);
                });
            }

            // 🔹 Ambil data dengan pagination
            $notifications = $query->orderBy('created_at', 'desc')->paginate($entries);

            // 🔹 Proses tiap notifikasi untuk data tambahan
            foreach ($notifications as $notification) {
                // ✅ Cek dokumen abnormalitas
                $notification->isAbnormalAvailable = $notification->dokumenOrders
                    ->where('jenis_dokumen', 'abnormalitas')->isNotEmpty();

                // ✅ Scope of work
                $notification->isScopeOfWorkAvailable = $notification->scopeOfWork !== null;

                // ✅ Cek gambar teknik
                $notification->isGambarTeknikAvailable = $notification->dokumenOrders
                    ->where('jenis_dokumen', 'gambar_teknik')->isNotEmpty();

                // ✅ Cek HPP
                if ($notification->hpp1) {
                    $notification->isHppAvailable = true;
                    $notification->source_form   = $notification->hpp1->source_form;
                    $notification->total_amount  = $notification->hpp1->total_amount;
                } else {
                    $notification->isHppAvailable = false;
                }

                // ✅ Status Approval PO
                if ($notification->purchaseOrder) {
                    $po = $notification->purchaseOrder;
                    $allApproved = true;

                    $approvalStatuses = ($notification->source_form === 'createhpp1')
                        ? [
                            $po->approve_manager,
                            $po->approve_senior_manager,
                            $po->approve_general_manager,
                            $po->approve_direktur_operasional,
                        ]
                        : [
                            $po->approve_manager,
                            $po->approve_senior_manager,
                            $po->approve_general_manager,
                        ];

                    foreach ($approvalStatuses as $statusApproval) {
                        if (!$statusApproval) {
                            $allApproved = false;
                            break;
                        }
                    }

                    if ($allApproved) {
                        $notification->status_approval = 'Approved';
                    } else {
                        $notification->status_approval = 'Pending';
                        $approvalWaitList = [];

                        if (!$po->approve_manager) $approvalWaitList[] = 'Manager';
                        if (!$po->approve_senior_manager) $approvalWaitList[] = 'Senior Manager';
                        if (!$po->approve_general_manager) $approvalWaitList[] = 'General Manager';
                        if ($notification->source_form === 'createhpp1' && !$po->approve_direktur_operasional) {
                            $approvalWaitList[] = 'Direktur Operasional';
                        }

                        $notification->waiting_for = implode(', ', $approvalWaitList);
                    }
                } else {
                    $notification->status_approval = 'Pending';
                }
            }

            return view('admin.purchaseorder', compact(
                'notifications',
                'search',
                'entries',
                'status',
                'unit',
                'from',
                'to'
            ));
        } catch (\Throwable $e) {
            Log::error('Error di PurchaseOrderController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->view('errors.500', [
                'message' => 'Terjadi kesalahan saat memuat data Purchase Order.'
            ], 500);
        }
    }

    public function update(Request $request, $notification_number)
    {
        try {
            $request->validate([
                'purchase_order_number' => 'required|string|max:255',
                'po_document' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:10240',
                'approval_target' => 'required|string|in:setuju,tidak_setuju',
                'approval_note'   => 'nullable|string|max:1000',
                'catatan_pkm'     => 'nullable|string|max:1000',
                'target_penyelesaian' => 'nullable|date',
            ]);

            // ✅ Cari atau buat PO berdasarkan nomor notifikasi
            $purchaseOrder = PurchaseOrder::firstOrNew(['notification_number' => $notification_number]);

            $purchaseOrder->purchase_order_number = $request->purchase_order_number;
            $purchaseOrder->approval_target       = $request->approval_target;
            $purchaseOrder->approval_note         = $request->approval_note;
            $purchaseOrder->catatan_pkm           = $request->catatan_pkm;
            $purchaseOrder->target_penyelesaian   = $request->target_penyelesaian;
            $purchaseOrder->update_date           = now();

            // ✅ Checkbox approval
            $purchaseOrder->approve_manager         = $request->has('approve_manager');
            $purchaseOrder->approve_senior_manager  = $request->has('approve_senior_manager');
            $purchaseOrder->approve_general_manager = $request->has('approve_general_manager');

            // ✅ Cek source_form untuk direktur ops
            $notification = Notification::with('hpp1')->where('notification_number', $notification_number)->first();
            if ($notification && $notification->hpp1 && $notification->hpp1->source_form === 'createhpp1') {
                $purchaseOrder->approve_direktur_operasional = $request->has('approve_direktur_operasional');
            }

            // ✅ Upload dokumen PO
            if ($request->hasFile('po_document')) {
                if ($purchaseOrder->po_document_path) {
                    Storage::delete('public/' . $purchaseOrder->po_document_path);
                }

                $filename = $notification_number . '_' . time() . '.' .
                    $request->file('po_document')->getClientOriginalExtension();

                $path = $request->file('po_document')->storeAs('public/po_documents', $filename);
                $purchaseOrder->po_document_path = str_replace('public/', '', $path);
            }

            $purchaseOrder->save();

            return redirect()->back()->with('success', 'Purchase Order berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Error di PurchaseOrderController@update: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->view('errors.500', [
                'message' => 'Terjadi kesalahan saat memperbarui Purchase Order.'
            ], 500);
        }
    }
}
