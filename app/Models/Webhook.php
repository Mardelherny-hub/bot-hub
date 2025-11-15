<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Webhook Model
 * 
 * Representa un webhook configurado para un bot.
 * Permite notificar a sistemas externos cuando ocurren eventos específicos.
 * 
 * Relaciones:
 * - BelongsTo: Bot (bot propietario)
 * - BelongsTo: Tenant (a través de bot)
 * 
 * Eventos soportados:
 * - message.received
 * - message.sent
 * - conversation.started
 * - conversation.closed
 * - conversation.assigned
 * 
 * @property int $id
 * @property int $bot_id
 * @property string $name
 * @property string $url
 * @property string $method
 * @property array|null $headers
 * @property array $events
 * @property bool $is_active
 * @property int $max_retries
 * @property int $timeout_seconds
 * @property int $success_count
 * @property int $failure_count
 * @property \Carbon\Carbon|null $last_triggered_at
 * @property \Carbon\Carbon|null $last_success_at
 * @property \Carbon\Carbon|null $last_failure_at
 * @property string|null $last_error
 * @property string|null $secret
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class Webhook extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'webhooks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bot_id',
        'name',
        'url',
        'method',
        'headers',
        'events',
        'is_active',
        'max_retries',
        'timeout_seconds',
        'secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bot_id' => 'integer',
        'headers' => 'array',
        'events' => 'array',
        'is_active' => 'boolean',
        'max_retries' => 'integer',
        'timeout_seconds' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
        'last_triggered_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret',
    ];

    /**
     * Eventos válidos que pueden disparar webhooks.
     *
     * @var array<string>
     */
    public const VALID_EVENTS = [
        'message.received',
        'message.sent',
        'conversation.started',
        'conversation.closed',
        'conversation.assigned',
        'conversation.transferred',
    ];

    /**
     * Métodos HTTP válidos.
     *
     * @var array<string>
     */
    public const VALID_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Bot al que pertenece este webhook.
     * 
     * @return BelongsTo
     */
    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class, 'bot_id');
    }

    /**
     * Obtener el tenant a través del bot.
     * Requerido por el trait BelongsToTenant.
     * 
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->bot()->getRelated()->tenant();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Webhooks activos.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Webhooks inactivos.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Webhooks que escuchan un evento específico.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $event
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEvent($query, string $event)
    {
        return $query->whereJsonContains('events', $event);
    }

    /**
     * Scope: Webhooks con alta tasa de fallos.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $threshold Porcentaje mínimo de fallos (0-1)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithHighFailureRate($query, float $threshold = 0.5)
    {
        return $query->whereRaw(
            '(failure_count / (success_count + failure_count + 1)) >= ?',
            [$threshold]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE NEGOCIO
    |--------------------------------------------------------------------------
    */

    /**
     * Verificar si el webhook escucha un evento específico.
     * 
     * @param string $event
     * @return bool
     */
    public function listensToEvent(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }

    /**
     * Registrar un envío exitoso.
     * 
     * @return void
     */
    public function recordSuccess(): void
    {
        $this->increment('success_count');
        $this->update([
            'last_triggered_at' => now(),
            'last_success_at' => now(),
            'last_error' => null,
        ]);
    }

    /**
     * Registrar un envío fallido.
     * 
     * @param string $error
     * @return void
     */
    public function recordFailure(string $error): void
    {
        $this->increment('failure_count');
        $this->update([
            'last_triggered_at' => now(),
            'last_failure_at' => now(),
            'last_error' => $error,
        ]);
    }

    /**
     * Calcular tasa de éxito.
     * 
     * @return float Porcentaje de 0 a 1
     */
    public function getSuccessRate(): float
    {
        $total = $this->success_count + $this->failure_count;
        
        if ($total === 0) {
            return 0.0;
        }
        
        return $this->success_count / $total;
    }

    /**
     * Calcular tasa de fallos.
     * 
     * @return float Porcentaje de 0 a 1
     */
    public function getFailureRate(): float
    {
        return 1.0 - $this->getSuccessRate();
    }

    /**
     * Verificar si el webhook está saludable.
     * 
     * @param float $maxFailureRate Tasa máxima de fallos aceptable (0-1)
     * @return bool
     */
    public function isHealthy(float $maxFailureRate = 0.2): bool
    {
        return $this->getFailureRate() <= $maxFailureRate;
    }

    /**
     * Generar firma HMAC del payload.
     * 
     * @param array $payload
     * @return string|null
     */
    public function generateSignature(array $payload): ?string
    {
        if (!$this->secret) {
            return null;
        }
        
        return hash_hmac('sha256', json_encode($payload), $this->secret);
    }
}