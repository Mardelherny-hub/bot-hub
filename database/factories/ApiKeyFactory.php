<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ApiKey Factory
 * 
 * Factory para generar API keys de prueba.
 * 
 * Estados disponibles:
 * - active: API key activa
 * - inactive: API key inactiva
 * - expired: API key expirada
 * - withExpiration: Con fecha de expiración futura
 * - withRateLimit: Con límite de rate
 * - withPermissions: Con permisos específicos
 * - fullAccess: Acceso completo sin restricciones
 * - readOnly: Solo permisos de lectura
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiKey>
 */
class ApiKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApiKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plainKey = ApiKey::generate();
        
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->words(3, true) . ' API Key',
            'key' => ApiKey::hash($plainKey),
            'key_preview' => ApiKey::createPreview($plainKey),
            'permissions' => null, // Sin restricciones = acceso total
            'is_active' => true,
            'last_used_at' => null,
            'usage_count' => 0,
            'rate_limit_per_minute' => null,
            'expires_at' => null,
        ];
    }

    /**
     * Estado: API key activa.
     * 
     * @return static
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Estado: API key inactiva.
     * 
     * @return static
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Estado: API key expirada.
     * 
     * @return static
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Estado: API key con fecha de expiración futura.
     * 
     * @param int $days Días hasta la expiración
     * @return static
     */
    public function withExpiration(int $days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addDays($days),
        ]);
    }

    /**
     * Estado: API key sin expiración.
     * 
     * @return static
     */
    public function neverExpires(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }

    /**
     * Estado: API key con límite de rate.
     * 
     * @param int $limit Requests por minuto
     * @return static
     */
    public function withRateLimit(int $limit = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'rate_limit_per_minute' => $limit,
        ]);
    }

    /**
     * Estado: API key sin límite de rate.
     * 
     * @return static
     */
    public function withoutRateLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'rate_limit_per_minute' => null,
        ]);
    }

    /**
     * Estado: API key con permisos específicos.
     * 
     * @param array $permissions
     * @return static
     */
    public function withPermissions(array $permissions): static
    {
        return $this->state(fn (array $attributes) => [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Estado: Acceso completo sin restricciones.
     * 
     * @return static
     */
    public function fullAccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'permissions' => null,
        ]);
    }

    /**
     * Estado: Solo permisos de lectura.
     * 
     * @return static
     */
    public function readOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'permissions' => [
                'bots.read',
                'conversations.read',
                'messages.read',
                'analytics.read',
            ],
        ]);
    }

    /**
     * Estado: API key recientemente usada.
     * 
     * @return static
     */
    public function recentlyUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'usage_count' => fake()->numberBetween(10, 1000),
        ]);
    }

    /**
     * Estado: API key nunca usada.
     * 
     * @return static
     */
    public function neverUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => null,
            'usage_count' => 0,
        ]);
    }

    /**
     * Estado: API key con alto uso.
     * 
     * @return static
     */
    public function highUsage(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_used_at' => fake()->dateTimeBetween('-1 hour', 'now'),
            'usage_count' => fake()->numberBetween(5000, 50000),
        ]);
    }
}