<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TenantResolver Middleware
 * 
 * Resuelve y valida el tenant del usuario autenticado.
 * Setea el tenant en el contexto de la aplicación.
 * 
 * IMPORTANTE:
 * - Solo se aplica a rutas autenticadas de tenant y agent
 * - NO se aplica a rutas de super_admin (ellos ven todos los tenants)
 * - NO se aplica a rutas públicas
 * 
 * FUNCIONALIDAD:
 * 1. Verifica que el usuario tenga tenant_id
 * 2. Carga el tenant en memoria
 * 3. Lo setea en app('tenant') para acceso global
 * 4. Valida que el tenant esté activo
 * 
 * @package App\Http\Middleware
 */
class TenantResolver
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar a usuarios autenticados
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Super admin no tiene tenant (puede ver todos)
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Validar que el usuario tenga tenant_id
        if (!$user->tenant_id) {
            abort(403, 'Usuario no asignado a ningún tenant.');
        }

        // Cargar el tenant del usuario
        $tenant = \App\Models\Tenant::find($user->tenant_id);

        // Validar que el tenant exista
        if (!$tenant) {
            abort(403, 'Tenant no encontrado.');
        }

        // Validar que el tenant esté activo
        if ($tenant->subscription_status === 'cancelled') {
            abort(403, 'El tenant ha sido cancelado.');
        }

        if ($tenant->subscription_status === 'suspended') {
            abort(403, 'El tenant ha sido suspendido. Contacta con soporte.');
        }

        // Validar que la suscripción no haya expirado
        if ($tenant->subscription_ends_at && $tenant->subscription_ends_at->isPast()) {
            abort(403, 'La suscripción del tenant ha expirado.');
        }

        // Setear el tenant en el contenedor de la aplicación
        // Esto permite acceder al tenant con app('tenant') en cualquier parte
        app()->instance('tenant', $tenant);

        // Setear tenant_id en la sesión (útil para queries)
        session(['tenant_id' => $tenant->id]);

        return $next($request);
    }
}