<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SignatureService
{
    /** Simpan dataURI -> file pada disk 'signatures'. return: ['path','mime','size'] */
    public function storeBase64(string $dataUri, string $entity, string $key, string $id): array
    {
        if (!str_starts_with($dataUri, 'data:image/')) abort(422, 'Format tanda tangan tidak valid.');
        if (!preg_match('#^data:image/(png|jpeg);base64,#i', $dataUri, $m)) abort(422, 'Hanya PNG/JPEG.');

        $ext  = strtolower($m[1]) === 'jpeg' ? 'jpg' : 'png';
        $mime = $ext === 'jpg' ? 'image/jpeg' : 'image/png';
        $b64  = substr($dataUri, strpos($dataUri, ',') + 1);
        $bin  = base64_decode($b64, true);
        if ($bin === false) abort(422, 'Data tanda tangan rusak.');

        $max = (int) env('SIGNATURE_MAX_KB', 500) * 1024;
        if (strlen($bin) > $max) abort(413, 'Ukuran tanda tangan terlalu besar.');

        $hash = substr(hash('sha256', $bin), 0, 16);
        $filename = sprintf('%s_%s_%s_%s.%s', $entity, $id, $key, now()->format('YmdHis')."_{$hash}", $ext);
        $path = "hpp/{$filename}";

        Storage::disk('signatures')->put($path, $bin);

        return ['path' => $path, 'mime' => $mime, 'size' => strlen($bin)];
    }

    public function deleteIfExists(?string $path): void
    {
        if ($path) Storage::disk('signatures')->delete($path);
    }

    public function streamUrl(string $path): string
    {
        return route('signatures.stream', ['p' => base64_encode($path)]);
    }
    public function streamFile(string $path)
{
    // harden: tolak path aneh
    if ($path === '' || str_contains($path, '..')) {
        abort(404);
    }

    if (!\Illuminate\Support\Facades\Storage::disk('signatures')->exists($path)) {
        abort(404);
    }

    $disk = \Illuminate\Support\Facades\Storage::disk('signatures');
    $mime = $disk->mimeType($path) ?: (str_ends_with($path, '.jpg') ? 'image/jpeg' : 'image/png');
    $bin  = $disk->get($path);

    return response($bin, 200, [
        'Content-Type'  => $mime,
        'Cache-Control' => 'private, max-age=0, no-cache',
    ]);
}

}
