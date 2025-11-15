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
     * 
     * Redirección inteligente según tipo de usuario:
     * - Super Admin (sin tenant_id) → admin.dashboard
     * - Usuarios con tenant_id (admin, supervisor, agent) → tenant.dashboard
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();

        // Super Admin → admin.dashboard (acceso global)
        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        }

        // TODOS los usuarios con tenant_id → tenant.dashboard
        // Incluye: admin, supervisor, agent, viewer
        if ($user->tenant_id) {
            return redirect()->route('tenant.dashboard');
        }

        // Fallback (no debería llegar aquí en condiciones normales)
        return redirect('/');
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