<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Message Model
 * 
 * Representa un mensaje individual dentro de una conversación.
 * 
 * TIPOS DE REMITENTE:
 * - user: Usuario final (cliente)
 * - bot: Respuesta generada por IA
 * - agent: Mensaje de agente humano
 * 
 * DIRECCIÓN:
 * - inbound: Mensaje recibido del usuario
 * - outbound: Mensaje enviado (bot o agente)
 * 
 * IMPORTANTE: Esta tabla NO tiene updated_at, solo created_at.
 * Los mensajes son inmutables una vez creados.
 */
class Message extends Model
{
    use HasFactory;

    /**
     * Deshabilitar updated_at
     */
    const UPDATED_AT = null;

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'conversation_id',
        'direction',
        'sender_type',
        'sender_id',
        'content',
        'content_type',
        'interactive_type',
        'interactive_payload',
        'media_url',
        'media_mime_type',
        'external_message_id',
        'status',
        'error_message',
        'ai_generated',
        'ai_model_used',
        'ai_tokens_used',
        'processing_time_ms',
        'metadata',
    ];

    /**
     * Casts de atributos
     */
    protected $casts = [
        'interactive_payload' => 'array',
        'metadata' => 'array',
        'ai_generated' => 'boolean',
        'ai_tokens_used' => 'integer',
        'processing_time_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Conversación a la que pertenece
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Usuario que envió el mensaje (si es agente)
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope: Mensajes entrantes
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    /**
     * Scope: Mensajes salientes
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    /**
     * Scope: Mensajes del usuario
     */
    public function scopeFromUser($query)
    {
        return $query->where('sender_type', 'user');
    }

    /**
     * Scope: Mensajes del bot
     */
    public function scopeFromBot($query)
    {
        return $query->where('sender_type', 'bot');
    }

    /**
     * Scope: Mensajes de agentes
     */
    public function scopeFromAgent($query)
    {
        return $query->where('sender_type', 'agent');
    }

    /**
     * Scope: Mensajes generados por IA
     */
    public function scopeAiGenerated($query)
    {
        return $query->where('ai_generated', true);
    }

    /**
     * Verificar si es mensaje entrante
     */
    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    /**
     * Verificar si es mensaje saliente
     */
    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    /**
     * Verificar si fue enviado por el usuario
     */
    public function isFromUser(): bool
    {
        return $this->sender_type === 'user';
    }

    /**
     * Verificar si fue generado por el bot
     */
    public function isFromBot(): bool
    {
        return $this->sender_type === 'bot';
    }

    /**
     * Verificar si fue enviado por un agente
     */
    public function isFromAgent(): bool
    {
        return $this->sender_type === 'agent';
    }

    /**
     * Verificar si fue generado por IA
     */
    public function isAiGenerated(): bool
    {
        return $this->ai_generated;
    }

    /**
     * Verificar si tiene media adjunta
     */
    public function hasMedia(): bool
    {
        return !empty($this->media_url);
    }

    /**
     * Verificar si es mensaje de texto
     */
    public function isText(): bool
    {
        return $this->content_type === 'text';
    }

    /**
     * Verificar si es mensaje interactivo
     */
    public function isInteractive(): bool
    {
        return !empty($this->interactive_type);
    }

    /**
     * Marcar como entregado
     */
    public function markAsDelivered(): void
    {
        $this->update(['status' => 'delivered']);
    }

    /**
     * Marcar como leído
     */
    public function markAsRead(): void
    {
        $this->update(['status' => 'read']);
    }

    /**
     * Marcar como fallido
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }
}