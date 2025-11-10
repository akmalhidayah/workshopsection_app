<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hpp1;
use App\Models\User;
use App\Models\Notification;
use App\Services\HppApprovalLinkService;
use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HppApprovalController extends Controller
{
    // === GET: halaman tanda tangan ===
    public function show(string $token, HppApprovalLinkService $svc)
    {
        try {
            $link = $svc->validate($token); // throw jika invalid/expired/used
            $user = auth()->user();

            if ((int) $link->user_id !== (int) ($user->id ?? 0)) {
                Log::warning('[HPP] Token bukan milik user login', [
                    'token_id' => $link->id ?? null,
                    'link_user'=> $link->user_id ?? null,
                    'auth_user'=> $user->id ?? null,
                ]);
                abort(Response::HTTP_FORBIDDEN, 'Token ini bukan untuk akun Anda.');
            }

            $hpp = Hpp1::where('notification_number', $link->notification_number)->with('notification')->firstOrFail();

            $expected = $this->expectedSignTypeForStatus($hpp->status);
            if ($expected !== $link->sign_type) {
                Log::warning('[HPP] Sign type tidak sesuai status', [
                    'notif'    => $hpp->notification_number,
                    'status'   => $hpp->status,
                    'expected' => $expected,
                    'linkType' => $link->sign_type,
                ]);
                abort(Response::HTTP_FORBIDDEN, 'Langkah approval tidak sesuai status saat ini.');
            }

            return view('admin.inputhpp.approval_sign', [
                'hpp'           => $hpp,
                'token'         => $link->id,
                'signTypeLabel' => $this->labelOf($link->sign_type),
                'sign_type'     => $link->sign_type,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('[HPP] HPP tidak ditemukan saat show()', ['error' => $e->getMessage()]);
            abort(Response::HTTP_NOT_FOUND, 'Dokumen tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('[HPP] Gagal membuka halaman tanda tangan', ['error' => $e->getMessage()]);
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat membuka halaman tanda tangan.');
        }
    }

// === POST: submit approve / reject ===
public function sign(string $token, Request $request, HppApprovalLinkService $svc, SignatureService $sigSvc)
{
    $request->validate([
        'signature_base64' => 'required|string',        // data:image/png;base64,...
        'action'           => 'required|in:approve,reject',
        'reason'           => 'nullable|string|max:500',
        'note'             => 'nullable|string|max:1000',          // catatan approval (opsional)
        'note_target'      => 'nullable|in:controlling,requesting',// override target notes (opsional)
    ]);

    if ($request->action === 'reject' && blank($request->reason)) {
        return back()
            ->withErrors(['reason' => 'Alasan wajib diisi saat menolak.'])
            ->withInput()
            ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    try {
        $link = $svc->validate($token);
        $user = auth()->user();

        if ((int) $link->user_id !== (int) ($user->id ?? 0)) {
            Log::warning('[HPP] Token bukan milik user login (sign)', [
                'token_id' => $link->id ?? null,
                'link_user'=> $link->user_id ?? null,
                'auth_user'=> $user->id ?? null,
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Token ini bukan untuk akun Anda.');
        }

        $hpp = Hpp1::where('notification_number', $link->notification_number)
            ->with('notification')
            ->firstOrFail();

        $expected = $this->expectedSignTypeForStatus($hpp->status);
        if ($expected !== $link->sign_type) {
            Log::warning('[HPP] Sign type tidak sesuai status (sign)', [
                'notif'    => $hpp->notification_number,
                'status'   => $hpp->status,
                'expected' => $expected,
                'linkType' => $link->sign_type,
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Langkah approval tidak sesuai status saat ini.');
        }

        // === Simpan tanda tangan ===
        $stored  = $sigSvc->storeBase64(
            $request->input('signature_base64'),
            'hpp',
            $link->sign_type,
            (string) $hpp->notification_number
        );
        $sigPath = $stored['path'] ?? null;

// === Mapping kolom sesuai role ===
$map = [
    // PENGENDALI
    'manager'  => [
        'field'      => 'manager_signature',
        'user_field' => 'manager_signature_user_id',
        'time_field' => 'manager_signed_at',
        'approved'   => 'approved_manager',
    ],
    'sm' => [
        'field'      => 'senior_manager_signature',
        'user_field' => 'senior_manager_signature_user_id',
        'time_field' => 'senior_manager_signed_at',
        'approved'   => 'approved_sm',
    ],
    'gm' => [
        'field'      => 'general_manager_signature',
        'user_field' => 'general_manager_signature_user_id',
        'time_field' => 'general_manager_signed_at',
        'approved'   => 'approved_gm',
    ],
    'dir' => [
        'field'      => 'director_signature',
        'user_field' => 'director_signature_user_id',
        'time_field' => 'director_signed_at',
        'approved'   => 'approved_dir',
    ],

    // PEMINTA
    'mgr_req' => [
        'field'      => 'manager_signature_requesting_unit',
        'user_field' => 'manager_signature_requesting_user_id',
        'time_field' => 'manager_requesting_signed_at',
        'approved'   => 'approved_manager_req',
    ],
    'sm_req' => [
        'field'      => 'senior_manager_signature_requesting_unit',
        'user_field' => 'senior_manager_signature_requesting_user_id',
        'time_field' => 'senior_manager_requesting_signed_at',
        'approved'   => 'approved_sm_req',
    ],
    'gm_req' => [
        'field'      => 'general_manager_signature_requesting_unit',
        'user_field' => 'general_manager_signature_requesting_user_id',
        'time_field' => 'general_manager_requesting_signed_at',
        'approved'   => 'approved_gm_req',
    ],
];

        if (!isset($map[$link->sign_type])) {
            Log::error('[HPP] sign_type tidak dikenali', [
                'notif' => $hpp->notification_number,
                'type'  => $link->sign_type,
            ]);
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Jenis penandatangan tidak dikenali.');
        }

        $cfg = $map[$link->sign_type];

        // === Tentukan field notes & siapkan entry untuk di-append ===
        $noteTarget = $request->input('note_target');
        if (!$noteTarget) {
            // default otomatis dari sign_type
            $noteTarget = in_array($link->sign_type, ['manager','sm','gm','dir'])
                ? 'controlling'
                : 'requesting';
        }
        $noteField = $noteTarget === 'controlling' ? 'controlling_notes' : 'requesting_notes';

        // Ambil notes lama (array) dari model (model sudah cast ke array)
        $notesExisting = is_array($hpp->$noteField) ? $hpp->$noteField : [];

        // Siapkan entry catatan (opsional; kalau kosong tidak di-append)
$noteText  = trim((string) $request->input('note', ''));
$noteEntry = null;
if ($noteText !== '') {
    $noteEntry = [
        'note'    => $noteText,
        'user_id' => (int) auth()->id(),
        'at'      => now()->toDateTimeString(),
    ];
}

        // === Reject ===
        if ($request->action === 'reject') {
            if ($noteEntry) { $notesExisting[] = $noteEntry; }   // append catatan baru

            $hpp->update([
                $cfg['field']      => $sigPath,
                $cfg['user_field'] => $user->id,
                $cfg['time_field']  => now(),  
                'status'           => 'rejected',
                'rejection_reason' => $request->input('reason'),
                $noteField         => $notesExisting,             // simpan array notes
            ]);

            $svc->markUsed($link);

            return redirect()
                ->route('admin.inputhpp.index')
                ->with('warning', 'Dokumen ditolak.')
                ->setStatusCode(Response::HTTP_OK);
        }

        // === Approve ===
        if ($noteEntry) { $notesExisting[] = $noteEntry; }       // append catatan baru

        $hpp->update([
            $cfg['field']      => $sigPath,
            $cfg['user_field'] => $user->id,
             $cfg['time_field']  => now(), 
            'status'           => $cfg['approved'],
            $noteField         => $notesExisting,                 // simpan array notes
        ]);

        // Tandai token ini used
        $svc->markUsed($link);

        // === Issue token untuk approver berikutnya ===
        if ($next = $this->nextApprover($link->sign_type, $hpp)) {

            // pilih unit target berdasarkan tipe approver
            $targetUnit = $next['type'] === 'request'
                ? optional($hpp->notification)->unit_work                  // PEMINTA: dari Notification.unit_work
                : ($hpp->controlling_unit ?: 'Unit of Workshop & Design'); // PENGENDALI: fallback default

            $nextUser = $this->findUserByRoleUnit($next['role'], (string)$targetUnit);

            if (!$nextUser) {
                Log::warning('[HPP] Approver berikutnya tidak ditemukan', [
                    'notif' => $hpp->notification_number,
                    'role'  => $next['role'],
                    'unit'  => $targetUnit,
                    'type'  => $next['type'],
                ]);

                // TETAP lanjutin tanpa crash: info ke UI
                return redirect()
                    ->route('admin.inputhpp.index')
                    ->with('warning', "Approval berikutnya tidak ditemukan untuk role '{$next['role']}' pada unit '{$targetUnit}'. Silakan lengkapi data user.")
                    ->setStatusCode(Response::HTTP_OK);
            }

            $nextTok = $svc->issue(
                $hpp->notification_number,
                $next['key'],
                $nextUser->id,
                60 * 24
            );

            // Kirim WA (best-effort)
            try {
                Http::withHeaders([
                    'Authorization' => env('FONNTE_TOKEN', 'KBTe2RszCgc6aWhYapcv')
                ])->post('https://api.fonnte.com/send', [
                    'target'  => $nextUser->whatsapp_number,
                    'message' =>
                        "✍️ *Permintaan Tanda Tangan HPP*\n".
                        "No: {$hpp->notification_number}\n".
                        "Role: {$next['role']}\n".
                        "Klik untuk menandatangani:\n".$svc->url($nextTok)."\n\n".
                        "_Link berlaku 24 jam & hanya untuk Anda_",
                ]);
            } catch (\Throwable $e) {
                Log::error('[HPP] Gagal kirim WA approver berikutnya', [
                    'notif' => $hpp->notification_number,
                    'user'  => $nextUser->id,
                    'error' => $e->getMessage(),
                ]);
                // Tidak gagal proses—hanya alert di UI
                return redirect()
                    ->route('admin.inputhpp.index')
                    ->with('warning', 'Disetujui. Namun notifikasi WA ke approver berikutnya gagal terkirim.')
                    ->setStatusCode(Response::HTTP_OK);
            }
        }

        return redirect()
            ->route('admin.inputhpp.index')
            ->with('success', 'Dokumen disetujui.')
            ->setStatusCode(Response::HTTP_OK);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('[HPP] HPP tidak ditemukan saat sign()', ['error' => $e->getMessage()]);
        return redirect()
            ->route('admin.inputhpp.index')
            ->with('error', 'Dokumen tidak ditemukan.')
            ->setStatusCode(Response::HTTP_NOT_FOUND);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        // abort() sudah set status code yang tepat
        throw $e;
    } catch (\Throwable $e) {
        Log::error('[HPP] Gagal memproses tanda tangan', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()
            ->route('admin.inputhpp.index')
            ->with('error', 'Terjadi kesalahan saat memproses tanda tangan.')
            ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    // === State machine: next sign_type yang valid untuk status tertentu ===
    private function expectedSignTypeForStatus(?string $status): string
    {
        return match ($status) {
            'submitted'              => 'manager',   // PENGENDALI
            'approved_manager'       => 'sm',        // PENGENDALI
            'approved_sm'            => 'mgr_req',   // PEMINTA
            'approved_manager_req'   => 'sm_req',    // PEMINTA
            'approved_sm_req'        => 'gm',        // PENGENDALI
            'approved_gm'            => 'gm_req',    // PEMINTA
            'approved_gm_req'        => 'dir',       // PENGENDALI
            default                  => 'manager',
        };
    }

    // === Alur berikutnya + tipe unit yang dipakai ===
    private function nextApprover(string $current, Hpp1 $hpp): ?array
    {
        // type: 'controller' (pakai controlling_unit) | 'request' (pakai Notification.unit_work)
        return match ($current) {
            'manager'  => ['key'=>'sm',      'role'=>'Senior Manager',  'type'=>'controller'],
            'sm'       => ['key'=>'mgr_req', 'role'=>'Manager',         'type'=>'request'],
            'mgr_req'  => ['key'=>'sm_req',  'role'=>'Senior Manager',  'type'=>'request'],
            'sm_req'   => ['key'=>'gm',      'role'=>'General Manager', 'type'=>'controller'],
            'gm'       => ['key'=>'gm_req',  'role'=>'General Manager', 'type'=>'request'],
            'gm_req'   => ['key'=>'dir',     'role'=>'Director',        'type'=>'controller'],
            default    => null,
        };
    }

    private function labelOf(string $key): string
    {
        return match ($key) {
            'manager'  => 'Manager Bengkel Mesin (Pengendali)',
            'sm'       => 'Senior Manager Workshop (Pengendali)',
            'gm'       => 'General Manager (Pengendali)',
            'dir'      => 'Director of Operation',
            'mgr_req'  => 'Manager Peminta',
            'sm_req'   => 'Senior Manager Peminta',
            'gm_req'   => 'General Manager Peminta',
            default    => strtoupper($key),
        };
    }

    /**
     * Cari approver berdasarkan jabatan + unit.
     * - Cocokkan case-insensitive pada `unit_work` (prefix match)
     * - Atau `related_units` (JSON array) mengandung unit (full match / prefix varian)
     */
private function findUserByRoleUnit(string $role, string $unit): ?User
{
    $unit = trim($unit ?? '');
    $role = trim($role ?? '');

    if ($role === '') return null;

    // Alias jabatan biar fleksibel (case-insensitive)
    $aliases = match (strtolower($role)) {
        'manager'          => ['manager'],
        'senior manager'   => ['senior manager','sr manager','senior mgr'],
        'general manager'  => ['general manager','gm'],
        'director'         => [
            'director',
            'operational director',
            'operation director',
            'director of operation',
            'operational direction',
            'operation direction',
            'operation directorate',
            'operation directorat',
            'operation directorate (do)',
            'operation directorate.',   // antisipasi varian input
            'operation directorate '    // spasi nyasar
        ],
        default            => [strtolower($role)],
    };

    $q = User::query()
        ->where(function ($w) use ($aliases) {
            foreach ($aliases as $a) {
                $w->orWhereRaw('LOWER(jabatan) = ?', [strtolower($a)]);
            }
        });

    if ($unit !== '') {
        $q->where(function ($sub) use ($unit) {
            // 1) unit_work prefix-match
            $sub->whereRaw('LOWER(unit_work) LIKE ?', [strtolower($unit).'%'])
                // 2) related_units JSON contains (exact)
                ->orWhere(function ($qq) use ($unit) {
                    $qq->whereNotNull('related_units')
                       ->whereJsonContains('related_units', $unit);
                });
        });
    }

    return $q->first();
}

}
