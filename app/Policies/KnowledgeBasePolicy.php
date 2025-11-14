<?php

namespace App\Policies;

use App\Models\KnowledgeBase;
use App\Models\User;

/**
 * KnowledgeBasePolicy
 * 
 * Autorización para operaciones sobre Knowledge Bases.
 * 
 * REGLAS PRINCIPALES:
 * 1. Super admin: Acceso total
 * 2. Admin del tenant: Puede gestionar todas las KB del tenant
 * 3. Usuario con can_train_kb: Puede subir documentos y entrenar
 * 4. Usuario con can_manage: Puede configurar pero no entrenar
 * 5. Usuario con can_view_analytics: Solo lectura
 * 
 * IMPORTANTE: KnowledgeBase está asociada 1:1 con Bot.
 * Entrenar la KB es una operación sensible (puede afectar respuestas).
 */
class KnowledgeBasePolicy
{
    /**
     * Ver listado de knowledge bases
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'supervisor', 'agent', 'viewer']);
    }

    /**
     * Ver una knowledge base específica
     * 
     * REGLAS:
     * - Super admin: Ve todas
     * - Admin/supervisor del tenant: Ve todas del tenant
     * - Usuario con acceso al bot: Puede ver
     */
    public function view(User $user, KnowledgeBase $knowledgeBase): bool
    {
        // Super admin ve todo
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $bot = $knowledgeBase->bot;

        // Debe ser del mismo tenant
        if ($user->tenant_id !== $bot->tenant_id) {
            return false;
        }

        // Admin y supervisor del tenant ven todas las KB
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return true;
        }

        // Si tiene acceso al bot, puede ver la KB
        return $user->bots->contains($bot->id);
    }

    /**
     * Crear knowledge base
     * Se crea automáticamente con el bot, pero si es manual solo admin
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Actualizar configuración de KB (no documentos)
     * Requiere can_manage del bot
     */
    public function update(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $bot = $knowledgeBase->bot;

        if ($user->tenant_id !== $bot->tenant_id) {
            return false;
        }

        // Admin puede actualizar
        if ($user->hasRole('admin')) {
            return true;
        }

        // Usuario con can_manage en el bot
        return $user->canManageBot($bot);
    }

    /**
     * Eliminar knowledge base
     * Solo super_admin y admin
     */
    public function delete(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $bot = $knowledgeBase->bot;

        return $user->hasRole('admin') && $user->tenant_id === $bot->tenant_id;
    }

    /**
     * Subir documentos y entrenar KB
     * Requiere permiso can_train_kb
     * 
     * CRÍTICO: Este permiso es sensible porque afecta las respuestas del bot.
     * Solo admin y usuarios explícitamente autorizados.
     */
    public function train(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $bot = $knowledgeBase->bot;

        if ($user->tenant_id !== $bot->tenant_id) {
            return false;
        }

        // Admin puede entrenar
        if ($user->hasRole('admin')) {
            return true;
        }

        // Usuario con permiso can_train_kb en el bot
        return $user->canTrainKnowledgeBase($bot);
    }

    /**
     * Ver documentos de la KB
     * Cualquiera con acceso al bot puede ver los documentos
     */
    public function viewDocuments(User $user, KnowledgeBase $knowledgeBase): bool
    {
        return $this->view($user, $knowledgeBase);
    }

    /**
     * Eliminar documentos
     * Requiere can_delete_data O can_train_kb
     */
    public function deleteDocuments(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $bot = $knowledgeBase->bot;

        if ($user->tenant_id !== $bot->tenant_id) {
            return false;
        }

        // Admin puede eliminar
        if ($user->hasRole('admin')) {
            return true;
        }

        // Usuario con can_delete_data O can_train_kb
        $pivot = $user->bots()->where('bot_id', $bot->id)->first()?->pivot;
        return $pivot && ($pivot->can_delete_data || $pivot->can_train_kb);
    }

    /**
     * Descargar documentos
     * Cualquiera con acceso a la KB puede descargar
     */
    public function downloadDocuments(User $user, KnowledgeBase $knowledgeBase): bool
    {
        return $this->view($user, $knowledgeBase);
    }

    /**
     * Ver métricas de la KB (precisión, documentos procesados, etc)
     * Requiere can_view_analytics
     */
    public function viewMetrics(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $bot = $knowledgeBase->bot;

        if ($user->tenant_id !== $bot->tenant_id) {
            return false;
        }

        // Admin y supervisor pueden ver métricas
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return true;
        }

        // Usuario con can_view_analytics en el bot
        return $user->canViewAnalytics($bot);
    }

    /**
     * Restaurar KB eliminada
     */
    public function restore(User $user, KnowledgeBase $knowledgeBase): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $bot = $knowledgeBase->bot;

        return $user->hasRole('admin') && $user->tenant_id === $bot->tenant_id;
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(User $user, KnowledgeBase $knowledgeBase): bool
    {
        return $user->hasRole('super_admin');
    }
}