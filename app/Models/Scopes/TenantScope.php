<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Log;

/**
 * TenantScope - Global Scope para aislamiento multi-tenant
 * 
 * Este scope se aplica automáticamente a todos los modelos que usen
 * el trait BelongsToTenant, filtrando todas las queries por tenant_id.
 * 
 * CRÍTICO: Esta es la primera capa de seguridad multi-tenant.
 * NUNCA remover este scope sin autorización explícita.
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Verificar si hay usuario autenticado
        if (!auth()->check()) {
            // Si no hay usuario autenticado, no aplicar filtro
            // Esto permite operaciones de sistema sin usuario
            Log::debug('TenantScope: No authenticated user, scope not applied', [
                'model' => get_class($model)
            ]);
            return;
        }

        $user = auth()->user();

        // Verificar que el usuario tenga tenant_id
        if (!$user->tenant_id) {
            Log::warning('TenantScope: User without tenant_id', [
                'user_id' => $user->id,
                'model' => get_class($model)
            ]);
            return;
        }

        // Aplicar filtro por tenant_id
        $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);

        Log::debug('TenantScope applied', [
            'tenant_id' => $user->tenant_id,
            'model' => get_class($model),
            'table' => $model->getTable()
        ]);
    }

    /**
     * Extend the query builder with methods to bypass the scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        // Método para bypassear el scope cuando sea necesario
        // Uso: Model::withoutTenantScope()->get()
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}