<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BotUser Model (Pivot)
 * 
 * Tabla pivot con permisos granulares entre Bot y User.
 * Permite asignar usuarios específicos a bots con permisos individuales.
 * 
 * PERMISOS DISPONIBLES:
 * - can_manage: Configurar bot (nombre, teléfono, personalidad, etc)
 * - can_view_analytics: Ver métricas y reportes del bot
 * - can_chat: Usar chat en vivo (handoff)
 * - can_train_kb: Subir documentos y entrenar knowledge base
 * - can_delete_data: Eliminar conversaciones y documentos
 * 
 * NOTA: Super admin y admin del tenant bypassean estos permisos.
 * Estos permisos solo aplican a usuarios regulares (supervisor, agent, viewer).
 */
class BotUser extends Model
{
    use HasFactory;

    /**
     * Tabla asociada
     */
    protected $table = 'bot_user';

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'bot_id',
        'user_id',
        'can_manage',
        'can_view_analytics',
        'can_chat',
        'can_train_kb',
        'can_delete_data',
        'assigned_at',
    ];

    /**
     * Casts de atributos
     */
    protected $casts = [
        'can_manage' => 'boolean',
        'can_view_analytics' => 'boolean',
        'can_chat' => 'boolean',
        'can_train_kb' => 'boolean',
        'can_delete_data' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    /**
     * Bot relacionado
     */
    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }

    /**
     * Usuario relacionado
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si tiene todos los permisos
     */
    public function hasAllPermissions(): bool
    {
        return $this->can_manage 
            && $this->can_view_analytics 
            && $this->can_chat 
            && $this->can_train_kb 
            && $this->can_delete_data;
    }

    /**
     * Verificar si tiene algún permiso
     */
    public function hasAnyPermission(): bool
    {
        return $this->can_manage 
            || $this->can_view_analytics 
            || $this->can_chat 
            || $this->can_train_kb 
            || $this->can_delete_data;
    }

    /**
     * Otorgar todos los permisos
     */
    public function grantAllPermissions(): void
    {
        $this->update([
            'can_manage' => true,
            'can_view_analytics' => true,
            'can_chat' => true,
            'can_train_kb' => true,
            'can_delete_data' => true,
        ]);
    }

    /**
     * Revocar todos los permisos
     */
    public function revokeAllPermissions(): void
    {
        $this->update([
            'can_manage' => false,
            'can_view_analytics' => false,
            'can_chat' => false,
            'can_train_kb' => false,
            'can_delete_data' => false,
        ]);
    }
}
