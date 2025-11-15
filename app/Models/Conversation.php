<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Conversation Model
 * 
 * Representa un hilo de conversación entre un usuario final y un bot.
 * Puede ser transferida a un agente humano (handoff).
 * 
 * RELACIONES:
 * - belongsTo: Bot
 * - belongsTo: User (assigned_user_id) - agente humano asignado
 * - hasMany: Message
 * - hasMany: AnalyticsEvent
 * 
 * ESTADOS:
 * - active: Conversación activa con bot
 * - waiting_human: Esperando asignación a humano
 * - with_human: Conversación siendo atendida por humano
 * - resolved: Resuelta, esperando cierre
 * - closed: Cerrada
 */
class Conversation extends Model
{
    use HasFactory;

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'bot_id',
        'external_user_id',
        'external_user_name',
        'channel',
        'status',
        'assigned_user_id',
        'handoff_reason',
        'handoff_at',
        'last_message_at',
        'message_count',
        'first_response_time_ms',
        'sentiment_score',
        'satisfaction_rating',
        'tags',
        'metadata',
        'closed_at',
    ];

    /**
     * Casts de atributos
     */
    protected $casts = [
        'handoff_at' => 'datetime',
        'last_message_at' => 'datetime',
        'closed_at' => 'datetime',
        'tags' => 'array',
        'metadata' => 'array',
        'message_count' => 'integer',
        'first_response_time_ms' => 'integer',
        'sentiment_score' => 'float',
        'satisfaction_rating' => 'integer',
    ];

    /**
     * Bot que maneja la conversación
     */
    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }

    /**
     * Usuario (agente) asignado a la conversación
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Mensajes de la conversación
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Eventos de analytics de la conversación
     */
    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    /**
     * Scope: Conversaciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Conversaciones esperando humano
     */
    public function scopeWaitingHuman($query)
    {
        return $query->where('status', 'waiting_human');
    }

    /**
     * Scope: Conversaciones con humano
     */
    public function scopeWithHuman($query)
    {
        return $query->where('status', 'with_human');
    }

    /**
     * Scope: Conversaciones cerradas
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope: Conversaciones asignadas a un usuario
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    /**
     * Scope: Conversaciones por canal
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Verificar si la conversación está activa
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verificar si la conversación está cerrada
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Verificar si la conversación tiene un agente asignado
     */
    public function hasAssignedAgent(): bool
    {
        return $this->assigned_user_id !== null;
    }

    /**
     * Asignar conversación a un agente humano (handoff)
     */
    public function assignToAgent(int $userId, string $reason = null): void
    {
        $this->update([
            'assigned_user_id' => $userId,
            'handoff_reason' => $reason,
            'handoff_at' => now(),
            'status' => 'with_human',
        ]);
    }

    /**
     * Cerrar la conversación
     */
    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    /**
     * Reabrir la conversación
     */
    public function reopen(): void
    {
        $this->update([
            'status' => 'active',
            'closed_at' => null,
        ]);
    }

    /**
     * Actualizar contador de mensajes
     */
    public function incrementMessageCount(): void
    {
        $this->increment('message_count');
        $this->update(['last_message_at' => now()]);
    }

    /**
     * Agregar tag a la conversación
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    /**
     * Remover tag de la conversación
     */
    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $tags = array_filter($tags, fn($t) => $t !== $tag);
        $this->update(['tags' => array_values($tags)]);
    }

    /**
     * Verificar si tiene un tag específico
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }
}