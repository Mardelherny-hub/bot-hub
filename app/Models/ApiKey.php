<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * ApiKey Model
 * 
 * Representa una API key para integraciones externas.
 * Permite a los tenants acceder a la API de BotHub desde sus aplicaciones.
 * 
 * Relaciones:
 * - BelongsTo: Tenant (tenant propietario)
 * 
 * Seguridad:
 * - La key se almacena hasheada (SHA-256)
 * - Solo se muestra completa al momento de creación
 * - Se guarda un preview para identificación en UI
 * 
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $key Hash de la API key
 * @property string $key_preview Primeros caracteres
 * @property array|null $permissions
 * @property bool $is_active
 * @property \Carbon\Carbon|null $last_used_at
 * @property int $usage_count
 * @property int|null $rate_limit_per_minute
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class ApiKey extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_keys';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'key',
        'key_preview',
        'permissions',
        'is_active',
        'rate_limit_per_minute',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tenant_id' => 'integer',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'usage_count' => 'integer',
        'rate_limit_per_minute' => 'integer',
        'expires_at' => 'datetime',
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
        'key', // Nunca exponer el hash en JSON
    ];

    /**
     * Prefijo para las API keys generadas.
     *
     * @var string
     */
    public const KEY_PREFIX = 'bh_';

    /**
     * Longitud de la API key (sin prefijo).
     *
     * @var int
     */
    public const KEY_LENGTH = 32;

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Tenant al que pertenece esta API key.
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
     * Scope: API keys activas.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: API keys inactivas.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: API keys no expiradas.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope: API keys expiradas.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }

    /**
     * Scope: API keys válidas (activas y no expiradas).
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->active()->notExpired();
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE NEGOCIO
    |--------------------------------------------------------------------------
    */

    /**
     * Generar una nueva API key.
     * 
     * @return string La API key en texto plano (solo se muestra una vez)
     */
    public static function generate(): string
    {
        return self::KEY_PREFIX . Str::random(self::KEY_LENGTH);
    }

    /**
     * Hashear una API key.
     * 
     * @param string $plainKey
     * @return string
     */
    public static function hash(string $plainKey): string
    {
        return hash('sha256', $plainKey);
    }

    /**
     * Crear preview de la API key (primeros 8 caracteres + ...).
     * 
     * @param string $plainKey
     * @return string
     */
    public static function createPreview(string $plainKey): string
    {
        return substr($plainKey, 0, 8) . '...';
    }

    /**
     * Verificar si una API key coincide con este registro.
     * 
     * @param string $plainKey
     * @return bool
     */
    public function matches(string $plainKey): bool
    {
        return hash_equals($this->key, self::hash($plainKey));
    }

    /**
     * Verificar si la API key está expirada.
     * 
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verificar si la API key es válida.
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Registrar uso de la API key.
     * 
     * @return void
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Verificar si tiene un permiso específico.
     * 
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (is_null($this->permissions)) {
            return true; // Sin restricciones = acceso total
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Verificar si puede acceder a un recurso.
     * 
     * @param string $resource
     * @param string $action
     * @return bool
     */
    public function can(string $resource, string $action): bool
    {
        $permission = "{$resource}.{$action}";
        return $this->hasPermission($permission) || $this->hasPermission("{$resource}.*");
    }

    /**
     * Revocar la API key.
     * 
     * @return bool
     */
    public function revoke(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Activar la API key.
     * 
     * @return bool
     */
    public function activate(): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        return $this->update(['is_active' => true]);
    }
}