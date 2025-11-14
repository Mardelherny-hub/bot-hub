<?php

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
        
        // Gestión de Bots (CRUD)
        // Route::resource('bots', Tenant\BotController::class);
        
        // Configuración de bots
        // Route::get('bots/{bot}/configure', [Tenant\BotController::class, 'configure'])->name('bots.configure');
        // Gestionar usuarios del bot (Livewire)
        Route::get('bots/{bot}/users', function (App\Models\Bot $bot) {
            return view('tenant.bots.manage-users', compact('bot'));
        })->name('bots.manage-users');

        // Gestión de Usuarios del tenant
        // Route::resource('users', Tenant\UserController::class);
        
        // Asignar usuarios a bots con permisos
        // Route::post('bots/{bot}/assign-user', [Tenant\BotController::class, 'assignUser'])->name('bots.assign-user');
        
        // Knowledge Base
        // Route::resource('bots/{bot}/knowledge-base', Tenant\KnowledgeBaseController::class);
        
        // Analytics del tenant
        // Route::get('analytics', [Tenant\AnalyticsController::class, 'index'])->name('analytics');
        
        // Billing y suscripción
        // Route::get('billing', [Tenant\BillingController::class, 'index'])->name('billing');
        
        // NOTA: Controladores se crearán en Sprint 1
    });