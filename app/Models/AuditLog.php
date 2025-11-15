<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuditLog Model
 * 
 * Representa un log de auditoría del sistema.
 * Registra todas las acciones importantes para compliance y seguridad:
 * creación, modificación, eliminación de registros.
 * 
 * Relaciones:
 * - BelongsTo: Tenant (tenant relacionado, opcional)
 * - BelongsTo: User (usuario que realizó la acción, opcional)
 * 
 * Acciones comunes:
 * - created: Registro creado
 * - updated: Registro actualizado
 * - deleted: Registro eliminado
 * - restored: Registro restaurado (soft delete)
 * - login: Usuario inició sesión
 * - logout: Usuario cerró sesión
 * - failed_login: Intento de login fallido
 * 
 * @property int $id
 * @property int|null $tenant_id
 * @property int|null $user_id
 * @property string $action
 * @property string $entity_type
 * @property int|null $entity_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class AuditLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'audit_logs';

    /**
     * Indicates if the model should be timestamped.
     * Solo created_at, no updated_at (logs son inmutables).
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
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
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
        'user_id' => 'integer',
        'entity_id' => 'integer',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Acciones válidas.
     *
     * @var array<string>
     */
    public const ACTIONS = [
        'created',
        'updated',
        'deleted',
        'restored',
        'login',
        'logout',
        'failed_login',
        'password_reset',
        'email_verified',
        'permission_granted',
        'permission_revoked',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Tenant relacionado con el log.
     * 
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Usuario que realizó la acción.
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
     * Scope: Logs por acción.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Logs por tipo de entidad.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $entityType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope: Logs de una entidad específica.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $entityType
     * @param int $entityId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
                     ->where('entity_id', $entityId);
    }

    /**
     * Scope: Logs en un rango de fechas.
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
     * Scope: Logs de hoy.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Logs de esta semana.
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
     * Scope: Logs de este mes.
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
     * Scope: Logs del sistema (sin usuario).
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope: Logs de usuarios.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query)
    {
        return $query->whereNotNull('user_id');
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
     * Verificar si la acción fue realizada por el sistema.
     * 
     * @return bool
     */
    public function wasSystemAction(): bool
    {
        return is_null($this->user_id);
    }

    /**
     * Verificar si la acción fue realizada por un usuario.
     * 
     * @return bool
     */
    public function wasUserAction(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Obtener descripción legible de la acción.
     * 
     * @return string
     */
    public function getDescription(): string
    {
        $userName = $this->user ? $this->user->name : 'Sistema';
        $action = $this->getActionLabel();
        
        return "{$userName} {$action} {$this->entity_type}";
    }

    /**
     * Obtener label de la acción.
     * 
     * @return string
     */
    private function getActionLabel(): string
    {
        return match ($this->action) {
            'created' => 'creó',
            'updated' => 'actualizó',
            'deleted' => 'eliminó',
            'restored' => 'restauró',
            'login' => 'inició sesión en',
            'logout' => 'cerró sesión en',
            'failed_login' => 'falló al iniciar sesión en',
            default => $this->action,
        };
    }

    /**
     * Obtener cambios realizados (diff entre old y new).
     * 
     * @return array
     */
    public function getChanges(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }
        
        return $changes;
    }

    /**
     * Crear log de creación.
     * 
     * @param string $entityType
     * @param int $entityId
     * @param array $values
     * @param int|null $userId
     * @param int|null $tenantId
     * @return static
     */
    public static function logCreated(
        string $entityType,
        int $entityId,
        array $values,
        ?int $userId = null,
        ?int $tenantId = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => 'created',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'new_values' => $values,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Crear log de actualización.
     * 
     * @param string $entityType
     * @param int $entityId
     * @param array $oldValues
     * @param array $newValues
     * @param int|null $userId
     * @param int|null $tenantId
     * @return static
     */
    public static function logUpdated(
        string $entityType,
        int $entityId,
        array $oldValues,
        array $newValues,
        ?int $userId = null,
        ?int $tenantId = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => 'updated',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Crear log de eliminación.
     * 
     * @param string $entityType
     * @param int $entityId
     * @param array $values
     * @param int|null $userId
     * @param int|null $tenantId
     * @return static
     */
    public static function logDeleted(
        string $entityType,
        int $entityId,
        array $values,
        ?int $userId = null,
        ?int $tenantId = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => 'deleted',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $values,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}