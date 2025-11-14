<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

/**
 * ConversationPolicy
 * 
 * Autorización para operaciones sobre Conversations.
 * 
 * REGLAS PRINCIPALES:
 * 1. Super admin: Acceso total
 * 2. Admin/supervisor del tenant: Acceso a todas las conversaciones del tenant
 * 3. Agent asignado: Solo conversaciones asignadas a él
 * 4. Agent con can_chat: Puede ver conversaciones de bots asignados
 * 
 * IMPORTANTE: Las conversaciones están asociadas a un Bot,
 * por lo que siempre se valida el acceso al bot primero.
 */
class ConversationPolicy
{
    /**
     * Ver listado de conversaciones
     * Filtrado por tenant y bots asignados
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'supervisor', 'agent']);
    }

    /**
     * Ver una conversación específica
     * 
     * REGLAS:
     * - Super admin: Ve todas
     * - Admin/supervisor del tenant: Ve todas del tenant
     * - Agent: Solo si está asignado O tiene acceso al bot
     */
    public function view(User $user, Conversation $conversation): bool
    {
        // Super admin ve todo
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Debe ser del mismo tenant
        if ($user->tenant_id !== $conversation->bot->tenant_id) {
            return false;
        }

        // Admin y supervisor del tenant ven todas las conversaciones
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return true;
        }

        // Si la conversación está asignada a este usuario
        if ($conversation->assigned_user_id === $user->id) {
            return true;
        }

        // Si tiene acceso al bot (can_chat o can_view_analytics)
        $pivot = $user->bots()->where('bot_id', $conversation->bot_id)->first()?->pivot;
        return $pivot && ($pivot->can_chat || $pivot->can_view_analytics);
    }

    /**
     * Crear conversación
     * Generalmente las conversaciones se crean automáticamente al recibir mensajes
     * Solo super_admin y admin pueden crear manualmente
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Actualizar conversación (ej: cambiar estado, asignar agente)
     * Requiere can_manage del bot O ser admin/supervisor
     */
    public function update(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->tenant_id !== $conversation->bot->tenant_id) {
            return false;
        }

        // Admin y supervisor pueden actualizar
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return true;
        }

        // Usuario con can_manage en el bot
        return $user->canManageBot($conversation->bot);
    }

    /**
     * Eliminar conversación
     * Requiere can_delete_data del bot
     */
    public function delete(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->tenant_id !== $conversation->bot->tenant_id) {
            return false;
        }

        // Admin puede eliminar
        if ($user->hasRole('admin')) {
            return true;
        }

        // Verificar permiso can_delete_data en el bot
        $pivot = $user->bots()->where('bot_id', $conversation->bot_id)->first()?->pivot;
        return $pivot?->can_delete_data ?? false;
    }

    /**
     * Responder en conversación (chat en vivo)
     * Requiere can_chat del bot O estar asignado
     */
    public function reply(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->tenant_id !== $conversation->bot->tenant_id) {
            return false;
        }

        // Admin puede responder en cualquier conversación
        if ($user->hasRole('admin')) {
            return true;
        }

        // Si está asignado a esta conversación
        if ($conversation->assigned_user_id === $user->id) {
            return true;
        }

        // Si tiene permiso can_chat en el bot
        return $user->canChatInBot($conversation->bot);
    }

    /**
     * Asignar conversación a un agente
     * Solo admin, supervisor, o quien tenga can_manage
     */
    public function assign(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->tenant_id !== $conversation->bot->tenant_id) {
            return false;
        }

        // Admin y supervisor pueden asignar
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return true;
        }

        // Usuario con can_manage en el bot
        return $user->canManageBot($conversation->bot);
    }

    /**
     * Restaurar conversación eliminada
     */
    public function restore(User $user, Conversation $conversation): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('admin') && $user->tenant_id === $conversation->bot->tenant_id;
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(User $user, Conversation $conversation): bool
    {
        return $user->hasRole('super_admin');
    }
}