<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitWork;
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
            $q = $request->input('q');
            $entries = (int) $request->input('entries', 10);
            $allowed = [10, 25, 50, 100];
            if (!in_array($entries, $allowed, true)) $entries = 10;

            $units = UnitWork::query()
                ->when($q, function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                          ->orWhereRaw("JSON_SEARCH(seksi, 'one', ?) IS NOT NULL", ["%{$q}%"]);
                })
                ->orderBy('name')
                ->paginate($entries)
                ->withQueryString();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'OK',
                    'data'    => $units,
                ], 200);
            }

            return response()
                ->view('admin.unit_work.index', compact('units'), 200);

        } catch (Throwable $e) {
            Log::error('UnitWork index failed', ['error' => $e->getMessage()]);
            return $this->respondError($request, 'Gagal memuat data unit kerja.', 500);
        }
    }

    public function create(Request $request)
    {
        // halaman form create tidak butuh transaksi
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Endpoint ini hanya untuk tampilan form.',
            ], 405);
        }

        return response()->view('admin.unit_work.create', [], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'  => ['required', 'string', 'max:255', Rule::unique('unit_work', 'name')],
                // 'seksi' dikirim JSON string dari hidden input (opsional)
            ]);

            $seksi = $this->parseSeksiJson($request->input('seksi'));

            DB::beginTransaction();
            $unit = UnitWork::create([
                'name'  => trim((string) $validated['name']),
                'seksi' => $seksi,
            ]);
            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Unit kerja berhasil ditambahkan.',
                    'data'    => $unit,
                ], 201);
            }

            return redirect()
                ->route('admin.unit_work.index')
                ->with('success', 'Unit kerja berhasil ditambahkan.')
                ->setStatusCode(201);

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('UnitWork store query error', ['error' => $e->getMessage()]);
            return $this->respondError($request, 'Gagal menyimpan data (database).', 500);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UnitWork store failed', ['error' => $e->getMessage()]);
            return $this->respondError($request, 'Terjadi kesalahan saat menyimpan.', 500);
        }
    }

    public function edit(Request $request, UnitWork $unitWork)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'OK',
                'data'    => $unitWork,
            ], 200);
        }

        return response()->view('admin.unit_work.edit', compact('unitWork'), 200);
    }

    public function update(Request $request, UnitWork $unitWork)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('unit_work', 'name')->ignore($unitWork->id)],
            ]);

            $seksi = $this->parseSeksiJson($request->input('seksi'));

            DB::beginTransaction();
            $unitWork->update([
                'name'  => trim((string) $validated['name']),
                'seksi' => $seksi,
            ]);
            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Unit kerja berhasil diperbarui.',
                    'data'    => $unitWork->fresh(),
                ], 200);
            }

            return redirect()
                ->route('admin.unit_work.index')
                ->with('success', 'Unit kerja berhasil diperbarui.')
                ->setStatusCode(200);

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('UnitWork update query error', ['id' => $unitWork->id, 'error' => $e->getMessage()]);
            return $this->respondError($request, 'Gagal memperbarui data (database).', 500);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UnitWork update failed', ['id' => $unitWork->id, 'error' => $e->getMessage()]);
            return $this->respondError($request, 'Terjadi kesalahan saat memperbarui.', 500);
        }
    }

    public function destroy(Request $request, UnitWork $unitWork)
    {
        try {
            DB::beginTransaction();
            $unitWork->delete();
            DB::commit();

            if ($request->wantsJson()) {
                // No content untuk delete
                return response()->json([
                    'message' => 'Unit kerja berhasil dihapus.',
                ], 200);
            }

            return redirect()
                ->route('admin.unit_work.index')
                ->with('success', 'Unit kerja berhasil dihapus.')
                ->setStatusCode(200);

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('UnitWork destroy query error', ['id' => $unitWork->id, 'error' => $e->getMessage()]);
            return $this->respondError($request, 'Gagal menghapus data (database).', 500);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UnitWork destroy failed', ['id' => $unitWork->id, 'error' => $e->getMessage()]);
            return $this->respondError($request, 'Terjadi kesalahan saat menghapus.', 500);
        }
    }

    /**
     * Parse hidden input JSON "seksi" menjadi array string yang rapi:
     * - JSON decode aman (fallback [])
     * - trim spasi, buang kosong
     * - unik (preserve order)
     */
    private function parseSeksiJson(?string $json): array
    {
        if (!$json) return [];
        // Toleran terhadap string non-JSON: misal "a,b,c"
        $arr = json_decode($json, true);
        if (!is_array($arr)) {
            // fallback split by comma
            $arr = array_map('trim', explode(',', (string) $json));
        }

        $clean = [];
        foreach ($arr as $item) {
            if (!is_string($item)) continue;
            $val = trim($item);
            if ($val === '') continue;
            $clean[] = $val;
        }

        // unik + jaga urutan
        $unique = [];
        foreach ($clean as $v) {
            if (!in_array($v, $unique, true)) $unique[] = $v;
        }
        return $unique;
    }

    /**
     * Helper: konsistenkan error response (JSON vs Redirect) dengan status code.
     */
    private function respondError(Request $request, string $message, int $statusCode)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
            ], $statusCode);
        }

        // Kembali ke halaman sebelumnya bila ada, dengan flash error.
        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['error' => $message])
            ->setStatusCode($statusCode);
    }
}
