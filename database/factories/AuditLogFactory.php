<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * AuditLog Factory
 * 
 * Factory para generar logs de auditoría de prueba.
 * 
 * Estados disponibles:
 * - created: Log de creación
 * - updated: Log de actualización
 * - deleted: Log de eliminación
 * - systemAction: Acción del sistema (sin usuario)
 * - userAction: Acción de usuario
 * - forBot: Log relacionado con Bot
 * - forConversation: Log relacionado con Conversation
 * - forUser: Log relacionado con User
 * - withChanges: Con valores old y new
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $action = fake()->randomElement(['created', 'updated', 'deleted']);
        $entityType = fake()->randomElement(['Bot', 'Conversation', 'User', 'KnowledgeBase']);
        
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => fake()->numberBetween(1, 1000),
            'old_values' => null,
            'new_values' => $this->generateSampleValues(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Estado: Log de creación.
     * 
     * @return static
     */
    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'created',
            'old_values' => null,
            'new_values' => $this->generateSampleValues(),
        ]);
    }

    /**
     * Estado: Log de actualización.
     * 
     * @return static
     */
    public function updated(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'updated',
            'old_values' => $this->generateSampleValues(),
            'new_values' => $this->generateSampleValues(),
        ]);
    }

    /**
     * Estado: Log de eliminación.
     * 
     * @return static
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'deleted',
            'old_values' => $this->generateSampleValues(),
            'new_values' => null,
        ]);
    }

    /**
     * Estado: Acción del sistema (sin usuario).
     * 
     * @return static
     */
    public function systemAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Estado: Acción de usuario.
     * 
     * @param int|null $userId
     * @return static
     */
    public function userAction(?int $userId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId ?? User::factory(),
        ]);
    }

    /**
     * Estado: Log relacionado con Bot.
     * 
     * @return static
     */
    public function forBot(): static
    {
        return $this->state(fn (array $attributes) => [
            'entity_type' => 'Bot',
            'entity_id' => fake()->numberBetween(1, 100),
        ]);
    }

    /**
     * Estado: Log relacionado con Conversation.
     * 
     * @return static
     */
    public function forConversation(): static
    {
        return $this->state(fn (array $attributes) => [
            'entity_type' => 'Conversation',
            'entity_id' => fake()->numberBetween(1, 500),
        ]);
    }

    /**
     * Estado: Log relacionado con User.
     * 
     * @return static
     */
    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'entity_type' => 'User',
            'entity_id' => fake()->numberBetween(1, 50),
        ]);
    }

    /**
     * Estado: Log relacionado con KnowledgeBase.
     * 
     * @return static
     */
    public function forKnowledgeBase(): static
    {
        return $this->state(fn (array $attributes) => [
            'entity_type' => 'KnowledgeBase',
            'entity_id' => fake()->numberBetween(1, 100),
        ]);
    }

    /**
     * Estado: Log de login.
     * 
     * @return static
     */
    public function login(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'login',
            'entity_type' => 'Auth',
            'old_values' => null,
            'new_values' => [
                'success' => true,
                'timestamp' => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Estado: Log de login fallido.
     * 
     * @return static
     */
    public function failedLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'failed_login',
            'entity_type' => 'Auth',
            'old_values' => null,
            'new_values' => [
                'success' => false,
                'reason' => 'Invalid credentials',
            ],
        ]);
    }

    /**
     * Estado: Log reciente (últimas 24h).
     * 
     * @return static
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-24 hours', 'now'),
        ]);
    }

    /**
     * Estado: Log antiguo.
     * 
     * @return static
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-90 days', '-30 days'),
        ]);
    }

    /**
     * Generar valores de ejemplo para old_values o new_values.
     * 
     * @return array
     */
    private function generateSampleValues(): array
    {
        return [
            'name' => fake()->words(3, true),
            'status' => fake()->randomElement(['active', 'inactive', 'pending']),
            'is_active' => fake()->boolean(),
            'updated_by' => fake()->name(),
        ];
    }
}