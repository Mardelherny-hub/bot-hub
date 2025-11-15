<?php

use App\Http\Controllers\Tenant\BotController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas de Tenant
 * 
 * ACCESO: Usuarios autenticados que pertenecen a un tenant
 * 
 * ESTRUCTURA:
 * - Dashboard: admin, supervisor, agent (todos los roles de tenant)
 * - Gestión de bots: solo admin y supervisor
 * 
 * MIDDLEWARE:
 * - auth: Usuario autenticado
 * - tenant.resolver: Valida tenant y lo setea en contexto
 * - role: Varía según la ruta (especificado por grupo)
 */

Route::middleware(['auth', 'tenant.resolver'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        
        /*
        |--------------------------------------------------------------------------
        | Dashboard (Todos los roles del tenant)
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:admin|supervisor|agent')->group(function () {
            Route::get('/dashboard', function () {
                $tenant = app('tenant');
                return view('tenant.dashboard', compact('tenant'));
            })->name('dashboard');
        });
        
        /*
        |--------------------------------------------------------------------------
        | Gestión de Bots (Solo admin y supervisor)
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:admin|supervisor')->group(function () {
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
        });
        
        // Gestionar usuarios del bot (Livewire) - Sprint futuro
        // Route::get('bots/{bot}/users', function (App\Models\Bot $bot) {
        //     return view('tenant.bots.manage-users', compact('bot'));
        // })->name('bots.manage-users');

        // Gestión de Usuarios del tenant - Sprint futuro
        // Route::middleware('role:admin')->group(function () {
        //     Route::resource('users', Tenant\UserController::class);
        // });
        
        // Knowledge Base - Sprint 3
        // Route::resource('bots/{bot}/knowledge-base', Tenant\KnowledgeBaseController::class);
        
        // Analytics del tenant - Sprint 4
        // Route::get('analytics', [Tenant\AnalyticsController::class, 'index'])->name('analytics');
        
        // Billing y suscripción - Post-MVP
        // Route::middleware('role:admin')->group(function () {
        //     Route::get('billing', [Tenant\BillingController::class, 'index'])->name('billing');
        // });
    });