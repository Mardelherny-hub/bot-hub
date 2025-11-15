<?php

namespace Database\Factories;

use App\Models\Bot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * ConversationFactory
 * 
 * Genera conversaciones de prueba con diferentes estados.
 * 
 * USO:
 * Conversation::factory()->create(); // Conversación activa
 * Conversation::factory()->withHuman()->create(); // Con agente asignado
 * Conversation::factory()->closed()->create(); // Cerrada
 */
class ConversationFactory extends Factory
{
    /**
     * Estado por defecto
     */
    public function definition(): array
    {
        return [
            'bot_id' => Bot::factory(),
            'external_user_id' => '+549' . fake()->numerify('##########'),
            'external_user_name' => fake()->name(),
            'channel' => 'whatsapp',
            'status' => 'active',
            'assigned_user_id' => null,
            'handoff_reason' => null,
            'handoff_at' => null,
            'last_message_at' => now(),
            'message_count' => fake()->numberBetween(1, 50),
            'first_response_time_ms' => fake()->numberBetween(500, 5000),
            'sentiment_score' => fake()->randomFloat(2, -1, 1),
            'satisfaction_rating' => null,
            'tags' => fake()->randomElement([
                [],
                ['support'],
                ['sales'],
                ['urgent'],
                ['support', 'urgent'],
                ['sales', 'vip'],
            ]),
            'metadata' => null,
            'closed_at' => null,
        ];
    }

    /**
     * Estado: Conversación activa
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'assigned_user_id' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * Estado: Esperando asignación a humano
     */
    public function waitingHuman(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waiting_human',
            'handoff_reason' => fake()->randomElement([
                'Cliente solicita hablar con humano',
                'Problema complejo que requiere atención personalizada',
                'Cliente insatisfecho con respuestas del bot',
            ]),
        ]);
    }

    /**
     * Estado: Con agente humano asignado
     */
    public function withHuman(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'with_human',
            'assigned_user_id' => User::factory(),
            'handoff_reason' => fake()->randomElement([
                'Cliente solicita hablar con humano',
                'Problema técnico complejo',
                'Consulta de ventas',
            ]),
            'handoff_at' => now()->subMinutes(fake()->numberBetween(5, 60)),
        ]);
    }

    /**
     * Estado: Resuelta
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'satisfaction_rating' => fake()->numberBetween(1, 5),
        ]);
    }

    /**
     * Estado: Cerrada
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'closed_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'satisfaction_rating' => fake()->numberBetween(1, 5),
        ]);
    }

    /**
     * Estado: Conversación reciente
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_message_at' => now()->subMinutes(fake()->numberBetween(1, 30)),
            'message_count' => fake()->numberBetween(1, 10),
        ]);
    }

    /**
     * Estado: Conversación antigua
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_message_at' => now()->subDays(fake()->numberBetween(7, 90)),
            'message_count' => fake()->numberBetween(10, 100),
        ]);
    }

    /**
     * Estado: Con sentimiento positivo
     */
    public function positiveSentiment(): static
    {
        return $this->state(fn (array $attributes) => [
            'sentiment_score' => fake()->randomFloat(2, 0.5, 1.0),
        ]);
    }

    /**
     * Estado: Con sentimiento negativo
     */
    public function negativeSentiment(): static
    {
        return $this->state(fn (array $attributes) => [
            'sentiment_score' => fake()->randomFloat(2, -1.0, -0.5),
        ]);
    }

    /**
     * Estado: Con alta satisfacción
     */
    public function highSatisfaction(): static
    {
        return $this->state(fn (array $attributes) => [
            'satisfaction_rating' => fake()->numberBetween(4, 5),
        ]);
    }

    /**
     * Estado: Con baja satisfacción
     */
    public function lowSatisfaction(): static
    {
        return $this->state(fn (array $attributes) => [
            'satisfaction_rating' => fake()->numberBetween(1, 2),
        ]);
    }

    /**
     * Estado: Urgente
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => ['urgent', 'support'],
        ]);
    }

    /**
     * Estado: Conversación de ventas
     */
    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => ['sales'],
        ]);
    }

    /**
     * Estado: Conversación de soporte
     */
    public function support(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => ['support'],
        ]);
    }
}