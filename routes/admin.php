<?php

use App\Http\Controllers\Admin\TenantController;
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
        
          /*
        |--------------------------------------------------------------------------
        | Gestión de Tenants
        |--------------------------------------------------------------------------
        */
        Route::resource('tenants', TenantController::class);

        // Acciones adicionales para tenants
        Route::prefix('tenants')->name('tenants.')->group(function () {
            // Restaurar tenant eliminado (soft delete)
            Route::post('{id}/restore', [TenantController::class, 'restore'])
                ->name('restore')
                ->withTrashed();

            // Suspender suscripción de un tenant
            Route::post('{tenant}/suspend', [TenantController::class, 'suspend'])
                ->name('suspend');

            // Reactivar suscripción de un tenant
            Route::post('{tenant}/activate', [TenantController::class, 'activate'])
                ->name('activate');
        });
        
        // Gestión de Usuarios globales
        // Route::resource('users', Admin\UserController::class);
        
        // Analytics de la plataforma
        // Route::get('analytics', [Admin\AnalyticsController::class, 'index'])->name('analytics');
        
        // Configuración de la plataforma
        // Route::get('settings', [Admin\SettingsController::class, 'index'])->name('settings');
        
        // NOTA: Controladores se crearán en Sprint 1
    });