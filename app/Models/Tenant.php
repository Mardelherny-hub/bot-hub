<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant Model
 * 
 * Representa una agencia o empresa que usa la plataforma BotHub.
 * Cada tenant tiene sus propios usuarios, bots y datos completamente aislados.
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'website',
        'logo_url',
        'subscription_plan',
        'subscription_status',
        'subscription_started_at',
        'subscription_ends_at',
        'monthly_conversation_limit',
        'monthly_bot_limit',
        'monthly_user_limit',
        'is_white_label',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subscription_started_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_white_label' => 'boolean',
        'settings' => 'array',
        'monthly_conversation_limit' => 'integer',
        'monthly_bot_limit' => 'integer',
        'monthly_user_limit' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Get the users that belong to this tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the bots that belong to this tenant.
     */
    public function bots()
    {
        return $this->hasMany(Bot::class);
    }

    /**
     * Get the API keys that belong to this tenant.
     */
    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Scope to get only active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('subscription_status', 'active');
    }

    /**
     * Scope to get tenants by subscription plan.
     */
    public function scopeByPlan($query, string $plan)
    {
        return $query->where('subscription_plan', $plan);
    }

    /**
     * Check if tenant has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active';
    }

    /**
     * Check if tenant has white label enabled.
     */
    public function hasWhiteLabel(): bool
    {
        return $this->is_white_label;
    }

    /**
     * Check if tenant can create more bots.
     */
    public function canCreateBot(): bool
    {
        return $this->bots()->count() < $this->monthly_bot_limit;
    }

    /**
     * Check if tenant can add more users.
     */
    public function canAddUser(): bool
    {
        return $this->users()->count() < $this->monthly_user_limit;
    }

    /**
     * Get remaining bot slots.
     */
    public function getRemainingBotSlotsAttribute(): int
    {
        return max(0, $this->monthly_bot_limit - $this->bots()->count());
    }

    /**
     * Get remaining user slots.
     */
    public function getRemainingUserSlotsAttribute(): int
    {
        return max(0, $this->monthly_user_limit - $this->users()->count());
    }
}