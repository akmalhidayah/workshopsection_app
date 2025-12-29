<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class WelcomeController extends Controller
{
    /**
     * Disk publik yang digunakan.
     */
    protected string $disk = 'public';

    /**
     * Ekstensi file yang diizinkan.
     */
    protected array $allowedExtensions = [
        'pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'svg', 'gif'
    ];

    /**
     * Cache TTL (detik).
     */
    protected int $cacheTtl = 600; // 10 menit

    /**
     * ===============================
     * LANDING PAGE
     * ===============================
     */
    public function index(Request $request)
    {
        try {
            // ===============================
            // CARA KERJA (ROLE-BASED)
            // ===============================
            $caraKerjaPns      = $this->getFilesFromFolder('uploads/info/cara_kerja/pns');
            $caraKerjaPkm      = $this->getFilesFromFolder('uploads/info/cara_kerja/pkm');
            $caraKerjaApproval = $this->getFilesFromFolder('uploads/info/cara_kerja/approval');

            // ===============================
            // DOKUMEN UMUM
            // ===============================
            $flowchartFiles = $this->getFilesFromFolder('uploads/info/flowchart_aplikasi');
            $kontrakFiles   = $this->getFilesFromFolder('uploads/info/kontrak_pkm');

            // ===============================
            // API RESPONSE
            // ===============================
            if ($request->wantsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'cara_kerja' => [
                        'pns'      => $caraKerjaPns,
                        'pkm'      => $caraKerjaPkm,
                        'approval' => $caraKerjaApproval,
                    ],
                    'flowchart'   => $flowchartFiles,
                    'kontrak_pkm' => $kontrakFiles,
                ], Response::HTTP_OK);
            }

            // ===============================
            // VIEW RESPONSE
            // ===============================
            return response()->view(
                'welcome',
                compact(
                    'caraKerjaPns',
                    'caraKerjaPkm',
                    'caraKerjaApproval',
                    'flowchartFiles',
                    'kontrakFiles'
                ),
                Response::HTTP_OK
            );

        } catch (\Throwable $e) {

            Log::error('WelcomeController@index failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // API error
            if ($request->wantsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'error' => 'Gagal memuat data welcome page.'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // View fallback (agar Blade tidak pecah)
            return response()->view(
                'welcome',
                [
                    'caraKerjaPns'      => [],
                    'caraKerjaPkm'      => [],
                    'caraKerjaApproval' => [],
                    'flowchartFiles'    => [],
                    'kontrakFiles'      => [],
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * ===============================
     * AMBIL FILE DARI FOLDER (CACHED)
     * ===============================
     */
    protected function getFilesFromFolder(string $folder): array
    {
        $cacheKey = 'welcome_files_' . md5($this->disk . '|' . $folder);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($folder) {

            if (!Storage::disk($this->disk)->exists($folder)) {
                Log::info('WelcomeController: folder not found', [
                    'disk'   => $this->disk,
                    'folder' => $folder,
                ]);
                return [];
            }

            return collect(Storage::disk($this->disk)->files($folder))
                ->filter(fn ($path) =>
                    in_array(
                        strtolower(pathinfo($path, PATHINFO_EXTENSION)),
                        $this->allowedExtensions,
                        true
                    )
                )
                ->map(function ($path) {
                    try {
                        return [
                            'path' => $path,
                            'url'  => Storage::disk($this->disk)->url($path),
                            'name' => basename($path),
                            'ext'  => strtolower(pathinfo($path, PATHINFO_EXTENSION)),
                            'size' => $this->safeFileSize($path),
                        ];
                    } catch (\Throwable $e) {
                        Log::warning('File metadata read failed', [
                            'path'  => $path,
                            'error' => $e->getMessage(),
                        ]);
                        return null;
                    }
                })
                ->filter() // buang null
                ->values()
                ->all();
        });
    }

    /**
     * ===============================
     * AMBIL UKURAN FILE (AMAN)
     * ===============================
     */
    protected function safeFileSize(string $path): ?int
    {
        try {
            return Storage::disk($this->disk)->size($path);
        } catch (\Throwable $e) {
            Log::debug('safeFileSize failed', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
