<?php

namespace Database\Factories;

use App\Models\AnalyticsEvent;
use App\Models\Bot;
use App\Models\Conversation;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * AnalyticsEvent Factory
 * 
 * Factory para generar eventos de analytics de prueba.
 * 
 * Estados disponibles:
 * - successful: Evento exitoso
 * - failed: Evento fallido
 * - messageEvent: Evento de mensaje
 * - conversationEvent: Evento de conversación
 * - aiEvent: Evento de IA
 * - webhookEvent: Evento de webhook
 * - withCost: Evento con costo asociado
 * - slow: Evento con tiempo de respuesta alto
 * - fast: Evento con tiempo de respuesta bajo
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnalyticsEvent>
 */
class AnalyticsEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AnalyticsEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventType = fake()->randomElement(AnalyticsEvent::EVENT_TYPES);
        $category = explode('.', $eventType)[0];
        
        return [
            'tenant_id' => Tenant::factory(),
            'bot_id' => Bot::factory(),
            'conversation_id' => null,
            'event_type' => $eventType,
            'event_category' => $category,
            'event_data' => [
                'user_id' => fake()->numberBetween(1, 100),
                'source' => fake()->randomElement(['whatsapp', 'api', 'web']),
            ],
            'response_time_ms' => fake()->numberBetween(100, 2000),
            'tokens_used' => null,
            'cost_usd' => null,
            'success' => true,
            'error_message' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Estado: Evento exitoso.
     * 
     * @return static
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => true,
            'error_message' => null,
        ]);
    }

    /**
     * Estado: Evento fallido.
     * 
     * @return static
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => false,
            'error_message' => fake()->randomElement([
                'Connection timeout',
                'API rate limit exceeded',
                'Invalid response from OpenAI',
                'WhatsApp API error',
                'Database connection failed',
            ]),
        ]);
    }

    /**
     * Estado: Evento de mensaje.
     * 
     * @return static
     */
    public function messageEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => fake()->randomElement([
                'message.sent',
                'message.received',
                'message.failed',
            ]),
            'event_category' => 'message',
            'conversation_id' => Conversation::factory(),
        ]);
    }

    /**
     * Estado: Evento de conversación.
     * 
     * @return static
     */
    public function conversationEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => fake()->randomElement([
                'conversation.started',
                'conversation.closed',
                'conversation.assigned',
            ]),
            'event_category' => 'conversation',
            'conversation_id' => Conversation::factory(),
        ]);
    }

    /**
     * Estado: Evento de IA.
     * 
     * @return static
     */
    public function aiEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'ai.completion.success',
            'event_category' => 'ai',
            'tokens_used' => fake()->numberBetween(50, 1500),
            'cost_usd' => fake()->randomFloat(6, 0.001, 0.05),
            'response_time_ms' => fake()->numberBetween(500, 3000),
            'event_data' => [
                'model' => 'gpt-4',
                'prompt_tokens' => fake()->numberBetween(20, 500),
                'completion_tokens' => fake()->numberBetween(30, 1000),
            ],
        ]);
    }

    /**
     * Estado: Evento de webhook.
     * 
     * @return static
     */
    public function webhookEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => fake()->randomElement([
                'webhook.triggered',
                'webhook.success',
                'webhook.failed',
            ]),
            'event_category' => 'webhook',
            'response_time_ms' => fake()->numberBetween(100, 5000),
        ]);
    }

    /**
     * Estado: Evento con costo asociado.
     * 
     * @return static
     */
    public function withCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_usd' => fake()->randomFloat(6, 0.0001, 0.1),
        ]);
    }

    /**
     * Estado: Evento lento (>3 segundos).
     * 
     * @return static
     */
    public function slow(): static
    {
        return $this->state(fn (array $attributes) => [
            'response_time_ms' => fake()->numberBetween(3000, 10000),
        ]);
    }

    /**
     * Estado: Evento rápido (<500ms).
     * 
     * @return static
     */
    public function fast(): static
    {
        return $this->state(fn (array $attributes) => [
            'response_time_ms' => fake()->numberBetween(50, 500),
        ]);
    }

    /**
     * Estado: Evento de hoy.
     * 
     * @return static
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('today', 'now'),
        ]);
    }

    /**
     * Estado: Evento de esta semana.
     * 
     * @return static
     */
    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Estado: Evento de este mes.
     * 
     * @return static
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Estado: Evento con conversación.
     * 
     * @param int $conversationId
     * @return static
     */
    public function forConversation(int $conversationId): static
    {
        return $this->state(fn (array $attributes) => [
            'conversation_id' => $conversationId,
        ]);
    }
}