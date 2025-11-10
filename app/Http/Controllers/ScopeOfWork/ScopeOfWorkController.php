<?php

namespace App\Http\Controllers\ScopeOfWork;

use App\Http\Controllers\Controller;
use App\Models\ScopeOfWork;
use App\Models\Notification;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ScopeOfWorkController extends Controller
{
    public function index()
    {
        try {
            $scopeOfWorks = ScopeOfWork::orderBy('tanggal_dokumen', 'desc')->get();
            return view('dokumen_orders.index', compact('scopeOfWorks'));
        } catch (\Exception $e) {
            Log::error('ScopeOfWorkController@index Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'Gagal memuat data Scope of Work.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return redirect()->back()->with('error', 'Gagal memuat data Scope of Work.');
        }
    }

    /**
     * Partial for modal create (AJAX)
     */
    public function modalCreate($notificationNumber)
    {
        try {
            $notification = Notification::where('notification_number', $notificationNumber)->firstOrFail();

            // return partial view (form only) - view harus dibuat: resources/views/scopeofwork/_form.blade.php
            return view('scopeofwork._form', [
                'notification' => $notification,
                'scopeOfWork' => null,
                'mode' => 'create',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ScopeOfWorkController@modalCreate NotFound: '.$notificationNumber);
            return response()->view('scopeofwork._error', ['message' => 'Notifikasi tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('ScopeOfWorkController@modalCreate Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString(), 'notification' => $notificationNumber]);
            return response()->view('scopeofwork._error', ['message' => 'Terjadi kesalahan saat memuat form.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Partial for modal edit (AJAX)
     */
    public function modalEdit($notificationNumber)
    {
        try {
            $scopeOfWork = ScopeOfWork::where('notification_number', $notificationNumber)->firstOrFail();

            // decode safely
            $scopeOfWork->scope_pekerjaan = json_decode($scopeOfWork->scope_pekerjaan, true) ?? [];
            $scopeOfWork->qty             = json_decode($scopeOfWork->qty, true) ?? [];
            $scopeOfWork->satuan          = json_decode($scopeOfWork->satuan, true) ?? [];
            $scopeOfWork->keterangan      = json_decode($scopeOfWork->keterangan, true) ?? [];

            $notification = Notification::where('notification_number', $scopeOfWork->notification_number)->first();

            return view('scopeofwork._form', [
                'notification' => $notification,
                'scopeOfWork' => $scopeOfWork,
                'mode' => 'edit',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ScopeOfWorkController@modalEdit NotFound: '.$notificationNumber);
            return response()->view('scopeofwork._error', ['message' => 'Scope of Work tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('ScopeOfWorkController@modalEdit Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString(), 'notification' => $notificationNumber]);
            return response()->view('scopeofwork._error', ['message' => 'Terjadi kesalahan saat memuat form edit.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create($notificationNumber)
    {
        try {
            $notification = Notification::where('notification_number', $notificationNumber)->firstOrFail();
            return view('scopeofwork.create', compact('notification'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ScopeOfWorkController@create NotFound: '.$notificationNumber);
            return redirect()->route('dokumen_orders.index')->with('error', 'Notifikasi tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('ScopeOfWorkController@create Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return redirect()->route('dokumen_orders.index')->with('error', 'Terjadi kesalahan saat membuka form create.');
        }
    }

    // Store Function
    public function store(Request $request)
    {
        $request->validate([
            'notification_number' => 'required|string|max:255|unique:scope_of_works,notification_number',
            'nama_pekerjaan'      => 'required|string|max:255',
            'unit_kerja'          => 'required|string|max:255',
            'tanggal_pemakaian'   => 'nullable|date',
            'tanggal_dokumen'     => 'required|date',
            'scope_pekerjaan'     => 'required|array|min:1',
            'scope_pekerjaan.*'   => 'required|string|max:255',
            'qty'                 => 'required|array|min:1',
            'qty.*'               => 'required|string|max:255',
            'satuan'              => 'required|array|min:1',
            'satuan.*'            => 'required|string|max:255',
            'keterangan'          => 'nullable|array',
            'keterangan.*'        => 'nullable|string|max:255',
            'catatan'             => 'nullable|string',
            'nama_penginput'      => 'nullable|string|max:255',
            'tanda_tangan'        => 'nullable|string',
        ]);

        // Normalisasi keterangan: ubah missing -> [], ubah '' -> null
        $keteranganInput = $request->input('keterangan', []);
        $keteranganNormalized = array_map(function ($v) {
            if (is_null($v)) return null;
            $v = trim($v);
            return $v === '' ? null : $v;
        }, $keteranganInput);

        // Jika semua null atau kosong, simpan sebagai null (opsional)
        $hasAny = false;
        foreach ($keteranganNormalized as $val) {
            if (!is_null($val) && $val !== '') { $hasAny = true; break; }
        }
        $keteranganToStore = $hasAny ? json_encode($keteranganNormalized) : null;

        DB::beginTransaction();
        try {
            $model = ScopeOfWork::create([
                'notification_number' => $request->notification_number,
                'nama_pekerjaan'      => $request->nama_pekerjaan,
                'unit_kerja'          => $request->unit_kerja,
                'tanggal_pemakaian'   => $request->tanggal_pemakaian,
                'tanggal_dokumen'     => $request->tanggal_dokumen,
                'scope_pekerjaan'     => json_encode($request->scope_pekerjaan),
                'qty'                 => json_encode($request->qty),
                'satuan'              => json_encode($request->satuan),
                'keterangan'          => $keteranganToStore,
                'catatan'             => $request->catatan,
                'nama_penginput'      => $request->nama_penginput,
                'tanda_tangan'        => $request->tanda_tangan,
            ]);
            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Scope of Work berhasil disimpan!', 'data' => $model], Response::HTTP_CREATED);
            }

            return redirect()->route('dokumen_orders.index')->with('success', 'Scope of Work berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ScopeOfWorkController@store Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString(), 'input' => $request->all()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat menyimpan Scope of Work.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan Scope of Work.');
        }
    }

    // Edit Function
    public function edit($notificationNumber)
    {
        try {
            $scopeOfWork = ScopeOfWork::where('notification_number', $notificationNumber)->firstOrFail();
            $notification = Notification::where('notification_number', $scopeOfWork->notification_number)->first();

            // decode safely
            $scopeOfWork->scope_pekerjaan = json_decode($scopeOfWork->scope_pekerjaan, true) ?? [];
            $scopeOfWork->qty             = json_decode($scopeOfWork->qty, true) ?? [];
            $scopeOfWork->satuan          = json_decode($scopeOfWork->satuan, true) ?? [];
            $scopeOfWork->keterangan      = json_decode($scopeOfWork->keterangan, true) ?? [];

            if (!$notification) {
                return redirect()->route('dokumen_orders.index')->with('error', 'Notifikasi terkait tidak ditemukan.');
            }

            return view('scopeofwork.edit', compact('scopeOfWork', 'notification'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ScopeOfWorkController@edit NotFound: '.$notificationNumber);
            return redirect()->route('dokumen_orders.index')->with('error', 'Scope of Work tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('ScopeOfWorkController@edit Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return redirect()->route('dokumen_orders.index')->with('error', 'Terjadi kesalahan saat membuka form edit.');
        }
    }

    // Update Function
    public function update(Request $request, $notificationNumber)
    {
        $request->validate([
            'notification_number' => 'required|string|max:255',
            'nama_pekerjaan'      => 'required|string|max:255',
            'unit_kerja'          => 'required|string|max:255',
            'tanggal_pemakaian'   => 'nullable|date',
            'tanggal_dokumen'     => 'required|date',
            'scope_pekerjaan'     => 'required|array|min:1',
            'scope_pekerjaan.*'   => 'required|string|max:255',
            'qty'                 => 'required|array|min:1',
            'qty.*'               => 'required|string|max:255',
            'satuan'              => 'required|array|min:1',
            'satuan.*'            => 'required|string|max:255',
            'keterangan'          => 'nullable|array',
            'keterangan.*'        => 'nullable|string|max:255',
            'catatan'             => 'nullable|string',
            'nama_penginput'      => 'nullable|string|max:255',
            'tanda_tangan'        => 'nullable|string',
        ]);

        // normalisasi keterangan
        $keteranganInput = $request->input('keterangan', []);
        $keteranganNormalized = array_map(function ($v) {
            if (is_null($v)) return null;
            $v = trim($v);
            return $v === '' ? null : $v;
        }, $keteranganInput);

        $hasAny = false;
        foreach ($keteranganNormalized as $val) {
            if (!is_null($val) && $val !== '') { $hasAny = true; break; }
        }
        $keteranganToStore = $hasAny ? json_encode($keteranganNormalized) : null;

        try {
            $scopeOfWork = ScopeOfWork::where('notification_number', $notificationNumber)->firstOrFail();

            DB::beginTransaction();
            $scopeOfWork->update([
                'notification_number' => $request->notification_number,
                'nama_pekerjaan'      => $request->nama_pekerjaan,
                'unit_kerja'          => $request->unit_kerja,
                'tanggal_pemakaian'   => $request->tanggal_pemakaian,
                'tanggal_dokumen'     => $request->tanggal_dokumen,
                'scope_pekerjaan'     => json_encode($request->scope_pekerjaan),
                'qty'                 => json_encode($request->qty),
                'satuan'              => json_encode($request->satuan),
                'keterangan'          => $keteranganToStore,
                'catatan'             => $request->catatan,
                'nama_penginput'      => $request->nama_penginput,
                'tanda_tangan'        => $request->tanda_tangan,
            ]);
            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Scope of Work berhasil diupdate!', 'data' => $scopeOfWork], Response::HTTP_OK);
            }
            return redirect()->route('dokumen_orders.index')->with('success', 'Scope of Work berhasil diupdate!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ScopeOfWorkController@update NotFound: '.$notificationNumber);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Scope of Work tidak ditemukan.'], Response::HTTP_NOT_FOUND);
            }
            return redirect()->route('dokumen_orders.index')->with('error', 'Scope of Work tidak ditemukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ScopeOfWorkController@update Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString(), 'input' => $request->all()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat mengupdate Scope of Work.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat mengupdate Scope of Work.');
        }
    }

    // Save Signature Function
    public function saveSignature(Request $request)
    {
        $request->validate([
            'scope_of_work_id' => 'required|exists:scope_of_works,notification_number',
            'tanda_tangan'     => 'required',
        ]);

        try {
            $scopeOfWork = ScopeOfWork::where('notification_number', $request->scope_of_work_id)->firstOrFail();
            $scopeOfWork->tanda_tangan = $request->tanda_tangan;
            $scopeOfWork->save();

            return response()->json(['message' => 'Signature saved successfully!'], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ScopeOfWorkController@saveSignature NotFound: '.$request->scope_of_work_id);
            return response()->json(['error' => 'Scope of Work tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('ScopeOfWorkController@saveSignature Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan tanda tangan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Show Function
    public function show($notificationNumber)
    {
        try {
            $scopeOfWork = ScopeOfWork::where('notification_number', $notificationNumber)->firstOrFail();

            $scopeOfWork->scope_pekerjaan = json_decode($scopeOfWork->scope_pekerjaan, true) ?? [];
            $scopeOfWork->qty             = json_decode($scopeOfWork->qty, true) ?? [];
            $scopeOfWork->satuan          = json_decode($scopeOfWork->satuan, true) ?? [];
            $scopeOfWork->keterangan      = json_decode($scopeOfWork->keterangan, true) ?? [];

            return view('scopeofwork.view', compact('scopeOfWork'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ScopeOfWorkController@show NotFound: '.$notificationNumber);
            return redirect()->route('dokumen_orders.index')->with('error', 'Scope of Work tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('ScopeOfWorkController@show Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return redirect()->route('dokumen_orders.index')->with('error', 'Terjadi kesalahan saat memuat Scope of Work.');
        }
    }

    public function downloadPDF($notificationNumber)
    {
        try {
            $scopeOfWork = ScopeOfWork::where('notification_number', $notificationNumber)->firstOrFail();

            $scopeOfWork->scope_pekerjaan = json_decode($scopeOfWork->scope_pekerjaan, true) ?? [];
            $scopeOfWork->qty             = json_decode($scopeOfWork->qty, true) ?? [];
            $scopeOfWork->satuan          = json_decode($scopeOfWork->satuan, true) ?? [];
            $scopeOfWork->keterangan      = json_decode($scopeOfWork->keterangan, true) ?? [];

            // handle tanda tangan base64
            $signaturePath = null;
            if ($scopeOfWork->tanda_tangan && str_starts_with($scopeOfWork->tanda_tangan, 'data:image')) {
                $imageData = explode(',', $scopeOfWork->tanda_tangan)[1] ?? null;
                if ($imageData) {
                    $signaturePath = storage_path('app/public/signatures/' . $scopeOfWork->notification_number . '.png');
                    file_put_contents($signaturePath, base64_decode($imageData));
                }
            }

            $pdf = Pdf::loadView('scopeofwork.pdf', [
                'scopeOfWork'   => $scopeOfWork,
                'signaturePath' => $signaturePath
            ])->setPaper('A4', 'portrait');

            return $pdf->stream('ScopeOfWork_'.$notificationNumber.'.pdf');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('ScopeOfWorkController@downloadPDF NotFound: '.$notificationNumber);
            return redirect()->route('dokumen_orders.index')->with('error', 'Scope of Work tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('ScopeOfWorkController@downloadPDF Exception: '.$e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return redirect()->route('dokumen_orders.index')->with('error', 'Terjadi kesalahan saat membuat PDF.');
        }
    }
}
