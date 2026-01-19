<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::user()->usertype != 'admin')
        {
            return redirect('dashboard');
        }

        $routeName = $request->route() ? $request->route()->getName() : null;
        if ($routeName) {
            $routeMap = config('admin_permissions.route_map', []);
            foreach ($routeMap as $permissionKey => $patterns) {
                foreach ((array) $patterns as $pattern) {
                    if (Str::is($pattern, $routeName)) {
                        if (!Auth::user()->hasAdminPermission($permissionKey)) {
                            abort(403);
                        }
                        break 2;
                    }
                }
            }
        }
        return $next($request);
    }
}
