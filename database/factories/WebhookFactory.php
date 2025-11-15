<?php

namespace Database\Factories;

use App\Models\Bot;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Webhook Factory
 * 
 * Factory para generar webhooks de prueba.
 * 
 * Estados disponibles:
 * - active: Webhook activo
 * - inactive: Webhook inactivo
 * - withSecret: Webhook con secret para firma HMAC
 * - withoutSecret: Webhook sin secret
 * - forMessages: Escucha solo eventos de mensajes
 * - forConversations: Escucha solo eventos de conversaciones
 * - withHighSuccessRate: Webhook con alta tasa de éxito
 * - withHighFailureRate: Webhook con alta tasa de fallos
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Webhook>
 */
class WebhookFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Webhook::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bot_id' => Bot::factory(),
            'name' => fake()->words(3, true) . ' Webhook',
            'url' => fake()->url(),
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'BotHub-Webhook/1.0',
            ],
            'events' => fake()->randomElements(Webhook::VALID_EVENTS, fake()->numberBetween(1, 3)),
            'is_active' => true,
            'max_retries' => 3,
            'timeout_seconds' => 30,
            'success_count' => 0,
            'failure_count' => 0,
            'last_triggered_at' => null,
            'last_success_at' => null,
            'last_failure_at' => null,
            'last_error' => null,
            'secret' => null,
        ];
    }

    /**
     * Estado: Webhook activo.
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
     * Estado: Webhook inactivo.
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
     * Estado: Webhook con secret para firma HMAC.
     * 
     * @return static
     */
    public function withSecret(): static
    {
        return $this->state(fn (array $attributes) => [
            'secret' => bin2hex(random_bytes(32)),
        ]);
    }

    /**
     * Estado: Webhook sin secret.
     * 
     * @return static
     */
    public function withoutSecret(): static
    {
        return $this->state(fn (array $attributes) => [
            'secret' => null,
        ]);
    }

    /**
     * Estado: Webhook que escucha solo eventos de mensajes.
     * 
     * @return static
     */
    public function forMessages(): static
    {
        return $this->state(fn (array $attributes) => [
            'events' => ['message.received', 'message.sent'],
        ]);
    }

    /**
     * Estado: Webhook que escucha solo eventos de conversaciones.
     * 
     * @return static
     */
    public function forConversations(): static
    {
        return $this->state(fn (array $attributes) => [
            'events' => ['conversation.started', 'conversation.closed', 'conversation.assigned'],
        ]);
    }

    /**
     * Estado: Webhook con alta tasa de éxito.
     * 
     * @return static
     */
    public function withHighSuccessRate(): static
    {
        return $this->state(fn (array $attributes) => [
            'success_count' => fake()->numberBetween(80, 100),
            'failure_count' => fake()->numberBetween(0, 10),
            'last_triggered_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'last_success_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    /**
     * Estado: Webhook con alta tasa de fallos.
     * 
     * @return static
     */
    public function withHighFailureRate(): static
    {
        return $this->state(fn (array $attributes) => [
            'success_count' => fake()->numberBetween(0, 20),
            'failure_count' => fake()->numberBetween(50, 100),
            'last_triggered_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'last_failure_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'last_error' => 'Connection timeout',
        ]);
    }

    /**
     * Estado: Webhook con headers personalizados.
     * 
     * @param array $headers
     * @return static
     */
    public function withHeaders(array $headers): static
    {
        return $this->state(fn (array $attributes) => [
            'headers' => array_merge($attributes['headers'] ?? [], $headers),
        ]);
    }

    /**
     * Estado: Webhook con Authorization header.
     * 
     * @param string|null $token
     * @return static
     */
    public function withAuth(?string $token = null): static
    {
        return $this->state(fn (array $attributes) => [
            'headers' => array_merge($attributes['headers'] ?? [], [
                'Authorization' => 'Bearer ' . ($token ?? fake()->sha256()),
            ]),
        ]);
    }

    /**
     * Estado: Webhook para evento específico.
     * 
     * @param string $event
     * @return static
     */
    public function forEvent(string $event): static
    {
        return $this->state(fn (array $attributes) => [
            'events' => [$event],
        ]);
    }

    /**
     * Estado: Webhook con método HTTP específico.
     * 
     * @param string $method
     * @return static
     */
    public function withMethod(string $method): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => strtoupper($method),
        ]);
    }
}