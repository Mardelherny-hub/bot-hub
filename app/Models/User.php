<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 * 
 * Representa un usuario del sistema BotHub.
 * 
 * IMPORTANTE: Este modelo NO usa el trait BelongsToTenant por diseño.
 * 
 * RAZONES:
 * 1. Super admins NO tienen tenant_id (son globales a la plataforma)
 * 2. Aplicar TenantScope automático rompería la funcionalidad de super admin
 * 3. Super admins deben poder ver y gestionar TODOS los tenants
 * 4. El filtrado por tenant se hace manualmente en queries cuando es necesario
 * 
 * ROLES DEL SISTEMA:
 * - super_admin: Acceso total a toda la plataforma (sin tenant)
 * - admin: Gestión completa de su tenant
 * - supervisor: Ver todo de su tenant, sin modificar
 * - agent: Solo bots asignados, puede chatear
 * - viewer: Solo lectura de bots asignados
 * 
 * PERMISOS POR BOT:
 * Los permisos específicos por bot se manejan en la tabla pivot bot_user:
 * - can_manage: Configurar el bot
 * - can_view_analytics: Ver métricas
 * - can_chat: Usar chat en vivo
 * - can_train_kb: Subir documentos y entrenar
 * - can_delete_data: Borrar conversaciones/documentos
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',        // FK a tenants - puede ser NULL para super_admin
        'name',
        'email',
        'password',
        'phone',
        'avatar_url',
        'role',             // DEPRECATED: usar Spatie roles en su lugar
        'is_active',
        'last_login_at',
        'preferences',      // JSON: configuración del usuario
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'preferences' => 'array',
    ];

    /**
     * Get the tenant that owns the user.
     * 
     * NOTA: Puede ser NULL para super_admin
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the bots assigned to this user.
     * 
     * RELACIÓN N:N con permisos granulares por bot.
     * Ver tabla pivot bot_user para permisos específicos.
     */
    public function bots()
    {
        return $this->belongsToMany(Bot::class, 'bot_user')
            ->withPivot([
                'can_manage',           // Puede configurar el bot
                'can_view_analytics',   // Puede ver métricas
                'can_chat',             // Puede usar chat en vivo
                'can_train_kb',         // Puede entrenar knowledge base
                'can_delete_data'       // Puede eliminar datos
            ])
            ->withTimestamps();
    }

    /**
     * Get conversations assigned to this user (handoff).
     * 
     * Conversaciones que fueron transferidas a este agente humano.
     */
    public function assignedConversations()
    {
        return $this->hasMany(Conversation::class, 'assigned_user_id');
    }

    /**
     * Check if user can manage a specific bot.
     * 
     * LÓGICA:
     * 1. Super admin → siempre TRUE
     * 2. Admin del mismo tenant → TRUE
     * 3. Usuario con permiso can_manage en bot_user → TRUE
     * 4. Resto → FALSE
     * 
     * @param Bot $bot
     * @return bool
     */
    public function canManageBot(Bot $bot): bool
    {
        // Super admin puede todo
        if ($this->hasRole('super_admin')) {
            return true;
        }
        
        // Admin del tenant puede todo en su tenant
        if ($this->hasRole('admin') && $this->tenant_id === $bot->tenant_id) {
            return true;
        }
        
        // Revisar permisos específicos del bot en tabla pivot
        $pivot = $this->bots()->where('bot_id', $bot->id)->first()?->pivot;
        return $pivot?->can_manage ?? false;
    }

    /**
     * Check if user can chat in a specific bot.
     * 
     * @param Bot $bot
     * @return bool
     */
    public function canChatInBot(Bot $bot): bool
    {
        if ($this->hasRole('super_admin')) return true;
        if ($this->hasRole('admin') && $this->tenant_id === $bot->tenant_id) return true;
        
        $pivot = $this->bots()->where('bot_id', $bot->id)->first()?->pivot;
        return $pivot?->can_chat ?? false;
    }

    /**
     * Check if user can view analytics of a specific bot.
     * 
     * NOTA: Supervisores también pueden ver analytics de todo su tenant.
     * 
     * @param Bot $bot
     * @return bool
     */
    public function canViewAnalytics(Bot $bot): bool
    {
        if ($this->hasRole('super_admin')) return true;
        
        // Admin y supervisor del tenant pueden ver analytics
        if ($this->hasAnyRole(['admin', 'supervisor']) && $this->tenant_id === $bot->tenant_id) {
            return true;
        }
        
        $pivot = $this->bots()->where('bot_id', $bot->id)->first()?->pivot;
        return $pivot?->can_view_analytics ?? false;
    }

    /**
     * Check if user can train knowledge base of a specific bot.
     * 
     * IMPORTANTE: Solo admin y usuarios con permiso explícito pueden entrenar KB.
     * Esto previene que agentes suban documentos no autorizados.
     * 
     * @param Bot $bot
     * @return bool
     */
    public function canTrainKnowledgeBase(Bot $bot): bool
    {
        if ($this->hasRole('super_admin')) return true;
        if ($this->hasRole('admin') && $this->tenant_id === $bot->tenant_id) return true;
        
        $pivot = $this->bots()->where('bot_id', $bot->id)->first()?->pivot;
        return $pivot?->can_train_kb ?? false;
    }

    /**
     * Check if user is a super admin.
     * 
     * Super admin tiene acceso total a toda la plataforma.
     * 
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Scope to filter users by tenant.
     * 
     * USO: User::forTenant($tenantId)->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $tenantId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to get only active users.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}