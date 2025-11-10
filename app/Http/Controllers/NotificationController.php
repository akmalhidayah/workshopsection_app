<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\UnitWork;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotificationController extends Controller
{
    /**
     * List notifications with search, sort and pagination.
     */
    public function index(Request $request)
    {
        try {
            $query = Notification::query();

            if (auth()->user()->usertype != 'admin') {
                $query->where('user_id', auth()->id());
            }

            // Search
            if ($request->filled('search')) {
                $q = $request->get('search');
                $query->where(function ($sub) use ($q) {
                    $sub->where('notification_number', 'like', '%' . $q . '%')
                        ->orWhere('job_name', 'like', '%' . $q . '%')
                        ->orWhere('seksi', 'like', '%' . $q . '%'); 
                });
            }

            // Sorting
            $sortOrder = $request->get('sortOrder', 'latest');
            switch ($sortOrder) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'priority-highest':
                    $query->orderByRaw("FIELD(priority, 'Urgently', 'Hard', 'Medium', 'Low')");
                    break;
                case 'priority-lowest':
                    $query->orderByRaw("FIELD(priority, 'Low', 'Medium', 'Hard', 'Urgently')");
                    break;
                case 'latest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            // Pagination jumlah (whitelist & cast)
            $allowedEntries = [10, 25, 50, 100];
            $entries = (int) $request->get('entries', 10);
            if (!in_array($entries, $allowedEntries)) {
                $entries = 10;
            }

            $notifications = $query->paginate($entries)->withQueryString();

            $units = UnitWork::orderBy('name')->get();

            return view('notifications.index', compact('notifications', 'units'));
        } catch (\Exception $e) {
            Log::error('NotificationController@index Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat memuat daftar notifikasi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return response()->view('errors.500', ['message' => 'Terjadi kesalahan saat memuat daftar notifikasi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store new notification (create).
     */
    public function store(Request $request)
    {
        $request->validate([
            'notification_number' => ['required', Rule::unique('notifications', 'notification_number')],
            'job_name' => 'required|string',
            'unit_work' => 'required|string',
            'seksi' => 'nullable|string|max:255',
            'priority' => 'required|in:Urgently,Hard,Medium,Low',
            'input_date' => 'required|date',
            'usage_plan_date' => 'required|date',
        ]);

        try {
            Notification::create([
                'notification_number' => $request->notification_number,
                'job_name' => $request->job_name,
                'unit_work' => $request->unit_work,
                'seksi' => $request->seksi,
                'priority' => $request->priority,
                'input_date' => $request->input_date,
                'usage_plan_date' => $request->usage_plan_date,
                'user_id' => auth()->id(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Order berhasil dibuat.'], Response::HTTP_CREATED);
            }
             // validasi seksi vs unit
    $unit = \App\Models\UnitWork::where('name', $request->unit_work)->first();
    if ($request->filled('seksi')) {
        $allowed = $unit? $unit->seksi_list : [];
        if (!in_array($request->seksi, $allowed, true)) {
            return back()->withErrors(['seksi' => 'Seksi tidak valid untuk Unit Kerja terpilih.'])->withInput();
        }
    }

            return redirect()->route('notifications.index')->with('success', 'Order berhasil dibuat.');
        } catch (QueryException $e) {
            Log::warning('NotificationController@store QueryException: '.$e->getMessage(), ['errorInfo' => $e->errorInfo ?? null]);

            // duplicate key
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                $message = 'Nomor order ini sudah digunakan oleh user lain.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $message], Response::HTTP_CONFLICT);
                }
                return redirect()->back()->withErrors(['notification_number' => $message])->withInput();
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat membuat order.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan, silakan coba lagi.'])->withInput();
        } catch (\Exception $e) {
            Log::error('NotificationController@store Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan server saat memproses permintaan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return response()->view('errors.500', ['message' => 'Terjadi kesalahan server saat memproses permintaan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Destroy (delete) notification.
     */
    public function destroy(Request $request, $notification_number)
    {
        try {
            $notification = Notification::where('notification_number', $notification_number)
                                        ->where('user_id', auth()->id())
                                        ->firstOrFail();

            $notification->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Data berhasil dihapus.'], Response::HTTP_OK);
            }

            return redirect()->route('notifications.index')->with('success', 'Data berhasil dihapus beserta data terkait.');
        } catch (ModelNotFoundException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Notifikasi tidak ditemukan atau akses ditolak.'], Response::HTTP_NOT_FOUND);
            }
            return redirect()->route('notifications.index')->withErrors(['error' => 'Notifikasi tidak ditemukan atau akses ditolak.']);
        } catch (\Exception $e) {
            Log::error('NotificationController@destroy Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat menghapus notifikasi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return response()->view('errors.500', ['message' => 'Terjadi kesalahan saat menghapus notifikasi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Edit (fetch single notification as JSON for edit modal).
     */
   public function edit(Request $request, $notification_number)
{
    try {
        if (auth()->user()->usertype === 'admin') {
            $notification = Notification::where('notification_number', $notification_number)->firstOrFail();
        } else {
            $notification = Notification::where('notification_number', $notification_number)
                                        ->where('user_id', auth()->id())
                                        ->firstOrFail();
        }

        return response()->json($notification, Response::HTTP_OK);
    } catch (ModelNotFoundException $e) {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['error' => 'Notifikasi tidak ditemukan atau akses ditolak.'], Response::HTTP_NOT_FOUND);
        }
        return redirect()->route('notifications.index')->withErrors(['error' => 'Notifikasi tidak ditemukan atau akses ditolak.']);
    } catch (\Exception $e) {
        Log::error('NotificationController@edit Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data notifikasi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->view('errors.500', ['message' => 'Terjadi kesalahan saat mengambil data notifikasi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

/**
 * Update notification.
 * - If request contains 'status' OR 'mode' => treat as admin status/catatan update (from admin panel)
 * - Otherwise treat as normal edit (modal) updating job_name, unit_work, priority, input_date, usage_plan_date
 */
public function update(Request $request, $notification_number)
{
    // ---------- ADMIN flow (status/catatan via admin panel) ----------
    // We detect admin flow either by presence of 'mode' (approved_jasa/...) OR presence of 'status'
    $isAdminFlow = $request->has('mode') || $request->has('status');

    if ($isAdminFlow) {
        // Define allowed buckets (should mirror what Blade provides)
        $opsiJasa = ['Jasa Fabrikasi','Jasa Konstruksi','Jasa Pengerjaan Mesin'];
        $opsiWorkshop = ['Regu Fabrikasi','Regu Bengkel (Refurbish)'];

        // Accept either 'mode' (preferred) or 'status' param.
        // mode: approved_jasa | approved_workshop | pending | reject
        $mode = $request->get('mode', null);
        $statusFromRequest = $request->get('status', null);

        // Basic validation for mode/status presence
        if (!$mode && !$statusFromRequest) {
            // nothing to validate -> bad request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Mode atau status diperlukan untuk pembaruan admin.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return redirect()->back()->withErrors(['error' => 'Mode atau status diperlukan untuk pembaruan admin.']);
        }

        // Normalize mode -> if mode not provided but status provided, infer simple mapping
        if (!$mode && $statusFromRequest) {
            // If status is 'Approved' we cannot infer whether jasa/workshop — expect catatan to tell us.
            if ($statusFromRequest === 'Approved') {
                // keep mode null — we'll validate catatan against both buckets
                $mode = null;
            } elseif ($statusFromRequest === 'Pending') {
                $mode = 'pending';
            } elseif ($statusFromRequest === 'Reject') {
                $mode = 'reject';
            }
        }

        // Validate according to mode
        $rules = [
            // catatan is required for approved modes (and must match allowed lists)
            'catatan' => 'nullable|string|max:1000',
        ];

        // We'll run custom validation logic below (so not all rules put inside validate()).
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Extract catatan value (can be empty for pending/reject)
        $catatan = trim((string) $request->input('catatan', ''));

        // Now validate mode-specific constraints
        if ($mode === 'approved_jasa') {
            if (empty($catatan) || !in_array($catatan, $opsiJasa)) {
                $msg = 'Untuk Approved (Jasa), pilih catatan yang valid.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $msg], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                return redirect()->back()->withErrors(['catatan' => $msg])->withInput();
            }
            $statusToSave = 'Approved';
        } elseif ($mode === 'approved_workshop') {
            if (empty($catatan) || !in_array($catatan, $opsiWorkshop)) {
                $msg = 'Untuk Approved (Workshop), pilih catatan yang valid.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $msg], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                return redirect()->back()->withErrors(['catatan' => $msg])->withInput();
            }
            $statusToSave = 'Approved';
        } elseif ($mode === 'pending') {
            $statusToSave = 'Pending';
            // catatan optional (no further checks)
        } elseif ($mode === 'reject') {
            $statusToSave = 'Reject';
            // catatan optional (no further checks)
        } else {
            // mode null but statusFromRequest may be present (fallback)
            if ($statusFromRequest === 'Approved') {
                // status Approved but mode not given: ensure catatan belongs to either list
                if (empty($catatan) || (!in_array($catatan, $opsiJasa) && !in_array($catatan, $opsiWorkshop))) {
                    $msg = 'Status Approved memerlukan catatan yang valid (Jasa atau Workshop).';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['error' => $msg], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    return redirect()->back()->withErrors(['catatan' => $msg])->withInput();
                }
                $statusToSave = 'Approved';
            } elseif ($statusFromRequest === 'Pending') {
                $statusToSave = 'Pending';
            } elseif ($statusFromRequest === 'Reject') {
                $statusToSave = 'Reject';
            } else {
                $msg = 'Mode atau status tidak dikenali.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $msg], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                return redirect()->back()->withErrors(['error' => $msg]);
            }
        }

        // All validation passed — persist
        try {
            $notification = Notification::where('notification_number', $notification_number)->firstOrFail();

            $notification->status = $statusToSave;
            $notification->catatan = $catatan ?: null;
            $notification->save();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Status dan catatan berhasil diperbarui.'], Response::HTTP_OK);
            }

        // redirect to admin.notifikasi with tab preserved
$redirectRoute = route('admin.notifikasi', ['tab' => $request->get('tab', 'notif')]);
return redirect($redirectRoute)->with('success', 'Status dan catatan berhasil diperbarui.');

        } catch (ModelNotFoundException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Notifikasi tidak ditemukan.'], Response::HTTP_NOT_FOUND);
            }
            return redirect()->back()->withErrors(['error' => 'Notifikasi tidak ditemukan.']);
        } catch (\Exception $e) {
            Log::error('NotificationController@update (admin mode) Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat memperbarui status.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui status.']);
        }
    } // end admin flow


    // ---------- NORMAL edit (modal) flow ----------
    $request->validate([
        'job_name' => 'required|string|max:255',
        'unit_work' => 'required|string|max:255',
         'seksi' => 'nullable|string|max:255',
        'priority' => 'required|in:Urgently,Hard,Medium,Low',
        'input_date' => 'required|date',
        'usage_plan_date' => 'required|date',
    ]);

    try {
        if (auth()->user()->usertype === 'admin') {
            $notification = Notification::where('notification_number', $notification_number)->firstOrFail();
        } else {
            $notification = Notification::where('notification_number', $notification_number)
                                        ->where('user_id', auth()->id())
                                        ->firstOrFail();
        }

        $notification->job_name = $request->input('job_name');
        $notification->unit_work = $request->input('unit_work');
          $notification->seksi = $request->input('seksi');
        $notification->priority = $request->input('priority');
        $notification->input_date = $request->input('input_date');
        $notification->usage_plan_date = $request->input('usage_plan_date');
        $notification->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Notifikasi berhasil diperbarui.'], Response::HTTP_OK);
        }

        return redirect()->route('notifications.index')->with('success', 'Notifikasi berhasil diperbarui.');
    } catch (ModelNotFoundException $e) {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['error' => 'Notifikasi tidak ditemukan atau akses ditolak.'], Response::HTTP_NOT_FOUND);
        }
        return back()->withErrors(['error' => 'Notifikasi tidak ditemukan atau akses ditolak.']);
    } catch (\Exception $e) {
        Log::error('NotificationController@update (edit) Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['error' => 'Terjadi kesalahan saat memperbarui notifikasi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui notifikasi.']);
    }
}


    /**
     * Update priority (separate endpoint).
     */
    public function updatePriority(Request $request, $notification_number)
    {
        $request->validate([
            'priority' => 'required|string|in:Urgently,Hard,Medium,Low',
        ]);

        try {
            if (auth()->user()->usertype == 'admin') {
                $notification = Notification::where('notification_number', $notification_number)->firstOrFail();
            } else {
                $notification = Notification::where('notification_number', $notification_number)
                                            ->where('user_id', auth()->id())
                                            ->firstOrFail();
            }

            $notification->priority = $request->input('priority');
            $notification->save();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Priority berhasil diperbarui.'], Response::HTTP_OK);
            }
            return back()->with('success_priority', 'Priority berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Notifikasi tidak ditemukan atau akses ditolak.'], Response::HTTP_NOT_FOUND);
            }
            return back()->withErrors(['error' => 'Notifikasi tidak ditemukan atau akses ditolak.']);
        } catch (\Exception $e) {
            Log::error('NotificationController@updatePriority Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat memperbarui prioritas.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return response()->view('errors.500', ['message' => 'Terjadi kesalahan saat memperbarui prioritas.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
public function updateEkorin(Request $request, $notification_number)
{
    // validasi: HARUS SAMA PERSIS (dua-duanya wajib)
    $validated = $request->validate([
        'nomor_e_korin'  => 'required|string|max:255',
        'status_e_korin' => 'required|in:waiting_korin,waiting_transfer,complete_transfer',
    ]);

    try {
        // cek akses notif
        if (auth()->user()->usertype === 'admin') {
            $notif = \App\Models\Notification::where('notification_number', $notification_number)->firstOrFail();
        } else {
            $notif = \App\Models\Notification::where('notification_number', $notification_number)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        // ambil verifikasi anggaran
        $va = \App\Models\VerifikasiAnggaran::where('notification_number', $notification_number)->first();

        if (!$va) {
            return back()->withErrors(['ekorin' => 'Verifikasi Anggaran belum dibuat oleh admin.'])->withInput();
        }

        if ($va->status_anggaran !== 'Tersedia') {
            return back()->withErrors(['ekorin' => 'E-KORIN hanya bisa diisi jika status dana = Tersedia.'])->withInput();
        }

        // update hanya dua kolom ini
        $va->nomor_e_korin  = $validated['nomor_e_korin'];
        $va->status_e_korin = $validated['status_e_korin'];
        // opsional: cap waktu update
        $va->tanggal_verifikasi = $va->tanggal_verifikasi ?? now();
        $va->save();

        return back()->with('success', 'Nomor E-KORIN dan Status E-KORIN berhasil disimpan.');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return back()->withErrors(['ekorin' => 'Notifikasi tidak ditemukan atau akses ditolak.']);
    } catch (\Exception $e) {
        \Log::error('updateEkorin error: '.$e->getMessage(), ['notification' => $notification_number]);
        return back()->withErrors(['ekorin' => 'Terjadi kesalahan saat menyimpan E-KORIN.']);
    }
}

}
