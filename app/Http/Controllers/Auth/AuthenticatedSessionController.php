<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Route; // <- ditambahkan
use Illuminate\Support\Facades\Log;

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

        $user = $request->user();

        try {
            // Admin - langsung ke dashboard admin
            if ($user->usertype === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Approval - kita coba beberapa fallback tanpa melempar exception
            if ($user->usertype === 'approval') {
                // coba arahkan ke named route approval.index (kalau ada), kirim unit_work jika tersedia
                try {
                    // Lebih aman memasukkan parameter di dalam try/catch.
                    // Kalau route tidak ada, akan masuk ke catch.
                    $params = [];
                    if (!empty($user->unit_work)) {
                        $params['unit_work'] = $user->unit_work;
                    }

                    if (Route::has('approval.index')) {
                        return redirect()->intended(route('approval.index', $params));
                    }

                    // fallback: approval.hpp.index jika ada
                    if (Route::has('approval.hpp.index')) {
                        return redirect()->intended(route('approval.hpp.index'));
                    }

                    // fallback berikutnya: admin dashboard
                    return redirect()->intended(route('admin.dashboard'));
                } catch (\Throwable $e) {
                    // Log error kecil agar mudah ditrace bila terjadi sesuatu di redirect
                    Log::warning('Redirect to approval route failed: ' . $e->getMessage());
                    // Pastikan tetap redirect ke sesuatu yang valid
                    if (Route::has('approval.hpp.index')) {
                        return redirect()->intended(route('approval.hpp.index'));
                    }
                    return redirect()->intended(route('admin.dashboard'));
                }
            }

            // PKM
            if ($user->usertype === 'pkm') {
                return redirect()->route('pkm.dashboard');
            }

            // Default: user biasa ke dashboard
            return redirect()->intended(route('dashboard'));
        } catch (\Throwable $e) {
            // Kalau ada error tak terduga selama proses redirect, log dan fallback ke dashboard utama
            Log::error('Auth redirect error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('dashboard');
        }
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
