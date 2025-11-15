<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TenantUsageReport Model
 * 
 * Representa un reporte mensual de uso por tenant para billing y analytics.
 * Consolida todas las métricas de consumo del período.
 * 
 * Relaciones:
 * - BelongsTo: Tenant (tenant al que pertenece el reporte)
 * 
 * Estados de billing:
 * - pending: Reporte pendiente de cálculo
 * - calculated: Reporte calculado pero no facturado
 * - billed: Reporte facturado
 * - paid: Factura pagada
 * - overdue: Factura vencida
 * 
 * @property int $id
 * @property int $tenant_id
 * @property string $period Formato: YYYY-MM
 * @property int $conversations_used
 * @property int $messages_sent
 * @property int $messages_received
 * @property int $tokens_used
 * @property int $bots_active
 * @property int $users_active
 * @property float $storage_mb_used
 * @property float $whatsapp_cost_usd
 * @property float $openai_cost_usd
 * @property float $total_cost_usd
 * @property string $billing_status
 * @property \Carbon\Carbon|null $billed_at
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class TenantUsageReport extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tenant_usage_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'period',
        'conversations_used',
        'messages_sent',
        'messages_received',
        'tokens_used',
        'bots_active',
        'users_active',
        'storage_mb_used',
        'whatsapp_cost_usd',
        'openai_cost_usd',
        'total_cost_usd',
        'billing_status',
        'billed_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tenant_id' => 'integer',
        'conversations_used' => 'integer',
        'messages_sent' => 'integer',
        'messages_received' => 'integer',
        'tokens_used' => 'integer',
        'bots_active' => 'integer',
        'users_active' => 'integer',
        'storage_mb_used' => 'decimal:2',
        'whatsapp_cost_usd' => 'decimal:4',
        'openai_cost_usd' => 'decimal:4',
        'total_cost_usd' => 'decimal:4',
        'billed_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Estados válidos de billing.
     *
     * @var array<string>
     */
    public const BILLING_STATUSES = [
        'pending',
        'calculated',
        'billed',
        'paid',
        'overdue',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Tenant al que pertenece este reporte.
     * 
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Reportes por período.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $period Formato: YYYY-MM
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope: Reportes por estado de billing.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByBillingStatus($query, string $status)
    {
        return $query->where('billing_status', $status);
    }

    /**
     * Scope: Reportes pendientes.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('billing_status', 'pending');
    }

    /**
     * Scope: Reportes calculados pero no facturados.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCalculated($query)
    {
        return $query->where('billing_status', 'calculated');
    }

    /**
     * Scope: Reportes facturados.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBilled($query)
    {
        return $query->whereIn('billing_status', ['billed', 'paid', 'overdue']);
    }

    /**
     * Scope: Reportes pagados.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('billing_status', 'paid');
    }

    /**
     * Scope: Reportes vencidos.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->where('billing_status', 'overdue');
    }

    /**
     * Scope: Reportes del año actual.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrentYear($query)
    {
        $year = now()->year;
        return $query->where('period', 'LIKE', "{$year}-%");
    }

    /**
     * Scope: Reportes de un año específico.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('period', 'LIKE', "{$year}-%");
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE NEGOCIO
    |--------------------------------------------------------------------------
    */

    /**
     * Calcular el total de mensajes (enviados + recibidos).
     * 
     * @return int
     */
    public function getTotalMessages(): int
    {
        return $this->messages_sent + $this->messages_received;
    }

    /**
     * Calcular storage en GB.
     * 
     * @return float
     */
    public function getStorageGb(): float
    {
        return round($this->storage_mb_used / 1024, 2);
    }

    /**
     * Verificar si el reporte fue facturado.
     * 
     * @return bool
     */
    public function isBilled(): bool
    {
        return in_array($this->billing_status, ['billed', 'paid', 'overdue']);
    }

    /**
     * Verificar si el reporte fue pagado.
     * 
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->billing_status === 'paid';
    }

    /**
     * Verificar si el reporte está vencido.
     * 
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->billing_status === 'overdue';
    }

    /**
     * Marcar como calculado.
     * 
     * @return bool
     */
    public function markAsCalculated(): bool
    {
        return $this->update(['billing_status' => 'calculated']);
    }

    /**
     * Marcar como facturado.
     * 
     * @return bool
     */
    public function markAsBilled(): bool
    {
        return $this->update([
            'billing_status' => 'billed',
            'billed_at' => now(),
        ]);
    }

    /**
     * Marcar como pagado.
     * 
     * @return bool
     */
    public function markAsPaid(): bool
    {
        return $this->update(['billing_status' => 'paid']);
    }

    /**
     * Marcar como vencido.
     * 
     * @return bool
     */
    public function markAsOverdue(): bool
    {
        return $this->update(['billing_status' => 'overdue']);
    }

    /**
     * Calcular el costo total (suma de todos los costos).
     * 
     * @return float
     */
    public function calculateTotalCost(): float
    {
        return $this->whatsapp_cost_usd + $this->openai_cost_usd;
    }

    /**
     * Actualizar el costo total.
     * 
     * @return bool
     */
    public function updateTotalCost(): bool
    {
        $total = $this->calculateTotalCost();
        return $this->update(['total_cost_usd' => $total]);
    }

    /**
     * Obtener el año del período.
     * 
     * @return int
     */
    public function getYear(): int
    {
        return (int) substr($this->period, 0, 4);
    }

    /**
     * Obtener el mes del período.
     * 
     * @return int
     */
    public function getMonth(): int
    {
        return (int) substr($this->period, 5, 2);
    }

    /**
     * Obtener nombre del mes.
     * 
     * @return string
     */
    public function getMonthName(): string
    {
        $months = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
            '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
            '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
            '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
        ];
        
        $month = substr($this->period, 5, 2);
        return $months[$month] ?? 'Unknown';
    }

    /**
     * Crear o actualizar reporte para un período.
     * 
     * @param int $tenantId
     * @param string $period Formato: YYYY-MM
     * @param array $data
     * @return static
     */
    public static function createOrUpdate(int $tenantId, string $period, array $data): self
    {
        return self::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'period' => $period,
            ],
            $data
        );
    }

    /**
     * Obtener reporte del mes actual.
     * 
     * @param int $tenantId
     * @return static|null
     */
    public static function getCurrentMonth(int $tenantId): ?self
    {
        $period = now()->format('Y-m');
        return self::where('tenant_id', $tenantId)
                   ->where('period', $period)
                   ->first();
    }

    /**
     * Obtener reporte del mes anterior.
     * 
     * @param int $tenantId
     * @return static|null
     */
    public static function getLastMonth(int $tenantId): ?self
    {
        $period = now()->subMonth()->format('Y-m');
        return self::where('tenant_id', $tenantId)
                   ->where('period', $period)
                   ->first();
    }
}