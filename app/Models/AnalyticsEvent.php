<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AnalyticsEvent Model
 * 
 * Representa un evento de analytics del sistema.
 * Registra todas las métricas importantes: mensajes, conversaciones,
 * uso de IA, tiempos de respuesta, costos, etc.
 * 
 * Relaciones:
 * - BelongsTo: Tenant (tenant propietario)
 * - BelongsTo: Bot (bot relacionado, opcional)
 * - BelongsTo: Conversation (conversación relacionada, opcional)
 * 
 * Categorías de eventos:
 * - message: Eventos de mensajería
 * - conversation: Eventos de conversaciones
 * - ai: Eventos de IA (GPT, embeddings)
 * - whatsapp: Eventos de WhatsApp API
 * - webhook: Eventos de webhooks
 * - system: Eventos del sistema
 * 
 * @property int $id
 * @property int $tenant_id
 * @property int|null $bot_id
 * @property int|null $conversation_id
 * @property string $event_type
 * @property string $event_category
 * @property array|null $event_data
 * @property int|null $response_time_ms
 * @property int|null $tokens_used
 * @property float|null $cost_usd
 * @property bool $success
 * @property string|null $error_message
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class AnalyticsEvent extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'analytics_events';

    /**
     * Indicates if the model should be timestamped.
     * Solo created_at, no updated_at (eventos son inmutables).
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
        'tenant_id',
        'bot_id',
        'conversation_id',
        'event_type',
        'event_category',
        'event_data',
        'response_time_ms',
        'tokens_used',
        'cost_usd',
        'success',
        'error_message',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tenant_id' => 'integer',
        'bot_id' => 'integer',
        'conversation_id' => 'integer',
        'event_data' => 'array',
        'response_time_ms' => 'integer',
        'tokens_used' => 'integer',
        'cost_usd' => 'decimal:6',
        'success' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Categorías válidas de eventos.
     *
     * @var array<string>
     */
    public const CATEGORIES = [
        'message',
        'conversation',
        'ai',
        'whatsapp',
        'webhook',
        'system',
    ];

    /**
     * Tipos de eventos válidos.
     *
     * @var array<string>
     */
    public const EVENT_TYPES = [
        // Message events
        'message.sent',
        'message.received',
        'message.failed',
        
        // Conversation events
        'conversation.started',
        'conversation.closed',
        'conversation.assigned',
        'conversation.transferred',
        
        // AI events
        'ai.completion.request',
        'ai.completion.success',
        'ai.completion.failed',
        'ai.embedding.generated',
        
        // WhatsApp events
        'whatsapp.message.sent',
        'whatsapp.message.delivered',
        'whatsapp.message.read',
        'whatsapp.message.failed',
        
        // Webhook events
        'webhook.triggered',
        'webhook.success',
        'webhook.failed',
        
        // System events
        'system.error',
        'system.warning',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Tenant al que pertenece este evento.
     * 
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Bot relacionado con el evento.
     * 
     * @return BelongsTo
     */
    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class, 'bot_id');
    }

    /**
     * Conversación relacionada con el evento.
     * 
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Eventos exitosos.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope: Eventos fallidos.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope: Eventos por categoría.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('event_category', $category);
    }

    /**
     * Scope: Eventos por tipo.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope: Eventos en un rango de fechas.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     * @param string $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope: Eventos de hoy.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Eventos de esta semana.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope: Eventos de este mes.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Scope: Eventos con costo.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCost($query)
    {
        return $query->whereNotNull('cost_usd')->where('cost_usd', '>', 0);
    }

    /**
     * Scope: Eventos lentos (tiempo de respuesta alto).
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $threshold Umbral en milisegundos
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSlow($query, int $threshold = 3000)
    {
        return $query->where('response_time_ms', '>', $threshold);
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE NEGOCIO
    |--------------------------------------------------------------------------
    */

    /**
     * Verificar si el evento fue exitoso.
     * 
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->success === true;
    }

    /**
     * Verificar si el evento falló.
     * 
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->success === false;
    }

    /**
     * Verificar si el evento tiene costo asociado.
     * 
     * @return bool
     */
    public function hasCost(): bool
    {
        return !is_null($this->cost_usd) && $this->cost_usd > 0;
    }

    /**
     * Verificar si el evento fue lento.
     * 
     * @param int $threshold Umbral en milisegundos
     * @return bool
     */
    public function wasSlow(int $threshold = 3000): bool
    {
        return !is_null($this->response_time_ms) && $this->response_time_ms > $threshold;
    }

    /**
     * Obtener el tiempo de respuesta en segundos.
     * 
     * @return float|null
     */
    public function getResponseTimeSeconds(): ?float
    {
        return $this->response_time_ms ? $this->response_time_ms / 1000 : null;
    }

    /**
     * Crear evento de mensaje enviado.
     * 
     * @param int $tenantId
     * @param int $botId
     * @param int $conversationId
     * @param array $data
     * @return static
     */
    public static function logMessageSent(
        int $tenantId,
        int $botId,
        int $conversationId,
        array $data = []
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'bot_id' => $botId,
            'conversation_id' => $conversationId,
            'event_type' => 'message.sent',
            'event_category' => 'message',
            'event_data' => $data,
            'success' => true,
        ]);
    }

    /**
     * Crear evento de completado de IA.
     * 
     * @param int $tenantId
     * @param int $botId
     * @param int $tokensUsed
     * @param float $costUsd
     * @param int $responseTimeMs
     * @param array $data
     * @return static
     */
    public static function logAiCompletion(
        int $tenantId,
        int $botId,
        int $tokensUsed,
        float $costUsd,
        int $responseTimeMs,
        array $data = []
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'bot_id' => $botId,
            'event_type' => 'ai.completion.success',
            'event_category' => 'ai',
            'tokens_used' => $tokensUsed,
            'cost_usd' => $costUsd,
            'response_time_ms' => $responseTimeMs,
            'event_data' => $data,
            'success' => true,
        ]);
    }
}