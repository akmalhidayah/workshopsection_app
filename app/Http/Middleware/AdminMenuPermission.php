<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMenuPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user->usertype !== 'admin') {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : null;
        if (!$routeName) {
            return $next($request);
        }

        if (str_starts_with($routeName, 'notifications.')) {
            $tab = (string) $request->query('tab');
            if ($tab === 'kawatlas') {
                if (!$user->hasAdminPermission('admin.order.kawatlas')) {
                    abort(403);
                }
            } elseif ($tab === 'notif') {
                if (!$user->hasAdminPermission('admin.order.jasa')) {
                    abort(403);
                }
            } else {
                if (!$user->hasAnyAdminPermission(['admin.order.jasa', 'admin.order.kawatlas'])) {
                    abort(403);
                }
            }
        }

        return $next($request);
    }
}
