<?php

use Illuminate\Support\Facades\Route;

/**
 * Rutas de Agentes y Usuarios del Tenant
 * 
 * ACCESO: Todos los usuarios autenticados de un tenant
 * (admin, supervisor, agent, viewer)
 * 
 * CARACTERÍSTICAS:
 * - Solo ven bots asignados a ellos
 * - Chat en vivo con conversaciones asignadas
 * - Ver analytics de bots permitidos
 * - Gestionar knowledge base (si tienen permiso)
 * 
 * MIDDLEWARE:
 * - auth: Usuario autenticado
 * - tenant.resolver: Valida tenant y lo setea en contexto
 * 
 * PERMISOS:
 * Los permisos específicos se validan en cada controlador/policy
 * usando la tabla pivot bot_user (can_chat, can_manage, etc.)
 */

Route::middleware(['auth', 'tenant.resolver'])
    ->name('agent.')
    ->group(function () {
        
        // Dashboard principal (redirige según rol)
        Route::get('/dashboard', function () {
            $user = auth()->user();
            $tenant = app('tenant');
            
            return view('dashboard', compact('user', 'tenant'));
        })->name('dashboard');
        
        // Mis Bots (solo asignados)
        // Route::get('my-bots', [Agent\BotController::class, 'myBots'])->name('my-bots');
        
        // Chat en vivo
        // Route::get('chat', [Agent\ChatController::class, 'index'])->name('chat.index');
        // Route::get('chat/{conversation}', [Agent\ChatController::class, 'show'])->name('chat.show');
        // Route::post('chat/{conversation}/message', [Agent\ChatController::class, 'sendMessage'])->name('chat.send');
        
        // Conversaciones asignadas a mí
        // Route::get('my-conversations', [Agent\ConversationController::class, 'myConversations'])->name('my-conversations');
        
        // Ver analytics (solo de bots permitidos)
        // Route::get('bots/{bot}/analytics', [Agent\AnalyticsController::class, 'show'])->name('bots.analytics');
        
        // Gestionar Knowledge Base (si tiene permiso can_train_kb)
        // Route::post('bots/{bot}/knowledge-base/upload', [Agent\KnowledgeBaseController::class, 'upload'])->name('kb.upload');
        
        // NOTA: Controladores se crearán en Sprint 4 (Chat) y Sprint 3 (KB)
    });