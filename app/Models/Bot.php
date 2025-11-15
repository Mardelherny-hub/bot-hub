<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Bot Model
 * 
 * Representa un bot de WhatsApp con IA.
 * Cada bot pertenece a un tenant y puede tener múltiples usuarios asignados.
 * 
 * RELACIONES:
 * - belongsTo: Tenant
 * - belongsToMany: User (pivot: bot_user con permisos)
 * - hasMany: Conversation
 * - hasOne: KnowledgeBase
 * - hasMany: Webhook
 * - hasMany: AnalyticsEvent
 */
class Bot extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'phone_number',
        'whatsapp_business_account_id',
        'whatsapp_phone_number_id',
        'ai_model',
        'personality',
        'instructions',
        'max_tokens',
        'temperature',
        'language',
        'is_active',
        'fallback_to_human',
        'inactivity_timeout_minutes',
        'business_hours_start',
        'business_hours_end',
        'business_days',
        'out_of_hours_message',
        'use_knowledge_base',
        'knowledge_base_results',
        'knowledge_base_threshold',
        'metadata',
    ];

    /**
     * Casts de atributos
     */
    protected $casts = [
        'is_active' => 'boolean',
        'fallback_to_human' => 'boolean',
        'use_knowledge_base' => 'boolean',
        'business_days' => 'array',
        'metadata' => 'array',
        'temperature' => 'float',
        'knowledge_base_threshold' => 'float',
        'max_tokens' => 'integer',
        'inactivity_timeout_minutes' => 'integer',
        'knowledge_base_results' => 'integer',
    ];

    /**
     * Tenant al que pertenece el bot
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Usuarios asignados al bot con permisos específicos
     * 
     * PIVOT: bot_user
     * - can_manage: Puede configurar el bot
     * - can_view_analytics: Puede ver métricas
     * - can_chat: Puede usar chat en vivo
     * - can_train_kb: Puede entrenar knowledge base
     * - can_delete_data: Puede eliminar datos
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bot_user')
            ->withPivot([
                'can_manage',
                'can_view_analytics',
                'can_chat',
                'can_train_kb',
                'can_delete_data',
                'assigned_at',
            ])
            ->withTimestamps();
    }

    /**
     * Conversaciones del bot
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Knowledge base del bot
     */
    public function knowledgeBase(): HasOne
    {
        return $this->hasOne(KnowledgeBase::class);
    }

    /**
     * Webhooks configurados para el bot
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    /**
     * Eventos de analytics del bot
     */
    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    /**
     * Scope: Solo bots activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Bots con knowledge base habilitada
     */
    public function scopeWithKnowledgeBase($query)
    {
        return $query->where('use_knowledge_base', true);
    }

    /**
     * Verificar si el bot está dentro del horario de atención
     */
    public function isWithinBusinessHours(): bool
    {
        $now = now();
        $currentDay = strtolower($now->format('l')); // 'monday', 'tuesday', etc
        $currentTime = $now->format('H:i:s');

        // Verificar si hoy es día laboral
        if (!in_array($currentDay, $this->business_days ?? [])) {
            return false;
        }

        // Verificar horario
        return $currentTime >= $this->business_hours_start 
            && $currentTime <= $this->business_hours_end;
    }

    /**
     * Verificar si el bot tiene knowledge base configurada
     */
    public function hasKnowledgeBase(): bool
    {
        return $this->use_knowledge_base && $this->knowledgeBase()->exists();
    }

    /**
     * Obtener conversaciones activas
     */
    public function activeConversations()
    {
        return $this->conversations()->where('status', 'active');
    }

    /**
     * Contar conversaciones por estado
     */
    public function conversationsByStatus(string $status): int
    {
        return $this->conversations()->where('status', $status)->count();
    }
}