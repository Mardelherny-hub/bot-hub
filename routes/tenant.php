<?php

use App\Http\Controllers\Tenant\BotController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas de Admin de Tenant
 * 
 * ACCESO: Usuarios con rol 'admin' dentro de su tenant
 * 
 * CARACTERÍSTICAS:
 * - Gestión completa de su tenant (bots, usuarios, KB)
 * - Ver analytics de todos los bots de su tenant
 * - Configuración del tenant
 * - Gestión de suscripción y billing
 * 
 * MIDDLEWARE:
 * - auth: Usuario autenticado
 * - tenant.resolver: Valida tenant y lo setea en contexto
 * - role:admin|supervisor: Solo admin o supervisor del tenant
 * 
 * IMPORTANTE:
 * Todas estas rutas están aisladas por tenant automáticamente
 * gracias a TenantScope + TenantResolver.
 */

Route::middleware(['auth', 'tenant.resolver', 'role:admin|supervisor'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        
        // Dashboard del tenant
        Route::get('/dashboard', function () {
            $tenant = app('tenant');
            return view('tenant.dashboard', compact('tenant'));
        })->name('dashboard');
        
        /*
        |--------------------------------------------------------------------------
        | Gestión de Bots
        |--------------------------------------------------------------------------
        */
        Route::resource('bots', BotController::class);

        // Acciones adicionales para bots
        Route::prefix('bots')->name('bots.')->group(function () {
            // Activar bot
            Route::post('{bot}/activate', [BotController::class, 'activate'])
                ->name('activate');

            // Desactivar bot
            Route::post('{bot}/deactivate', [BotController::class, 'deactivate'])
                ->name('deactivate');
        });
        
        // Gestionar usuarios del bot (Livewire) - Sprint futuro
        // Route::get('bots/{bot}/users', function (App\Models\Bot $bot) {
        //     return view('tenant.bots.manage-users', compact('bot'));
        // })->name('bots.manage-users');

        // Gestión de Usuarios del tenant - Sprint futuro
        // Route::resource('users', Tenant\UserController::class);
        
        // Knowledge Base - Sprint 3
        // Route::resource('bots/{bot}/knowledge-base', Tenant\KnowledgeBaseController::class);
        
        // Analytics del tenant - Sprint 4
        // Route::get('analytics', [Tenant\AnalyticsController::class, 'index'])->name('analytics');
        
        // Billing y suscripción - Post-MVP
        // Route::get('billing', [Tenant\BillingController::class, 'index'])->name('billing');
    });