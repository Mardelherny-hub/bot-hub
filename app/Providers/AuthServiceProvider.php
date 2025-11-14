<?php

namespace App\Providers;

use App\Models\Bot;
use App\Models\Conversation;
use App\Models\KnowledgeBase;
use App\Models\User;
use App\Policies\BotPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\KnowledgeBasePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * AuthServiceProvider
 * 
 * Registra las políticas de autorización y gates personalizados.
 * 
 * POLÍTICAS REGISTRADAS:
 * - BotPolicy: Autorización para operaciones sobre bots
 * - ConversationPolicy: Autorización para conversaciones
 * - KnowledgeBasePolicy: Autorización para knowledge bases
 * 
 * GATES PERSONALIZADOS:
 * - manage-bot: Verificar permiso can_manage en bot específico
 * - chat-in-bot: Verificar permiso can_chat en bot específico
 * - view-bot-analytics: Verificar permiso can_view_analytics
 * - train-bot-kb: Verificar permiso can_train_kb
 * 
 * IMPORTANTE: Super admin bypasea todas las policies automáticamente.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapeo de modelos a políticas
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Bot::class => BotPolicy::class,
        Conversation::class => ConversationPolicy::class,
        KnowledgeBase::class => KnowledgeBasePolicy::class,
    ];

    /**
     * Registrar servicios de autenticación/autorización
     */
    public function boot(): void
    {
        // Gate global: Super admin bypasea todas las policies
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        // Gates personalizados para permisos por bot
        Gate::define('manage-bot', function (User $user, Bot $bot) {
            return $user->canManageBot($bot);
        });

        Gate::define('chat-in-bot', function (User $user, Bot $bot) {
            return $user->canChatInBot($bot);
        });

        Gate::define('view-bot-analytics', function (User $user, Bot $bot) {
            return $user->canViewAnalytics($bot);
        });

        Gate::define('train-bot-kb', function (User $user, Bot $bot) {
            return $user->canTrainKnowledgeBase($bot);
        });

        // Gate para verificar si usuario pertenece al mismo tenant
        Gate::define('same-tenant', function (User $user, $model) {
            if ($user->hasRole('super_admin')) {
                return true;
            }

            return $user->tenant_id === $model->tenant_id;
        });

        // Gate para verificar si usuario tiene acceso a un bot
        Gate::define('access-bot', function (User $user, Bot $bot) {
            if ($user->hasRole('super_admin')) {
                return true;
            }

            if ($user->tenant_id !== $bot->tenant_id) {
                return false;
            }

            if ($user->hasAnyRole(['admin', 'supervisor'])) {
                return true;
            }

            return $user->bots->contains($bot->id);
        });
    }
}