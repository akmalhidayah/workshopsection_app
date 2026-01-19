<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LHPP;
use App\Models\Hpp1;
use App\Models\Hpp2;
use App\Models\Hpp3;
use App\Models\Hpp4;
use App\Models\User;
use App\Models\Notification;
use App\Models\UnitWork;
use App\Models\UnitWorkSection;
use App\Models\SystemNotification;
use App\Services\LHPPApprovalLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LHPPApprovalController extends Controller
{
    /**
     * (Opsional) List LHPP untuk Manager PKM (berdasarkan kontrak yang dia pegang).
     * status_approve di sini hanya untuk filter tampilan admin/PKM,
     * TIDAK dipakai di flow token approval.
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (! $user) {
                abort(Response::HTTP_UNAUTHORIZED, 'Silakan login terlebih dahulu.');
            }

            if (! $this->isPkmManager($user)) {
                Log::warning('[LHPP][PKM] Akses index approval ditolak (bukan manager PKM berdasar struktur org)', [
                    'user_id' => $user->id ?? null,
                ]);
                abort(Response::HTTP_FORBIDDEN, 'Anda bukan Manager PKM.');
            }

            // Manager PKM bisa pegang beberapa seksi (Fabrikasi / Konstruksi / Pengerjaan Mesin)
            $kontrakList = $this->getPkmManagedContracts($user); // array nama seksi di PKM

            if (empty($kontrakList)) {
                // Secara teori tidak masuk ke sini kalau isPkmManager() true,
                // tapi jaga-jaga saja.
                $lhpps  = LHPP::whereRaw('1 = 0')->paginate(15);
                $status = $request->query('status', 'pending');

                return view('pkm.lhpp.approval_index', [
                    'lhpps'  => $lhpps,
                    'status' => $status,
                ]);
            }

            $status = $request->query('status', 'pending'); // pending/approved/rejected/all

            $lhppQuery = LHPP::query()
                ->whereIn('kontrak_pkm', $kontrakList)
                ->orderByDesc('created_at');

            if ($status !== 'all') {
                $lhppQuery->where('status_approve', $status);
            }

            $lhpps = $lhppQuery->paginate(15);

            return view('pkm.lhpp.approval_index', [
                'lhpps'  => $lhpps,
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            Log::error('[LHPP][PKM] Gagal load index approval', [
                'err' => $e->getMessage(),
            ]);
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat membuka daftar LHPP.');
        }
    }

    /**
     * Tampilkan halaman approval via TOKEN + canvas tanda tangan.
     * Urutan role:
     *  - manager_user
     *  - manager_workshop
     *  - manager_pkm
     *
     * Flow urutan pakai cek TTD, BUKAN status_approve.
     */
    public function show(string $token, LHPPApprovalLinkService $svc)
    {
        try {
            // 1. Validasi token (ada, belum used, belum expired)
            $link = $svc->validate($token);
            $user = auth()->user();

            // 2. Pastikan token ditujukan ke user login
            if (! $user || (int) $link->user_id !== (int) $user->id) {
                Log::warning('[LHPP] Token bukan milik user login', [
                    'token_id'  => $link->id ?? null,
                    'link_user' => $link->user_id ?? null,
                    'auth_user' => $user->id ?? null,
                ]);
                abort(Response::HTTP_FORBIDDEN, 'Token ini bukan untuk akun Anda.');
            }

            // 3. Ambil LHPP
            $lhpp = LHPP::where('notification_number', $link->notification_number)->firstOrFail();

            // 4. Cek apakah user berhak sign untuk sign_type ini (berdasar struktur organisasi)
            if (! $this->canSignForType($user, $lhpp, $link->sign_type)) {
                Log::warning('[LHPP] User tidak berhak sign LHPP', [
                    'user_id'   => $user->id ?? null,
                    'notif'     => $lhpp->notification_number,
                    'sign_type' => $link->sign_type,
                ]);
                abort(Response::HTTP_FORBIDDEN, 'Anda tidak berhak menyetujui LHPP ini.');
            }

            // 5. Validasi urutan approval berbasis TTD, BUKAN status_approve
            if (! $this->canProceedForSignType($lhpp, $link->sign_type)) {
                Log::warning('[LHPP] Urutan approval belum sesuai', [
                    'notif'     => $lhpp->notification_number,
                    'sign_type' => $link->sign_type,
                ]);
                abort(
                    Response::HTTP_FORBIDDEN,
                    'Urutan approval belum sesuai. Pastikan langkah sebelumnya sudah ditandatangani.'
                );
            }

            // 6. Cek apakah user ini punya TTD lama untuk sign_type ini (base64 yg sudah tersimpan)
            $hasOldSignature = $this->hasOldSignatureForUser($user, $link->sign_type);

            // 7. Label untuk ditampilkan di view
            $label = $this->labelForSignType($link->sign_type);

            // 8. URL PDF untuk preview (PAKAI ROUTE APPROVAL, BUKAN PKM)
            $pdfUrl = route('approval.lhpp.download_pdf', $lhpp->notification_number);

            // 9. Cari HPP terkait (HPP1/2/3/4) berdasarkan notification_number
            $hpp = $this->findHppForNotification($lhpp->notification_number);

            return view('pkm.lhpp.approval', [
                'lhpp'            => $lhpp,
                'pdfUrl'          => $pdfUrl,
                'token'           => $token,
                'sign_type'       => $link->sign_type,
                'signTypeLabel'   => $label,
                'hasOldSignature' => $hasOldSignature,
                'hpp'             => $hpp, // supaya Blade bisa nampilin panel HPP
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('[LHPP] LHPP tidak ditemukan saat show()', [
                'token' => $token,
                'err'   => $e->getMessage(),
            ]);
            abort(Response::HTTP_NOT_FOUND, 'Dokumen LHPP tidak ditemukan.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // abort() akan melempar HttpException — biarkan lewat apa adanya
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[LHPP] Gagal membuka halaman approval', [
                'token' => $token,
                'err'   => $e->getMessage(),
            ]);
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Terjadi kesalahan saat membuka halaman approval LHPP.');
        }
    }

    /**
     * Proses submit APPROVE / REJECT via TOKEN.
     * Urutan step:
     *  - manager_user      → lanjut manager_workshop
     *  - manager_workshop  → lanjut manager_pkm
     *  - manager_pkm       → akhir
     *
     * PENTING: TIDAK menyentuh status_approve.
     */
    public function sign(string $token, Request $request, LHPPApprovalLinkService $svc)
    {
        // Sama konsepnya seperti HPP: boleh pakai TTD lama atau gambar baru
        $rules = [
            'action'            => 'required|in:approve,reject',
            'reason'            => 'nullable|string|max:500',
            'note'              => 'nullable|string|max:1000',
            'note_target'       => 'nullable|in:controlling,requesting',
            'use_old_signature' => 'nullable|boolean',
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

            if (! $user || (int) $link->user_id !== (int) $user->id) {
                Log::warning('[LHPP] Token bukan milik user login (sign)', [
                    'token_id'  => $link->id ?? null,
                    'link_user' => $link->user_id ?? null,
                    'auth_user' => $user->id ?? null,
                ]);
                abort(Response::HTTP_FORBIDDEN, 'Token ini bukan untuk akun Anda.');
            }

            $lhpp = LHPP::where('notification_number', $link->notification_number)->firstOrFail();

            if (! $this->canSignForType($user, $lhpp, $link->sign_type)) {
                Log::warning('[LHPP] User tidak berhak sign LHPP (sign)', [
                    'user_id'   => $user->id ?? null,
                    'notif'     => $lhpp->notification_number,
                    'sign_type' => $link->sign_type,
                ]);
                abort(Response::HTTP_FORBIDDEN, 'Anda tidak berhak menyetujui LHPP ini.');
            }

            if (! $this->canProceedForSignType($lhpp, $link->sign_type)) {
                Log::warning('[LHPP] Urutan approval belum sesuai (sign)', [
                    'notif'     => $lhpp->notification_number,
                    'sign_type' => $link->sign_type,
                ]);
                abort(Response::HTTP_FORBIDDEN, 'Urutan approval belum sesuai.');
            }

            // Cegah double sign untuk role yang sama
            if ($this->signatureAlreadyFilled($lhpp, $link->sign_type)) {
                Log::info('[LHPP] Tanda tangan untuk role ini sudah terisi', [
                    'notif'     => $lhpp->notification_number,
                    'sign_type' => $link->sign_type,
                ]);
                abort(Response::HTTP_FORBIDDEN, 'LHPP ini sudah ditandatangani untuk peran ini.');
            }

            // === Mapping kolom sesuai role LHPP ===
            $map = [
                'manager_user' => [
                    'field'      => 'manager_signature_requesting',
                    'user_field' => 'manager_signature_requesting_user_id',
                ],
                'manager_workshop' => [
                    'field'      => 'manager_signature',
                    'user_field' => 'manager_signature_user_id',
                ],
                'manager_pkm' => [
                    'field'      => 'manager_pkm_signature',
                    'user_field' => 'manager_pkm_signature_user_id',
                ],
            ];

            if (! isset($map[$link->sign_type])) {
                Log::error('[LHPP] sign_type tidak dikenali', [
                    'notif' => $lhpp->notification_number,
                    'type'  => $link->sign_type,
                ]);
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Jenis penandatangan tidak dikenali.');
            }

            $cfg = $map[$link->sign_type];

            // === Tentukan value signature: pakai TTD lama ATAU base64 baru ===
            if ($request->boolean('use_old_signature')) {
                // Cari TTD terakhir user ini untuk role/sign_type ini (base64)
                $last = LHPP::where($cfg['user_field'], $user->id)
                    ->whereNotNull($cfg['field'])
                    ->orderByDesc('created_at')
                    ->first();

                if (! $last) {
                    return back()
                        ->withErrors(['signature_base64' => 'Tanda tangan lama tidak ditemukan. Silakan gambar tanda tangan baru.'])
                        ->withInput()
                        ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $signatureValue = $last->{$cfg['field']};
            } else {
                $signatureValue = $request->input('signature_base64');
            }

            // === Tentukan field notes & siapkan entry untuk di-append ===
            $noteTarget = $request->input('note_target');
            if (! $noteTarget) {
                // default: pengendali (PKM/Workshop) ke controlling, peminta ke requesting
                $noteTarget = in_array($link->sign_type, ['manager_pkm', 'manager_workshop'])
                    ? 'controlling'
                    : 'requesting';
            }

            $noteField = $noteTarget === 'requesting' ? 'requesting_notes' : 'controlling_notes';

            $notesExisting = is_array($lhpp->$noteField) ? $lhpp->$noteField : [];

            $noteText = trim((string) $request->input('note', ''));
            if ($noteText !== '') {
                $notesExisting[] = [
                    'note'    => $noteText,
                    'user_id' => (int) $user->id,
                    'at'      => now()->toDateTimeString(),
                ];
            }

            $isApprove = $request->action === 'approve';

            // === Update LHPP: HANYA field TTD + user_id + notes ===
            $update = [
                $cfg['field']      => $signatureValue,
                $cfg['user_field'] => $user->id,
                $noteField         => $notesExisting,
            ];

            $lhpp->update($update);

            // Tandai token terpakai
            $svc->markUsed($link);

            // === Kalau APPROVE, issue token untuk approver berikutnya (kalau ada) ===
            if ($isApprove) {
                if ($next = $this->nextApprover($link->sign_type, $lhpp)) {
                    $nextUser = $this->findApproverUser($next['key'], $lhpp);

                    if (! $nextUser) {
                        Log::warning('[LHPP] Approver berikutnya tidak ditemukan (cek struktur organisasi)', [
                            'notif' => $lhpp->notification_number,
                            'role'  => $next['role'],
                            'type'  => $next['key'],
                        ]);
                    } else {
                        try {
                            $nextTok = $svc->issue(
                                $lhpp->notification_number,
                                $next['key'],
                                $nextUser->id,
                                60 * 24 * 30 // 30 hari
                            );

                        } catch (\Throwable $e) {
                            Log::error('[LHPP] Gagal issue token approver berikutnya', [
                                'notif' => $lhpp->notification_number,
                                'user'  => $nextUser->id ?? null,
                                'err'   => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

            // === Setelah semua proses sign ===
            $pdfUrl = route('approval.lhpp.download_pdf', $lhpp->notification_number);

            if ($isApprove) {
                // 🎯 langsung arahkan ke PDF
                return redirect()->to($pdfUrl);
            }

            // ❌ kalau ditolak tetap kembali ke index
            return redirect()
                ->route('pkm.lhpp.index')
                ->with('warning', 'LHPP ditolak.')
                ->setStatusCode(Response::HTTP_OK);


        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('[LHPP] LHPP tidak ditemukan saat sign()', [
                'token' => $token,
                'err'   => $e->getMessage(),
            ]);
            return redirect()
                ->route('pkm.lhpp.index')
                ->with('error', 'Dokumen LHPP tidak ditemukan.')
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[LHPP] Gagal memproses tanda tangan LHPP', [
                'token' => $token,
                'err'   => $e->getMessage(),
            ]);
            return redirect()
                ->route('pkm.lhpp.index')
                ->with('error', 'Terjadi kesalahan saat memproses tanda tangan LHPP.')
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /* ================= HELPER ROLE & ORG ================= */

    /**
     * Cek apakah user ini adalah salah satu Manager PKM (manager seksi
     * di unit "PT. Prima Karya Manunggal").
     */
    private function isPkmManager(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $contracts = $this->getPkmManagedContracts($user);

        return ! empty($contracts);
    }

    /**
     * Ambil daftar nama seksi PKM (Fabrikasi / Konstruksi / Pengerjaan Mesin)
     * yang dimanage oleh user ini.
     */
    private function getPkmManagedContracts(User $user): array
    {
        $pkmUnit = UnitWork::where('name', 'PT. Prima Karya Manunggal')->first();

        if (! $pkmUnit) {
            return [];
        }

        return $pkmUnit->sections()
            ->where('manager_id', $user->id)
            ->pluck('name')
            ->all();
    }

    /**
     * Validasi generic: boleh sign untuk sign_type tertentu?
     * Sekarang berbasis struktur organisasi, BUKAN jabatan/usertype.
     */
    private function canSignForType(User $user, LHPP $lhpp, string $signType): bool
    {
        $expected = match ($signType) {
            'manager_user'      => $this->getManagerUserForLhpp($lhpp),
            'manager_workshop'  => $this->getManagerWorkshopForLhpp($lhpp),
            'manager_pkm'       => $this->getManagerPkmForLhpp($lhpp),
            default             => null,
        };

        if (! $expected) {
            // konfigurasi struktur organisasi tidak lengkap
            Log::warning('[LHPP] Expected approver tidak ditemukan (struktur org belum lengkap)', [
                'notif'     => $lhpp->notification_number,
                'sign_type' => $signType,
            ]);
            return false;
        }

        return (int) $expected->id === (int) $user->id;
    }

    /**
     * Cek apakah tanda tangan untuk role ini sudah terisi (hindari double-sign).
     */
    private function signatureAlreadyFilled(LHPP $lhpp, string $signType): bool
    {
        return match ($signType) {
            'manager_user' =>
                ! empty($lhpp->manager_signature_requesting) ||
                ! empty($lhpp->manager_signature_requesting_user_id),

            'manager_workshop' =>
                ! empty($lhpp->manager_signature) ||
                ! empty($lhpp->manager_signature_user_id),

            'manager_pkm' =>
                ! empty($lhpp->manager_pkm_signature) ||
                ! empty($lhpp->manager_pkm_signature_user_id),

            default => false,
        };
    }

    /**
     * Cek urutan approval berdasar TTD, bukan status_approve.
     * - manager_user      : step 1 (selalu boleh kalau belum sign)
     * - manager_workshop  : butuh manager_user sudah sign
     * - manager_pkm       : butuh manager_user & manager_workshop sudah sign
     */
    private function canProceedForSignType(LHPP $lhpp, string $signType): bool
    {
        $userSigned = ! empty($lhpp->manager_signature_requesting) ||
                      ! empty($lhpp->manager_signature_requesting_user_id);

        $workshopSigned = ! empty($lhpp->manager_signature) ||
                          ! empty($lhpp->manager_signature_user_id);

        $pkmSigned = ! empty($lhpp->manager_pkm_signature) ||
                     ! empty($lhpp->manager_pkm_signature_user_id);

        return match ($signType) {
            'manager_user'      => ! $userSigned,
            'manager_workshop'  => $userSigned && ! $workshopSigned,
            'manager_pkm'       => $userSigned && $workshopSigned && ! $pkmSigned,
            default             => false,
        };
    }

    /**
     * Cek apakah user ini sudah punya TTD lama untuk sign_type tertentu di LHPP lain.
     */
    private function hasOldSignatureForUser(User $user, string $signType): bool
    {
        $map = [
            'manager_user' => [
                'field'      => 'manager_signature_requesting',
                'user_field' => 'manager_signature_requesting_user_id',
            ],
            'manager_workshop' => [
                'field'      => 'manager_signature',
                'user_field' => 'manager_signature_user_id',
            ],
            'manager_pkm' => [
                'field'      => 'manager_pkm_signature',
                'user_field' => 'manager_pkm_signature_user_id',
            ],
        ];

        if (! isset($map[$signType])) {
            return false;
        }

        $cfg = $map[$signType];

        return LHPP::where($cfg['user_field'], $user->id)
            ->whereNotNull($cfg['field'])
            ->exists();
    }

    /** Label manis untuk ditampilkan di view */
    private function labelForSignType(string $signType): string
    {
        return match ($signType) {
            'manager_user'      => 'Manager User (Unit Kerja Peminta)',
            'manager_workshop'  => 'Manager Workshop',
            'manager_pkm'       => 'Manager PKM (Quality Control)',
            default             => ucfirst($signType),
        };
    }

    /**
     * Tentukan approver berikutnya berdasarkan sign_type saat ini.
     * Urutan:
     *  - manager_user     -> manager_workshop
     *  - manager_workshop -> manager_pkm
     *  - manager_pkm      -> null (final)
     */
    private function nextApprover(string $current, LHPP $lhpp): ?array
    {
        return match ($current) {
            'manager_user' => [
                'key'  => 'manager_workshop',
                'role' => 'Manager Workshop',
            ],
            'manager_workshop' => [
                'key'  => 'manager_pkm',
                'role' => 'Manager PKM',
            ],
            'manager_pkm' => null,
            default       => null,
        };
    }

    /**
     * Cari user approver untuk sign_type tertentu, mengikuti **struktur organisasi**.
     */
    private function findApproverUser(string $signType, LHPP $lhpp): ?User
    {
        return match ($signType) {
            'manager_user'      => $this->getManagerUserForLhpp($lhpp),
            'manager_workshop'  => $this->getManagerWorkshopForLhpp($lhpp),
            'manager_pkm'       => $this->getManagerPkmForLhpp($lhpp),
            default             => null,
        };
    }

    /* ================== RESOLVER APPROVER BERDASAR STRUKTUR ORG ================== */

    /**
     * Manager User:
     * LHPP -> Notification (unit_work + seksi) -> UnitWork -> UnitWorkSection -> manager
     */
    private function getManagerUserForLhpp(LHPP $lhpp): ?User
    {
        $notification = Notification::where('notification_number', $lhpp->notification_number)->first();

        if (! $notification) {
            Log::warning('[LHPP][Resolver] Notification tidak ditemukan', [
                'notif' => $lhpp->notification_number,
            ]);
            return null;
        }

        if (! $notification->unit_work || ! $notification->seksi) {
            Log::warning('[LHPP][Resolver] unit_work/seksi Notification kosong', [
                'notif'      => $lhpp->notification_number,
                'unit_work'  => $notification->unit_work,
                'seksi'      => $notification->seksi,
            ]);
            return null;
        }

        $unitWork = UnitWork::where('name', $notification->unit_work)->first();

        if (! $unitWork) {
            Log::warning('[LHPP][Resolver] UnitWork tidak ditemukan untuk Manager User', [
                'notif'     => $lhpp->notification_number,
                'unit_work' => $notification->unit_work,
            ]);
            return null;
        }

        /** @var UnitWorkSection|null $section */
        $section = $unitWork->sections()
            ->where('name', $notification->seksi)
            ->first();

        if (! $section) {
            Log::warning('[LHPP][Resolver] Section (seksi) tidak ditemukan untuk Manager User', [
                'notif'     => $lhpp->notification_number,
                'unit_work' => $notification->unit_work,
                'seksi'     => $notification->seksi,
            ]);
            return null;
        }

        if (! $section->manager) {
            Log::warning('[LHPP][Resolver] Manager untuk seksi peminta belum di-set', [
                'notif'  => $lhpp->notification_number,
                'seksi'  => $section->name,
            ]);
            return null;
        }

        return $section->manager;
    }

    /**
     * Manager Workshop:
     * Selalu Manager dari seksi "Section of Machine Workshop"
     * pada unit "Unit of Workshop & Design".
     */
    private function getManagerWorkshopForLhpp(LHPP $lhpp): ?User
    {
        $unitWork = UnitWork::where('name', 'Unit of Workshop & Design')->first();

        if (! $unitWork) {
            Log::warning('[LHPP][Resolver] UnitWork Workshop tidak ditemukan', [
                'notif' => $lhpp->notification_number,
            ]);
            return null;
        }

        /** @var UnitWorkSection|null $section */
        $section = $unitWork->sections()
            ->where('name', 'Section of Machine Workshop')
            ->first();

        if (! $section) {
            Log::warning('[LHPP][Resolver] Section of Machine Workshop tidak ditemukan', [
                'notif' => $lhpp->notification_number,
            ]);
            return null;
        }

        if (! $section->manager) {
            Log::warning('[LHPP][Resolver] Manager Section of Machine Workshop belum di-set', [
                'notif' => $lhpp->notification_number,
            ]);
            return null;
        }

        return $section->manager;
    }

    /**
     * Manager PKM:
     * Unit "PT. Prima Karya Manunggal" + seksi sama dengan `lhpp.kontrak_pkm`
     * (Fabrikasi / Konstruksi / Pengerjaan Mesin).
     */
    private function getManagerPkmForLhpp(LHPP $lhpp): ?User
    {
        if (! $lhpp->kontrak_pkm) {
            Log::warning('[LHPP][Resolver] kontrak_pkm kosong', [
                'notif' => $lhpp->notification_number,
            ]);
            return null;
        }

        $pkmUnit = UnitWork::where('name', 'PT. Prima Karya Manunggal')->first();

        if (! $pkmUnit) {
            Log::warning('[LHPP][Resolver] UnitWork PKM tidak ditemukan', [
                'notif' => $lhpp->notification_number,
            ]);
            return null;
        }

        /** @var UnitWorkSection|null $section */
        $section = $pkmUnit->sections()
            ->where('name', $lhpp->kontrak_pkm)
            ->first();

        if (! $section) {
            Log::warning('[LHPP][Resolver] Section PKM tidak ditemukan untuk kontrak_pkm', [
                'notif'       => $lhpp->notification_number,
                'kontrak_pkm' => $lhpp->kontrak_pkm,
            ]);
            return null;
        }

        if (! $section->manager) {
            Log::warning('[LHPP][Resolver] Manager PKM untuk kontrak_pkm belum di-set', [
                'notif'       => $lhpp->notification_number,
                'kontrak_pkm' => $lhpp->kontrak_pkm,
            ]);
            return null;
        }

        return $section->manager;
    }

    /**
     * Cari HPP terkait notification_number di salah satu tabel HPP1–HPP4.
     * Tidak mengubah flow approval LHPP, hanya untuk lampiran di view.
     */
    private function findHppForNotification(string $notificationNumber)
    {
        foreach ([Hpp1::class, Hpp2::class, Hpp3::class, Hpp4::class] as $modelClass) {
            /** @var \Illuminate\Database\Eloquent\Model|null $found */
            $found = $modelClass::find($notificationNumber);
            if ($found) {
                return $found;
            }
        }

        return null;
    }
}
