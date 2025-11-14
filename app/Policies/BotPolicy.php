<?php

namespace App\Policies;

use App\Models\Bot;
use App\Models\User;

/**
 * BotPolicy
 * 
 * Autorización para operaciones sobre Bots.
 * 
 * JERARQUÍA DE AUTORIZACIÓN:
 * 1. super_admin: Puede todo en todos los bots
 * 2. admin: Puede todo en bots de su tenant
 * 3. supervisor: Ve todos los bots de su tenant, no modifica
 * 4. agent/viewer: Solo bots asignados con permisos específicos
 * 
 * IMPORTANTE: Los permisos por bot (pivot bot_user) se evalúan
 * solo cuando no hay permisos de role superior.
 */
class BotPolicy
{
    /**
     * Ver listado de bots
     * Cualquier usuario autenticado puede ver el listado (filtrado por tenant)
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'supervisor', 'agent', 'viewer']);
    }

    /**
     * Ver un bot específico
     * 
     * REGLAS:
     * - super_admin: Puede ver cualquier bot
     * - Mismo tenant: Puede ver si tiene rol o está asignado al bot
     * - Otro tenant: No puede ver
     */
    public function view(User $user, Bot $bot): bool
    {
        // Super admin ve todo
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Debe ser del mismo tenant
        if ($user->tenant_id !== $bot->tenant_id) {
            return false;
        }

        // Admin y supervisor del tenant ven todos los bots
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return true;
        }

        // Agent/viewer: solo si está asignado al bot
        return $user->bots->contains($bot->id);
    }

    /**
     * Crear nuevo bot
     * Solo admin y super_admin
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Actualizar bot (configuración general)
     * Requiere permiso can_manage
     */
    public function update(User $user, Bot $bot): bool
    {
        return $user->canManageBot($bot);
    }

    /**
     * Eliminar bot
     * Requiere permiso can_manage
     */
    public function delete(User $user, Bot $bot): bool
    {
        return $user->canManageBot($bot);
    }

    /**
     * Usar chat en vivo (handoff)
     * Requiere permiso can_chat
     */
    public function chat(User $user, Bot $bot): bool
    {
        return $user->canChatInBot($bot);
    }

    /**
     * Ver analytics del bot
     * Requiere permiso can_view_analytics
     */
    public function viewAnalytics(User $user, Bot $bot): bool
    {
        return $user->canViewAnalytics($bot);
    }

    /**
     * Entrenar knowledge base
     * Requiere permiso can_train_kb
     */
    public function trainKnowledgeBase(User $user, Bot $bot): bool
    {
        return $user->canTrainKnowledgeBase($bot);
    }

    /**
     * Eliminar datos (conversaciones, documentos)
     * Requiere permiso can_delete_data
     */
    public function deleteData(User $user, Bot $bot): bool
    {
        // Super admin puede todo
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin del tenant puede borrar datos
        if ($user->hasRole('admin') && $user->tenant_id === $bot->tenant_id) {
            return true;
        }

        // Verificar permiso específico en pivot
        $pivot = $user->bots()->where('bot_id', $bot->id)->first()?->pivot;
        return $pivot?->can_delete_data ?? false;
    }

    /**
     * Gestionar usuarios del bot (asignar/remover)
     * Solo admin del tenant y super_admin
     */
    public function manageUsers(User $user, Bot $bot): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('admin') && $user->tenant_id === $bot->tenant_id;
    }

    /**
     * Restaurar bot eliminado (soft delete)
     * Solo super_admin y admin
     */
    public function restore(User $user, Bot $bot): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('admin') && $user->tenant_id === $bot->tenant_id;
    }

    /**
     * Eliminar permanentemente
     * Solo super_admin
     */
    public function forceDelete(User $user, Bot $bot): bool
    {
        return $user->hasRole('super_admin');
    }
}