<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ApprovalMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->usertype !== 'approval') {
            return redirect('dashboard');
        }
        return $next($request);
    }
}
