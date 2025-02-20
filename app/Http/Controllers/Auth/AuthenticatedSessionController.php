<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
    
    // Redirect berdasarkan usertype
    if ($request->user()->usertype === 'admin') {
        // Redirect ke halaman terakhir yang diakses atau default ke dashboard admin
        return redirect()->intended(route('admin.dashboard'));
        } elseif ($request->user()->usertype === 'approval') {
            // Redirect to the last accessed page or fallback to '/approval'
            return redirect()->intended(route('approval.index', ['unit_work' => $request->user()->unit_work]));
        } elseif ($request->user()->usertype === 'pkm') {
            return redirect()->route('pkm.dashboard');
        }
    
        return redirect()->intended(route('dashboard'));
    }
    
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
    
}
