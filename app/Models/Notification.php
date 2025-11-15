<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notification Model
 * 
 * Representa una notificación del sistema para un usuario.
 * Notifica eventos importantes como nuevos mensajes, asignaciones,
 * límites de uso, errores, etc.
 * 
 * Relaciones:
 * - BelongsTo: User (usuario destinatario)
 * 
 * Tipos de notificaciones:
 * - message.new: Nuevo mensaje recibido
 * - conversation.assigned: Conversación asignada
 * - conversation.transferred: Conversación transferida
 * - bot.limit.reached: Límite de bot alcanzado
 * - system.error: Error del sistema
 * - system.warning: Advertencia del sistema
 * 
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property string|null $action_url
 * @property bool $is_read
 * @property \Carbon\Carbon|null $read_at
 * @property string $priority
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Indicates if the model should be timestamped.
     * Solo created_at, no updated_at (notificaciones son inmutables).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'is_read',
        'read_at',
        'priority',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Tipos válidos de notificaciones.
     *
     * @var array<string>
     */
    public const TYPES = [
        'message.new',
        'message.failed',
        'conversation.assigned',
        'conversation.transferred',
        'conversation.closed',
        'bot.limit.reached',
        'bot.limit.warning',
        'system.error',
        'system.warning',
        'system.info',
    ];

    /**
     * Prioridades válidas.
     *
     * @var array<string>
     */
    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Usuario destinatario de la notificación.
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Notificaciones no leídas.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Notificaciones leídas.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope: Notificaciones por tipo.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Notificaciones por prioridad.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $priority
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Notificaciones urgentes.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope: Notificaciones de alta prioridad.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Scope: Notificaciones recientes (últimas 24 horas).
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    /**
     * Scope: Ordenar por más recientes primero.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE NEGOCIO
    |--------------------------------------------------------------------------
    */

    /**
     * Marcar la notificación como leída.
     * 
     * @return bool
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return false; // Ya estaba leída
        }

        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Marcar la notificación como no leída.
     * 
     * @return bool
     */
    public function markAsUnread(): bool
    {
        if (!$this->is_read) {
            return false; // Ya estaba sin leer
        }

        return $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Verificar si la notificación fue leída.
     * 
     * @return bool
     */
    public function wasRead(): bool
    {
        return $this->is_read === true;
    }

    /**
     * Verificar si la notificación es urgente.
     * 
     * @return bool
     */
    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

    /**
     * Verificar si la notificación es de alta prioridad.
     * 
     * @return bool
     */
    public function isHighPriority(): bool
    {
        return in_array($this->priority, ['high', 'urgent']);
    }

    /**
     * Verificar si la notificación tiene una acción asociada.
     * 
     * @return bool
     */
    public function hasAction(): bool
    {
        return !is_null($this->action_url);
    }

    /**
     * Obtener tiempo transcurrido desde la creación.
     * 
     * @return string
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Crear notificación de nuevo mensaje.
     * 
     * @param int $userId
     * @param string $conversationId
     * @param string $senderName
     * @return static
     */
    public static function newMessage(
        int $userId,
        string $conversationId,
        string $senderName
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => 'message.new',
            'title' => 'Nuevo mensaje',
            'message' => "Nuevo mensaje de {$senderName}",
            'action_url' => "/conversations/{$conversationId}",
            'priority' => 'normal',
            'metadata' => [
                'conversation_id' => $conversationId,
                'sender_name' => $senderName,
            ],
        ]);
    }

    /**
     * Crear notificación de conversación asignada.
     * 
     * @param int $userId
     * @param int $conversationId
     * @param string $botName
     * @return static
     */
    public static function conversationAssigned(
        int $userId,
        int $conversationId,
        string $botName
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => 'conversation.assigned',
            'title' => 'Nueva conversación asignada',
            'message' => "Se te asignó una conversación en {$botName}",
            'action_url' => "/conversations/{$conversationId}",
            'priority' => 'high',
            'metadata' => [
                'conversation_id' => $conversationId,
                'bot_name' => $botName,
            ],
        ]);
    }

    /**
     * Crear notificación de límite alcanzado.
     * 
     * @param int $userId
     * @param string $limitType
     * @param int $currentValue
     * @param int $maxValue
     * @return static
     */
    public static function limitReached(
        int $userId,
        string $limitType,
        int $currentValue,
        int $maxValue
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => 'bot.limit.reached',
            'title' => 'Límite alcanzado',
            'message' => "Has alcanzado el límite de {$limitType}: {$currentValue}/{$maxValue}",
            'action_url' => "/settings/billing",
            'priority' => 'urgent',
            'metadata' => [
                'limit_type' => $limitType,
                'current_value' => $currentValue,
                'max_value' => $maxValue,
            ],
        ]);
    }
}