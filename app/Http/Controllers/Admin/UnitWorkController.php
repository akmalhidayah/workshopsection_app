<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\UnitWork;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Throwable;

class UnitWorkController extends Controller
{
    public function index(Request $request)
    {
        try {
            $q          = $request->input('q');
            $department = $request->input('department');
            $entries    = (int) $request->input('entries', 10);

            $allowed = [10, 25, 50, 100];
            if (!in_array($entries, $allowed, true)) {
                $entries = 10;
            }

            $units = UnitWork::with([
                    'department.generalManager', // GM
                    'seniorManager',             // SM
                    'sections.manager',          // Manager per seksi
                ])
                ->when($department, function ($query) use ($department) {
                    $query->where('department_id', $department);
                })
                ->search($q) // pakai scope dari model
                ->orderBy('name')
                ->paginate($entries)
                ->withQueryString();

            $departments = Department::orderBy('name')->get();

            return view('admin.unit_work.index', compact(
                'units',
                'departments'
            ));

        } catch (Throwable $e) {
            Log::error('UnitWork index failed', ['error' => $e->getMessage()]);

            return $this->respondError(
                $request,
                'Gagal memuat data unit kerja.',
                500
            );
        }
    }

    public function create(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Endpoint ini hanya untuk tampilan form.',
            ], 405);
        }

        $departments     = Department::orderBy('name')->get();
        $generalManagers = User::generalManagers()
            ->orderBy('name')
            ->get();
        $seniorManagers  = User::seniorManagers()
            ->orderBy('name')
            ->get();

        return response()->view(
            'admin.unit_work.create',
            compact('departments', 'generalManagers', 'seniorManagers'),
            200
        );
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'          => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('unit_work', 'name'),
                ],
                'department_id' => [
                    'required',
                    'integer',
                    'exists:departments,id',
                ],
                'general_manager_id' => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
                'senior_manager_id' => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
            ]);

            $seksi = $this->parseSeksiJson($request->input('seksi'));

            DB::beginTransaction();

            // buat unit kerja baru
            $unit = UnitWork::create([
                'department_id'     => $validated['department_id'],
                'name'              => trim((string) $validated['name']),
                'seksi'             => $seksi,
                'senior_manager_id' => $validated['senior_manager_id'] ?? null,
            ]);

            // kalau di form dikirim GM, update di tabel departments
            if (array_key_exists('general_manager_id', $validated)) {
                $dept = Department::find($validated['department_id']);
                if ($dept) {
                    $dept->general_manager_id = $validated['general_manager_id'] ?? null;
                    $dept->save();
                }
            }

            // (opsional) kalau kedepannya create form juga kirim mapping sections,
            // kita sudah siap sinkronisasi di sini.
            $sectionsInput = $request->input('sections', []);
            $this->syncSectionManagers($unit, $sectionsInput);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Unit kerja berhasil ditambahkan.',
                    'data'    => $unit->load(['department.generalManager', 'seniorManager', 'sections.manager']),
                ], 201);
            }

            return redirect()
                ->route('admin.unit_work.index')
                ->with('success', 'Unit kerja berhasil ditambahkan.');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('UnitWork store query error', ['error' => $e->getMessage()]);

            return $this->respondError(
                $request,
                'Gagal menyimpan data (database).',
                500
            );
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UnitWork store failed', ['error' => $e->getMessage()]);

            return $this->respondError(
                $request,
                'Terjadi kesalahan saat menyimpan.',
                500
            );
        }
    }

    public function edit(Request $request, UnitWork $unitWork)
    {
        // load relasi yang dipakai di preview + manager per seksi
        $unitWork->load(['department.generalManager', 'seniorManager', 'sections.manager']);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'OK',
                'data'    => $unitWork,
            ], 200);
        }

        $departments      = Department::orderBy('name')->get();
        $generalManagers  = User::generalManagers()->orderBy('name')->get();
        $seniorManagers   = User::seniorManagers()->orderBy('name')->get();
        // kandidat manager seksi (user approval)
        $sectionManagers  = User::approval()->orderBy('name')->get();

        return response()->view(
            'admin.unit_work.edit',
            compact('unitWork', 'departments', 'generalManagers', 'seniorManagers', 'sectionManagers'),
            200
        );
    }

    public function update(Request $request, UnitWork $unitWork)
    {
        try {
            $validated = $request->validate([
                'name'          => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('unit_work', 'name')->ignore($unitWork->id),
                ],
                'department_id' => [
                    'required',
                    'integer',
                    'exists:departments,id',
                ],
                'general_manager_id' => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
                'senior_manager_id' => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
            ]);

            $seksi = $this->parseSeksiJson($request->input('seksi'));

            DB::beginTransaction();

            // update unit work (termasuk senior manager & daftar seksi)
            $unitWork->update([
                'department_id'     => $validated['department_id'],
                'name'              => trim((string) $validated['name']),
                'seksi'             => $seksi,
                'senior_manager_id' => $validated['senior_manager_id'] ?? null,
            ]);

            // update GM di tabel departments (untuk departemen yang dipilih)
            if (array_key_exists('general_manager_id', $validated)) {
                $dept = Department::find($validated['department_id']);
                if ($dept) {
                    $dept->general_manager_id = $validated['general_manager_id'] ?? null;
                    $dept->save();
                }
            }

            // sinkronisasi manager per seksi ke tabel unit_work_sections
            $sectionsInput = $request->input('sections', []);
            $this->syncSectionManagers($unitWork, $sectionsInput);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Unit kerja berhasil diperbarui.',
                    'data'    => $unitWork->fresh(['department.generalManager', 'seniorManager', 'sections.manager']),
                ], 200);
            }

            return redirect()
                ->route('admin.unit_work.index')
                ->with('success', 'Unit kerja berhasil diperbarui.');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('UnitWork update query error', [
                'id'    => $unitWork->id,
                'error' => $e->getMessage(),
            ]);

            return $this->respondError(
                $request,
                'Gagal memperbarui data (database).',
                500
            );
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UnitWork update failed', [
                'id'    => $unitWork->id,
                'error' => $e->getMessage(),
            ]);

            return $this->respondError(
                $request,
                'Terjadi kesalahan saat memperbarui.',
                500
            );
        }
    }

    public function destroy(Request $request, UnitWork $unitWork)
    {
        try {
            DB::beginTransaction();

            // hapus juga relasi sections-nya (kalau di DB tidak pakai ON DELETE CASCADE)
            $unitWork->sections()->delete();

            $unitWork->delete();

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Unit kerja berhasil dihapus.',
                ], 200);
            }

            return redirect()
                ->route('admin.unit_work.index')
                ->with('success', 'Unit kerja berhasil dihapus.');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('UnitWork destroy query error', [
                'id'    => $unitWork->id,
                'error' => $e->getMessage(),
            ]);

            return $this->respondError(
                $request,
                'Gagal menghapus data (database).',
                500
            );
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UnitWork destroy failed', [
                'id'    => $unitWork->id,
                'error' => $e->getMessage(),
            ]);

            return $this->respondError(
                $request,
                'Terjadi kesalahan saat menghapus.',
                500
            );
        }
    }

    // ================= HELPER LAINNYA =================

    private function parseSeksiJson(?string $json): array
    {
        if (!$json) {
            return [];
        }

        $arr = json_decode($json, true);
        if (!is_array($arr)) {
            $arr = array_map('trim', explode(',', (string) $json));
        }

        $clean = [];
        foreach ($arr as $item) {
            if (!is_string($item)) {
                continue;
            }

            $val = trim($item);
            if ($val === '') {
                continue;
            }

            $clean[] = $val;
        }

        $unique = [];
        foreach ($clean as $v) {
            if (!in_array($v, $unique, true)) {
                $unique[] = $v;
            }
        }

        return $unique;
    }

    /**
     * Sinkronisasi manager per seksi ke tabel unit_work_sections.
     *
     * @param  \App\Models\UnitWork  $unitWork
     * @param  array                 $sectionsInput  bentuk: [index => ['name' => ..., 'manager_id' => ...], ...]
     */
    private function syncSectionManagers(UnitWork $unitWork, array $sectionsInput): void
    {
        // daftar seksi terbaru (setelah parseSeksiJson & update)
        $currentSeksi = $unitWork->seksi_list; // accessor dari model

        // kalau tidak ada seksi sama sekali -> hapus semua section record
        if (empty($currentSeksi)) {
            $unitWork->sections()->delete();
            return;
        }

        // hapus section yang tidak lagi ada di daftar seksi
        $unitWork->sections()
            ->whereNotIn('name', $currentSeksi)
            ->delete();

        // buat / update section per nama seksi
        foreach ($currentSeksi as $i => $seksiName) {
            $row       = $sectionsInput[$i] ?? [];
            $name      = $row['name'] ?? $seksiName;
            $managerId = $row['manager_id'] ?? null;

            $section = $unitWork->sections()
                ->firstOrNew(['name' => $name]);

            $section->unit_work_id = $unitWork->id;
            $section->manager_id   = $managerId ?: null;
            $section->save();
        }
    }

    private function respondError(Request $request, string $message, int $statusCode)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
            ], $statusCode);
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['error' => $message])
            ->setStatusCode($statusCode);
    }
}
