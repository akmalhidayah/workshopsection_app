<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\UnitWork;
use App\Models\VerifikasiAnggaran;
use App\Models\OrderBengkel;
use App\Services\NotificationService;
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
            $query = Notification::with([
    'dokumenOrders',
    'scopeOfWork',
    'orderBengkel',
    'verifikasiAnggaran',
]);


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

            $units = UnitWork::with('sections')->orderBy('name')->get();

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
$notification = Notification::create([
    'notification_number' => $request->notification_number,
    'job_name' => $request->job_name,
    'unit_work' => $request->unit_work,
    'seksi' => $request->seksi,
    'priority' => $request->priority,
    'input_date' => $request->input_date,
    'usage_plan_date' => $request->usage_plan_date,
    'user_id' => auth()->id(),
    'status' => Notification::STATUS_PENDING,
]);
NotificationService::notifyAdmin([
    'entity_type' => 'notification',
    'entity_id'   => $notification->notification_number,
    'action'      => 'created',
    'title'       => 'Order pekerjaan baru',
    'description' => 'Dari Unit ' . $notification->unit_work,
    'url'         => route('notifikasi.index', ['tab' => 'notif']),
    'priority'    => NotificationService::mapPriorityFromNotification(
        $notification->priority
    ),
]);




            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Order berhasil dibuat.'], Response::HTTP_CREATED);
            }
             // validasi seksi vs unit
            $unit = \App\Models\UnitWork::with('sections')->where('name', $request->unit_work)->first();
            if ($request->filled('seksi')) {
                $allowed = $unit
                    ? $unit->sections->pluck('name')->filter()->values()->all()
                    : [];
                if (empty($allowed) && method_exists($unit, 'getSeksiListAttribute')) {
                    $allowed = $unit->seksi_list;
                }
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
 * - Jika request mengandung 'status' atau 'mode' => dianggap update dari admin (status/catatan)
 * - Selain itu => edit biasa (modal) untuk job_name, unit_work, priority, dll.
 */
public function update(Request $request, $notification_number)
{
    // ---------- ADMIN flow (status/catatan via admin panel) ----------
    $isAdminFlow = $request->has('mode') || $request->has('status');

    if ($isAdminFlow) {
        // gunakan daftar opsi dari model (supaya konsisten)
        $opsiJasa     = Notification::JASA_NOTES;
        $opsiWorkshop = Notification::WORKSHOP_NOTES;

        // status enum dikirim dari Blade (hidden input `status`)
        // nilai yg diharapkan: approved_workshop | approved_jasa | approved_workshop_jasa | pending | reject
        $status = $request->input('status');
        $mode   = $request->input('mode'); // masih boleh dipakai jika perlu, tapi status yg utama

        // validasi dasar
        $validator = \Validator::make($request->all(), [
            'status'  => [
                'required',
                'string',
                Rule::in([
                    Notification::STATUS_PENDING,
                    Notification::STATUS_REJECT,
                    Notification::STATUS_APPROVED_WORKSHOP,
                    Notification::STATUS_APPROVED_JASA,
                    Notification::STATUS_APPROVED_WORKSHOP_JASA,
                ]),
            ],
            'catatan' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Extract catatan (boleh kosong untuk sebagian status)
        $catatan = trim((string) $request->input('catatan', ''));

        // --- Validasi tambahan per-status ---

        // Approved (Jasa) → catatan wajib & harus salah satu dari JASA_NOTES
        if ($status === Notification::STATUS_APPROVED_JASA) {
            if ($catatan === '' || !in_array($catatan, $opsiJasa, true)) {
                $msg = 'Untuk Approved (Jasa), pilih catatan yang valid.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $msg], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                return redirect()->back()->withErrors(['catatan' => $msg])->withInput();
            }
        }

        // Approved (Workshop) → catatan wajib & harus salah satu dari WORKSHOP_NOTES
        if ($status === Notification::STATUS_APPROVED_WORKSHOP) {
            if ($catatan === '' || !in_array($catatan, $opsiWorkshop, true)) {
                $msg = 'Untuk Approved (Workshop), pilih catatan yang valid.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $msg], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                return redirect()->back()->withErrors(['catatan' => $msg])->withInput();
            }
        }

        // Approved (Workshop + Jasa) → catatan opsional,
        // tapi kalau DIISI harus termasuk salah satu dari gabungan 2 list
        if ($status === Notification::STATUS_APPROVED_WORKSHOP_JASA) {
            if ($catatan !== '' && !in_array($catatan, array_merge($opsiJasa, $opsiWorkshop), true)) {
                $msg = 'Catatan untuk Approved (Workshop + Jasa) harus dari daftar opsi Jasa/Workshop.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => $msg], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                return redirect()->back()->withErrors(['catatan' => $msg])->withInput();
            }
        }

        // pending / reject → catatan bebas (opsional), tidak ada constraint khusus

        // Simpan ke DB
        try {
            $notification = Notification::where('notification_number', $notification_number)->firstOrFail();

            $notification->status  = $status;                     // ⬅️ langsung simpan enum baru
            $notification->catatan = $catatan !== '' ? $catatan : null;
            $notification->save();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message'      => 'Status dan catatan berhasil diperbarui.',
                    'notification' => $notification,
                ], Response::HTTP_OK);
            }

            // redirect ke admin.notifikasi dengan tab yang sama
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
    }

    // ---------- NORMAL edit (modal) flow (TIDAK DIUBAH) ----------
    $request->validate([
        'job_name'        => 'required|string|max:255',
        'unit_work'       => 'required|string|max:255',
        'seksi'           => 'nullable|string|max:255',
        'priority'        => 'required|in:Urgently,Hard,Medium,Low',
        'input_date'      => 'required|date',
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

        $notification->job_name        = $request->input('job_name');
        $notification->unit_work       = $request->input('unit_work');
        $notification->seksi           = $request->input('seksi');
        $notification->priority        = $request->input('priority');
        $notification->input_date      = $request->input('input_date');
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
    $validated = $request->validate([
        'nomor_e_korin'  => 'required|string|max:255',
        'status_e_korin' => 'required|in:waiting_korin,waiting_approval,waiting_transfer,complete_transfer',
    ]);

    try {
        // Cek akses notif
        if (auth()->user()->usertype === 'admin') {
            $notif = Notification::where('notification_number', $notification_number)->firstOrFail();
        } else {
            $notif = Notification::where('notification_number', $notification_number)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        $va = VerifikasiAnggaran::where('notification_number', $notification_number)->first();
        $ob = OrderBengkel::where('notification_number', $notification_number)->first();

        // ==============================
        // 1) Jika Verifikasi Anggaran: "Tidak Tersedia"
        // ==============================
        if ($va && $va->status_anggaran === 'Tidak Tersedia') {

            $va->nomor_e_korin  = $validated['nomor_e_korin'];
            $va->status_e_korin = $validated['status_e_korin'];
            $va->tanggal_verifikasi = $va->tanggal_verifikasi ?? now();
            $va->save();

            return back()->with('success', 'E-KORIN berhasil disimpan ke Verifikasi Anggaran.');
        }

        // ==============================
        // 2) Jika Order Bengkel: "Waiting Budget"
        // ==============================
        if ($ob && $ob->status_anggaran === 'Waiting Budget') {

            $ob->nomor_e_korin  = $validated['nomor_e_korin'];
            $ob->status_e_korin = $validated['status_e_korin'];
            $ob->save();

            return back()->with('success', 'E-KORIN berhasil disimpan ke Order Bengkel.');
        }

        // Jika dua-duanya tidak cocok
        return back()->withErrors(['ekorin' => 'Tidak dapat menyimpan E-KORIN. Status anggaran tidak valid.']);

    } catch (\Exception $e) {
        \Log::error('updateEkorin error: '.$e->getMessage());
        return back()->withErrors(['ekorin' => 'Terjadi kesalahan saat menyimpan E-KORIN.']);
    }
}



}
