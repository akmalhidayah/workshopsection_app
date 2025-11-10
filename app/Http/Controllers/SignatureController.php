<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SignatureService;

class SignatureController extends Controller
{
    public function stream(Request $req, SignatureService $svc)
    {
        $p = $req->query('p');          // base64-encoded path
        if (!$p) abort(404);

        $path = base64_decode($p, true);
        if (!$path) abort(404);

        return $svc->streamFile($path); // ⬅️ gunakan service
    }
}
