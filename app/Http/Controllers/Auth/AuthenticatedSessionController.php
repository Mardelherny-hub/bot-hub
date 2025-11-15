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

        // Redirección inteligente según el rol del usuario
        $user = auth()->user();

        // Super Admin → admin.dashboard
        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Admin o Supervisor → tenant.dashboard
        if ($user->hasRole(['admin', 'supervisor'])) {
            return redirect()->route('tenant.dashboard');
        }

        // Agente o Viewer → agent.dashboard
        return redirect()->route('agent.dashboard');
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
