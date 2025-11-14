<?php

use Illuminate\Support\Facades\Route;

/**
 * Rutas de Super Admin
 * 
 * ACCESO: Solo usuarios con rol 'super_admin'
 * 
 * CARACTERÍSTICAS:
 * - Sin tenant requerido (super admin es global)
 * - Gestión de todos los tenants de la plataforma
 * - Gestión global de usuarios
 * - Configuración de la plataforma
 * 
 * MIDDLEWARE:
 * - auth: Usuario autenticado
 * - role:super_admin: Solo super admin (Spatie Permission)
 * 
 * NOTA: NO usamos tenant.resolver aquí porque super admin
 * no tiene tenant asignado y necesita ver todos.
 */

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        // Dashboard de super admin
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        
        // Gestión de Tenants (CRUD completo)
        // Route::resource('tenants', Admin\TenantController::class);
        
        // Gestión de Usuarios globales
        // Route::resource('users', Admin\UserController::class);
        
        // Analytics de la plataforma
        // Route::get('analytics', [Admin\AnalyticsController::class, 'index'])->name('analytics');
        
        // Configuración de la plataforma
        // Route::get('settings', [Admin\SettingsController::class, 'index'])->name('settings');
        
        // NOTA: Controladores se crearán en Sprint 1
    });