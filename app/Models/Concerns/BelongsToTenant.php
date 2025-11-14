<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsToTenant Trait
 * 
 * Aplica automáticamente el TenantScope a los modelos y maneja
 * la asignación automática de tenant_id al crear registros.
 * 
 * USO:
 * class Bot extends Model
 * {
 *     use BelongsToTenant;
 * }
 */
trait BelongsToTenant
{
    /**
     * Boot del trait - se ejecuta cuando el modelo es inicializado
     */
    protected static function bootBelongsToTenant(): void
    {
        // Aplicar el TenantScope global
        static::addGlobalScope(new TenantScope);
        
        // Asignar tenant_id automáticamente al crear un registro
        static::creating(function ($model) {
            // Si el modelo ya tiene tenant_id, no hacer nada
            if ($model->tenant_id) {
                return;
            }

            // Si hay usuario autenticado, usar su tenant_id
            if (auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    /**
     * Relación con el modelo Tenant
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope para obtener registros de un tenant específico
     * Útil para super admins que necesitan ver datos de cualquier tenant
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $tenantId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId);
    }

    /**
     * Scope para obtener registros de todos los tenants
     * SOLO para super admins
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAllTenants($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}