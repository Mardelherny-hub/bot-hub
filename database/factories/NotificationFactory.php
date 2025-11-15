<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Notification Factory
 * 
 * Factory para generar notificaciones de prueba.
 * 
 * Estados disponibles:
 * - unread: Notificación no leída
 * - read: Notificación leída
 * - urgent: Prioridad urgente
 * - highPriority: Prioridad alta
 * - lowPriority: Prioridad baja
 * - withAction: Con URL de acción
 * - withoutAction: Sin URL de acción
 * - messageNotification: Notificación de mensaje
 * - assignmentNotification: Notificación de asignación
 * - limitNotification: Notificación de límite
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(Notification::TYPES);
        
        return [
            'user_id' => User::factory(),
            'type' => $type,
            'title' => $this->getTitleForType($type),
            'message' => fake()->sentence(10),
            'action_url' => fake()->boolean(70) ? fake()->url() : null,
            'is_read' => false,
            'read_at' => null,
            'priority' => fake()->randomElement(['low', 'normal', 'high']),
            'metadata' => [
                'source' => fake()->randomElement(['system', 'user', 'bot']),
            ],
            'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }

    /**
     * Estado: Notificación no leída.
     * 
     * @return static
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Estado: Notificación leída.
     * 
     * @return static
     */
    public function read(): static
    {
        return $this->state(function (array $attributes) {
            $createdAt = $attributes['created_at'] ?? now();
            
            return [
                'is_read' => true,
                'read_at' => fake()->dateTimeBetween($createdAt, 'now'),
            ];
        });
    }

    /**
     * Estado: Prioridad urgente.
     * 
     * @return static
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Estado: Prioridad alta.
     * 
     * @return static
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Estado: Prioridad normal.
     * 
     * @return static
     */
    public function normalPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'normal',
        ]);
    }

    /**
     * Estado: Prioridad baja.
     * 
     * @return static
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'low',
        ]);
    }

    /**
     * Estado: Con URL de acción.
     * 
     * @param string|null $url
     * @return static
     */
    public function withAction(?string $url = null): static
    {
        return $this->state(fn (array $attributes) => [
            'action_url' => $url ?? '/conversations/' . fake()->numberBetween(1, 100),
        ]);
    }

    /**
     * Estado: Sin URL de acción.
     * 
     * @return static
     */
    public function withoutAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_url' => null,
        ]);
    }

    /**
     * Estado: Notificación de nuevo mensaje.
     * 
     * @return static
     */
    public function messageNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'message.new',
            'title' => 'Nuevo mensaje',
            'message' => 'Tienes un nuevo mensaje de ' . fake()->name(),
            'priority' => 'normal',
        ]);
    }

    /**
     * Estado: Notificación de asignación.
     * 
     * @return static
     */
    public function assignmentNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'conversation.assigned',
            'title' => 'Conversación asignada',
            'message' => 'Se te asignó una nueva conversación',
            'priority' => 'high',
        ]);
    }

    /**
     * Estado: Notificación de límite.
     * 
     * @return static
     */
    public function limitNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bot.limit.reached',
            'title' => 'Límite alcanzado',
            'message' => 'Has alcanzado el límite de tu plan',
            'priority' => 'urgent',
            'action_url' => '/settings/billing',
        ]);
    }

    /**
     * Estado: Notificación del sistema.
     * 
     * @return static
     */
    public function systemNotification(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => fake()->randomElement(['system.error', 'system.warning', 'system.info']),
            'title' => 'Notificación del sistema',
            'message' => fake()->sentence(12),
        ]);
    }

    /**
     * Estado: Notificación reciente (últimas 24h).
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
     * Estado: Notificación antigua.
     * 
     * @return static
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-30 days', '-7 days'),
        ]);
    }

    /**
     * Obtener título apropiado según el tipo.
     * 
     * @param string $type
     * @return string
     */
    private function getTitleForType(string $type): string
    {
        return match ($type) {
            'message.new' => 'Nuevo mensaje',
            'message.failed' => 'Error al enviar mensaje',
            'conversation.assigned' => 'Conversación asignada',
            'conversation.transferred' => 'Conversación transferida',
            'conversation.closed' => 'Conversación cerrada',
            'bot.limit.reached' => 'Límite alcanzado',
            'bot.limit.warning' => 'Advertencia de límite',
            'system.error' => 'Error del sistema',
            'system.warning' => 'Advertencia del sistema',
            'system.info' => 'Información del sistema',
            default => 'Notificación',
        };
    }
}