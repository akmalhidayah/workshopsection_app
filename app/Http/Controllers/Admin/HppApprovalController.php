<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hpp1;
use App\Models\DokumenOrder;
use App\Models\User;
use App\Models\Notification;
use App\Services\HppApprovalLinkService;
use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Models\HppApprovalToken;

class HppApprovalController extends Controller
{
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

        $hpp = Hpp1::where('notification_number', $link->notification_number)
            ->with('notification')
            ->firstOrFail();

        // 🔹 Ambil dokumen abnormalitas (kalau ada)
        $dokumenAbnormalitas = DokumenOrder::where('notification_number', $hpp->notification_number)
            ->where('jenis_dokumen', 'abnormalitas')
            ->first();

        // gunakan mapping yang mempertimbangkan source_form
        $expected = $this->expectedSignTypeForStatus($hpp->status, $hpp);
        if ($expected !== $link->sign_type) {
            Log::warning('[HPP] Sign type tidak sesuai status', [
                'notif'    => $hpp->notification_number,
                'status'   => $hpp->status,
                'expected' => $expected,
                'linkType' => $link->sign_type,
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Langkah approval tidak sesuai status saat ini.');
        }

        // 🔹 Cek apakah user ini punya TTD lama untuk sign_type ini
        $map = [
            // PENGENDALI
            'manager'  => [
                'field'      => 'manager_signature',
                'user_field' => 'manager_signature_user_id',
                'time_field' => 'manager_signed_at',
            ],
            'sm' => [
                'field'      => 'senior_manager_signature',
                'user_field' => 'senior_manager_signature_user_id',
                'time_field' => 'senior_manager_signed_at',
            ],
            'gm' => [
                'field'      => 'general_manager_signature',
                'user_field' => 'general_manager_signature_user_id',
                'time_field' => 'general_manager_signed_at',
            ],
            'dir' => [
                'field'      => 'director_signature',
                'user_field' => 'director_signature_user_id',
                'time_field' => 'director_signed_at',
            ],

            // PEMINTA
            'mgr_req' => [
                'field'      => 'manager_signature_requesting_unit',
                'user_field' => 'manager_signature_requesting_user_id',
                'time_field' => 'manager_requesting_signed_at',
            ],
            'sm_req' => [
                'field'      => 'senior_manager_signature_requesting_unit',
                'user_field' => 'senior_manager_signature_requesting_user_id',
                'time_field' => 'senior_manager_requesting_signed_at',
            ],
            'gm_req' => [
                'field'      => 'general_manager_signature_requesting_unit',
                'user_field' => 'general_manager_signature_requesting_user_id',
                'time_field' => 'general_manager_signature_requesting_signed_at',
            ],
        ];

        $hasOldSignature = false;

        if (isset($map[$link->sign_type])) {
            $cfg = $map[$link->sign_type];

            $hasOldSignature = Hpp1::where($cfg['user_field'], $user->id)
                ->whereNotNull($cfg['field'])
                ->exists();
        }

        return view('admin.inputhpp.approval_sign', [
            'hpp'                => $hpp,
            'token'              => $link->id,
            'signTypeLabel'      => $this->labelOf($link->sign_type),
            'sign_type'          => $link->sign_type,
            'dokumenAbnormalitas'=> $dokumenAbnormalitas,
            'hasOldSignature'    => $hasOldSignature, // 🔺 kirim ke Blade
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('[HPP] HPP tidak ditemukan saat show()', ['error' => $e->getMessage()]);
        abort(Response::HTTP_NOT_FOUND, 'Dokumen tidak ditemukan.');
    } catch (\Throwable $e) {
        Log::error('[HPP] Gagal membuka halaman tanda tangan', ['error' => $e->getMessage()]);
        abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat membuka halaman tanda tangan.');
    }
}


public function sign(string $token, Request $request, HppApprovalLinkService $svc, SignatureService $sigSvc)
{
    // 🔹 Atur rules: signature_base64 hanya wajib kalau TIDAK pakai TTD lama
    $rules = [
        'action'           => 'required|in:approve,reject',
        'reason'           => 'nullable|string|max:500',
        'note'             => 'nullable|string|max:1000',
        'note_target'      => 'nullable|in:controlling,requesting',
        'use_old_signature'=> 'nullable|boolean',
    ];

    if (! $request->boolean('use_old_signature')) {
        $rules['signature_base64'] = 'required|string'; // data:image/png;base64,...
    }

    $request->validate($rules);

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

        // gunakan mapping yg aware source_form
        $expected = $this->expectedSignTypeForStatus($hpp->status, $hpp);
        if ($expected !== $link->sign_type) {
            Log::warning('[HPP] Sign type tidak sesuai status (sign)', [
                'notif'    => $hpp->notification_number,
                'status'   => $hpp->status,
                'expected' => $expected,
                'linkType' => $link->sign_type,
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Langkah approval tidak sesuai status saat ini.');
        }

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
            // NOTE: perbaikan kolom user_field (sesuai model Hpp1)
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

        // === Tentukan path signature: pakai TTD lama ATAU simpan baru ===
        if ($request->boolean('use_old_signature')) {
            // 🔹 Cari TTD terakhir user ini untuk role/sign_type ini
            $last = Hpp1::where($cfg['user_field'], $user->id)
                ->whereNotNull($cfg['field'])
                ->orderByDesc($cfg['time_field'])
                ->first();

            if (!$last) {
                return back()
                    ->withErrors(['signature_base64' => 'Tanda tangan lama tidak ditemukan. Silakan gambar tanda tangan baru.'])
                    ->withInput();
            }

            $sigPath = $last->{$cfg['field']};
        } else {
            // 🔹 Simpan TTD baru seperti biasa
            $stored  = $sigSvc->storeBase64(
                $request->input('signature_base64'),
                'hpp',
                $link->sign_type,
                (string) $hpp->notification_number
            );
            $sigPath = $stored['path'] ?? null;
        }

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
                $cfg['field']       => $sigPath,
                $cfg['user_field']  => $user->id,
                $cfg['time_field']  => now(),
                'status'            => 'rejected',
                'rejection_reason'  => $request->input('reason'),
                $noteField          => $notesExisting,             // simpan array notes
            ]);

            $svc->markUsed($link);

            // tentukan pdf url sesuai source_form (dipakai untuk open_pdf di frontend)
            $pdfUrl = match($hpp->source_form ?? 'createhpp1') {
                'createhpp1' => route('approval.hpp.download_hpp1', $hpp->notification_number),
                'createhpp2' => route('approval.hpp.download_hpp2', $hpp->notification_number),
                'createhpp3' => route('approval.hpp.download_hpp3', $hpp->notification_number),
                'createhpp4' => route('approval.hpp.download_hpp4', $hpp->notification_number),
                default      => route('approval.hpp.download_hpp1', $hpp->notification_number),
            };

            return redirect()
                ->route('dashboard')
                ->with('warning', 'Dokumen ditolak.')
                ->with('open_pdf', $pdfUrl)
                ->setStatusCode(Response::HTTP_OK);
        }

        // === Approve ===
        if ($noteEntry) { $notesExisting[] = $noteEntry; }       // append catatan baru

        $hpp->update([
            $cfg['field']       => $sigPath,
            $cfg['user_field']  => $user->id,
            $cfg['time_field']  => now(),
            'status'            => $cfg['approved'],
            $noteField          => $notesExisting,                 // simpan array notes
        ]);

        // Tandai token ini used
        $svc->markUsed($link);

        // === Issue token untuk approver berikutnya ===
        if ($next = $this->nextApprover($link->sign_type, $hpp)) {

            // pilih unit target berdasarkan tipe approver
            $targetUnit = $next['type'] === 'request'
                ? optional($hpp->notification)->unit_work
                : ($hpp->controlling_unit ?: 'Unit of Workshop & Design');

            $nextUser = $this->findUserByRoleUnit($next['role'], (string)$targetUnit);

            if (!$nextUser) {
                Log::warning('[HPP] Approver berikutnya tidak ditemukan', [
                    'notif' => $hpp->notification_number,
                    'role'  => $next['role'],
                    'unit'  => $targetUnit,
                    'type'  => $next['type'],
                ]);

                // tentukan pdf url agar frontend bisa membuka dokumen via SweetAlert
                $pdfUrl = match($hpp->source_form ?? 'createhpp1') {
                    'createhpp1' => route('approval.hpp.download_hpp1', $hpp->notification_number),
                    'createhpp2' => route('approval.hpp.download_hpp2', $hpp->notification_number),
                    'createhpp3' => route('approval.hpp.download_hpp3', $hpp->notification_number),
                    'createhpp4' => route('approval.hpp.download_hpp4', $hpp->notification_number),
                    default      => route('approval.hpp.download_hpp1', $hpp->notification_number),
                };

                return redirect()
                    ->route('dashboard')
                    ->with('warning', "Approval berikutnya tidak ditemukan untuk role '{$next['role']}' pada unit '{$targetUnit}'. Silakan lengkapi data user.")
                    ->with('open_pdf', $pdfUrl)
                    ->setStatusCode(Response::HTTP_OK);
            }

            $nextTok = $svc->issue(
                $hpp->notification_number,
                $next['key'],
                $nextUser->id,
                60 * 24 * 30 // 30 hari in minutes
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
                        "_Link berlaku 30 hari & hanya untuk Anda_",
                ]);
            } catch (\Throwable $e) {
                Log::error('[HPP] Gagal kirim WA approver berikutnya', [
                    'notif' => $hpp->notification_number,
                    'user'  => $nextUser->id,
                    'error' => $e->getMessage(),
                ]);

                // tetap redirect ke dashboard, sertakan open_pdf agar SweetAlert membuka file
                $pdfUrl = match($hpp->source_form ?? 'createhpp1') {
                    'createhpp1' => route('approval.hpp.download_hpp1', $hpp->notification_number),
                    'createhpp2' => route('approval.hpp.download_hpp2', $hpp->notification_number),
                    'createhpp3' => route('approval.hpp.download_hpp3', $hpp->notification_number),
                    'createhpp4' => route('approval.hpp.download_hpp4', $hpp->notification_number),
                    default      => route('approval.hpp.download_hpp1', $hpp->notification_number),
                };

                return redirect()
                    ->route('dashboard')
                    ->with('warning', 'Disetujui. Namun notifikasi WA ke approver berikutnya gagal terkirim.')
                    ->with('open_pdf', $pdfUrl)
                    ->setStatusCode(Response::HTTP_OK);
            }
        }

        // tentukan pdf url sesuai source_form (sama logika seperti Blade)
        $pdfUrl = match($hpp->source_form ?? 'createhpp1') {
            'createhpp1' => route('approval.hpp.download_hpp1', $hpp->notification_number),
            'createhpp2' => route('approval.hpp.download_hpp2', $hpp->notification_number),
            'createhpp3' => route('approval.hpp.download_hpp3', $hpp->notification_number),
            'createhpp4' => route('approval.hpp.download_hpp4', $hpp->notification_number),
            default      => route('approval.hpp.download_hpp1', $hpp->notification_number),
        };

        return redirect()
            ->route('dashboard')
            ->with('success', 'Dokumen disetujui.')
            ->with('open_pdf', $pdfUrl)
            ->setStatusCode(Response::HTTP_OK);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('[HPP] HPP tidak ditemukan saat sign()', ['error' => $e->getMessage()]);
        return redirect()
            ->route('dashboard')
            ->with('error', 'Dokumen tidak ditemukan.')
            ->setStatusCode(Response::HTTP_NOT_FOUND);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        throw $e;
    } catch (\Throwable $e) {
        Log::error('[HPP] Gagal memproses tanda tangan', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()
            ->route('dashboard')
            ->with('error', 'Terjadi kesalahan saat memproses tanda tangan.')
            ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


    /**
     * State machine: determine expected sign_type for given status,
     * but allow different mappings per source_form.
     *
     * @param string|null $status
     * @param Hpp1|null $hpp
     * @return string
     */
  /**
 * State machine: determine expected sign_type for given status,
 * sesuai urutan:
 * manager (ctrl) -> sm (ctrl) -> mgr_req -> sm_req -> gm_req -> gm (ctrl final)
 */
private function expectedSignTypeForStatus(?string $status, ?Hpp1 $hpp = null): string
{
    $source = (string) ($hpp->source_form ?? '');

    /**
     * DEFAULT (fallback umum, kalau source_form tidak dikenali)
     *
     * Status → sedang menunggu siapa?
     * - submitted            : Manager controlling
     * - approved_manager     : Senior Manager controlling
     * - approved_sm          : Manager Peminta
     * - approved_manager_req : Senior Manager Peminta
     * - approved_sm_req      : General Manager Peminta
     * - approved_gm_req      : General Manager controlling
     * - approved_gm          : sudah final (tidak ada yang tanda tangan lagi)
     */
    $default = [
        'submitted'             => 'manager',
        'approved_manager'      => 'sm',
        'approved_sm'           => 'mgr_req',
        'approved_manager_req'  => 'sm_req',
        'approved_sm_req'       => 'gm_req',
        'approved_gm_req'       => 'gm',
        // 'approved_gm'        => null (final, tidak butuh sign_type lagi)
    ];

    /**
     * Mapping per source_form
     * createhpp1 & createhpp2: full flow sampai GM controlling
     * createhpp3 & createhpp4: bisa disesuaikan sendiri kalau mau beda
     */
    $maps = [
        // HPP1 : Manager ctrl → SM ctrl → Mgr req → SM req → GM req → GM ctrl (final)
        'createhpp1' => [
            'submitted'             => 'manager',
            'approved_manager'      => 'sm',
            'approved_sm'           => 'mgr_req',
            'approved_manager_req'  => 'sm_req',
            'approved_sm_req'       => 'gm_req',
            'approved_gm_req'       => 'gm',
            // 'approved_gm'        => null
        ],

        // HPP2 : alur sama (sampai GM controlling, tanpa Direktur)
        'createhpp2' => [
            'submitted'             => 'manager',
            'approved_manager'      => 'sm',
            'approved_sm'           => 'mgr_req',
            'approved_manager_req'  => 'sm_req',
            'approved_sm_req'       => 'gm_req',
            'approved_gm_req'       => 'gm',
            // 'approved_gm'        => null
        ],

        // HPP3 : kalau maunya cuma sampai GM (tanpa jalur peminta), bisa:
        // manager ctrl → sm ctrl → gm ctrl → (opsional dir)
        'createhpp3' => [
            'submitted'             => 'manager',
            'approved_manager'      => 'sm',
            'approved_sm'           => 'gm',
            // 'approved_gm'        => 'dir', // kalau mau lanjut direktur
        ],

        // HPP4 : contoh sampai SM saja (silakan sesuaikan kebutuhan asli)
        'createhpp4' => [
            'submitted'             => 'manager',
            'approved_manager'      => 'sm',
            // 'approved_sm'        => 'gm', // kalau ternyata perlu GM, tinggal aktifkan
        ],
    ];

    $mapping = $maps[$source] ?? $default;

    // return mapped expected sign_type, fallback ke default, terakhir ke 'manager'
    return $mapping[$status] ?? ($default[$status] ?? 'manager');
}

    /**
     * Determine next approver (sign_type, role, and unit type) given current sign_type
     * and Hpp1 object (so behaviour can vary by source_form).
     *
     * Returns ['key'=>'sm', 'role'=>'Senior Manager', 'type'=>'controller'] or null.
     */
    private function nextApprover(string $current, Hpp1 $hpp): ?array
    {
        $source = (string) ($hpp->source_form ?? '');

        // Helper: produce mapping for createhpp1 (full flow)
        $map_createhpp1 = [
            'manager'  => ['key' => 'sm',      'role' => 'Senior Manager',  'type' => 'controller'],
            'sm'       => ['key' => 'mgr_req', 'role' => 'Manager',         'type' => 'request'],
            'mgr_req'  => ['key' => 'sm_req',  'role' => 'Senior Manager',  'type' => 'request'],
            'sm_req'   => ['key' => 'gm_req',  'role' => 'General Manager', 'type' => 'request'],
            'gm_req'   => ['key' => 'gm',      'role' => 'General Manager', 'type' => 'controller'],
            'gm'       => null,
            'dir'      => null,
        ];

        // createhpp2: same as createhpp1 but stop before director (no dir)
        $map_createhpp2 = $map_createhpp1;
        $map_createhpp2['gm'] = null;

        // createhpp3: manager -> sm -> gm -> dir
        $map_createhpp3 = [
            'manager'  => ['key' => 'sm', 'role' => 'Senior Manager', 'type' => 'controller'],
            'sm'       => ['key' => 'gm', 'role' => 'General Manager', 'type' => 'controller'],
            'gm'       => ['key' => 'dir','role' => 'Director', 'type' => 'controller'],
            'dir'      => null,
        ];

        // createhpp4: manager -> sm -> gm (final)
        $map_createhpp4 = [
            'manager'  => ['key' => 'sm', 'role' => 'Senior Manager', 'type' => 'controller'],
            'sm'       => ['key' => 'gm', 'role' => 'General Manager', 'type' => 'controller'],
            'gm'       => null,
        ];

        // pick map
        $map = match ($source) {
            'createhpp1' => $map_createhpp1,
            'createhpp2' => $map_createhpp2,
            'createhpp3' => $map_createhpp3,
            'createhpp4' => $map_createhpp4,
            default      => $map_createhpp1,
        };

        return $map[$current] ?? null;
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
    public function reissueToken(Request $request, string $notification_number, HppApprovalLinkService $svc)
{
    // minimal: pastikan user punya hak (middleware admin sudah ada, tapi double-check if needed)
    $user = auth()->user();

    try {
        $hpp = Hpp1::where('notification_number', $notification_number)->firstOrFail();

        // Tentukan sign_type: prefer explicit input (security: only allow known values),
        // otherwise deduce from expectedSignTypeForStatus (use controller helper)
        $requestedSignType = $request->input('sign_type');
        $expected = $this->expectedSignTypeForStatus($hpp->status, $hpp);

        $signType = $requestedSignType ? trim($requestedSignType) : $expected;

        // simple whitelist
        $allowed = ['manager','sm','gm','dir','mgr_req','sm_req','gm_req'];
        if (! in_array($signType, $allowed)) {
            return response()->json(['error' => 'Jenis sign_type tidak valid.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Rate-limit check: jangan re-issue terus menerus (1 menit)
        $recent = HppApprovalToken::where('notification_number', $notification_number)
            ->where('sign_type', $signType)
            ->whereNull('used_at')
            ->where('created_at', '>', now()->subMinutes(1))
            ->exists();
        if ($recent) {
            return response()->json(['error' => 'Token baru sudah diterbitkan baru-baru ini. Tunggu beberapa menit.'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        DB::beginTransaction();

        // Revoke any currently active tokens for this notif+signType
        $active = HppApprovalToken::where('notification_number', $notification_number)
            ->where('sign_type', $signType)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        foreach ($active as $a) {
            // gunakan service untuk menandai dipakai (preserve audit) atau set expires_at = now
            try {
                $svc->markUsed($a);
            } catch (\Throwable $e) {
                // fallback: langsung update
                $a->used_at = now();
                $a->save();
            }
        }

        // Tentukan user target untuk token baru.
        // Jika request menyertakan target_user_id (admin memilih approver), pakai itu.
        $targetUserId = $request->input('target_user_id');

        $targetUser = null;
        if ($targetUserId) {
            $targetUser = User::find($targetUserId);
        }

        // Jika targetUser tidak diberikan atau tidak ditemukan, cari user default berdasarkan role+unit
        if (! $targetUser) {
            // tentukan target unit sesuai nextApprover logic (mirip kode kamu)
            $next = $this->nextApprover($signType, $hpp) ?? ['type' => 'controller', 'role' => $this->labelOf($signType)];
            $targetUnit = $next['type'] === 'request'
                ? optional($hpp->notification)->unit_work
                : ($hpp->controlling_unit ?: null);

            // reuse method yang sudah ada untuk menemukan user
            $targetUser = $this->findUserByRoleUnit($next['role'], (string)($targetUnit ?? ''));
        }

        if (! $targetUser) {
            DB::rollBack();
            return response()->json([
                'error' => 'Approver tidak ditemukan — lengkapi data user untuk role/unit terkait.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // issue token (default 30 hari in minutes)
        $minutes = 60 * 24 * 30;
        $newTokId = $svc->issue($notification_number, $signType, $targetUser->id, $minutes);

        DB::commit();

        // optional: send WA asynchronously (best-effort here, you may dispatch a job)
        try {
            Http::withHeaders(['Authorization' => env('FONNTE_TOKEN', 'KBTe2RszCgc6aWhYapcv')])
                ->post('https://api.fonnte.com/send', [
                    'target' => $targetUser->whatsapp_number,
                    'message' => "✍️ *Permintaan Tanda Tangan HPP (reissued)*\nNo: {$hpp->notification_number}\nRole: {$signType}\nKlik: " . $svc->url($newTokId) . "\n_Link berlaku 30 hari_",
                ]);
        } catch (\Throwable $e) {
            Log::warning("[HPP] WA notify reissue failed: {$e->getMessage()}", ['notif' => $notification_number, 'user' => $targetUser->id]);
        }

        // return json (for JS)
        return response()->json([
            'ok' => true,
            'token_id' => $newTokId,
            'url' => $svc->url($newTokId),
            'expires_at' => HppApprovalToken::find($newTokId)->expires_at ?? null,
            'target_user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'whatsapp' => $targetUser->whatsapp_number,
            ],
        ], Response::HTTP_OK);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['error' => 'HPP tidak ditemukan.'], Response::HTTP_NOT_FOUND);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('[HPP] reissueToken failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json(['error' => 'Terjadi kesalahan saat menerbitkan token.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

}
