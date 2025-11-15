<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * MessageFactory
 * 
 * Genera mensajes de prueba con diferentes tipos y estados.
 * 
 * USO:
 * Message::factory()->create(); // Mensaje aleatorio
 * Message::factory()->fromUser()->create(); // Del usuario
 * Message::factory()->fromBot()->create(); // Del bot
 * Message::factory()->fromAgent()->create(); // De agente humano
 */
class MessageFactory extends Factory
{
    /**
     * Estado por defecto
     */
    public function definition(): array
    {
        $direction = fake()->randomElement(['inbound', 'outbound']);
        $senderType = $direction === 'inbound' ? 'user' : fake()->randomElement(['bot', 'agent']);

        return [
            'conversation_id' => Conversation::factory(),
            'direction' => $direction,
            'sender_type' => $senderType,
            'sender_id' => $senderType === 'agent' ? User::factory() : null,
            'content' => fake()->sentence(),
            'content_type' => 'text',
            'interactive_type' => null,
            'interactive_payload' => null,
            'media_url' => null,
            'media_mime_type' => null,
            'external_message_id' => 'wamid.' . fake()->uuid(),
            'status' => 'sent',
            'error_message' => null,
            'ai_generated' => $senderType === 'bot',
            'ai_model_used' => $senderType === 'bot' ? 'gpt-4' : null,
            'ai_tokens_used' => $senderType === 'bot' ? fake()->numberBetween(50, 500) : null,
            'processing_time_ms' => $senderType === 'bot' ? fake()->numberBetween(500, 3000) : null,
            'metadata' => null,
        ];
    }

    /**
     * Estado: Mensaje del usuario (inbound)
     */
    public function fromUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => 'inbound',
            'sender_type' => 'user',
            'sender_id' => null,
            'ai_generated' => false,
            'ai_model_used' => null,
            'ai_tokens_used' => null,
            'processing_time_ms' => null,
        ]);
    }

    /**
     * Estado: Mensaje del bot (outbound)
     */
    public function fromBot(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => 'outbound',
            'sender_type' => 'bot',
            'sender_id' => null,
            'ai_generated' => true,
            'ai_model_used' => fake()->randomElement(['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo']),
            'ai_tokens_used' => fake()->numberBetween(50, 500),
            'processing_time_ms' => fake()->numberBetween(500, 3000),
        ]);
    }

    /**
     * Estado: Mensaje de agente humano (outbound)
     */
    public function fromAgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'direction' => 'outbound',
            'sender_type' => 'agent',
            'sender_id' => User::factory(),
            'ai_generated' => false,
            'ai_model_used' => null,
            'ai_tokens_used' => null,
            'processing_time_ms' => null,
        ]);
    }

    /**
     * Estado: Mensaje con imagen
     */
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => 'image',
            'media_url' => fake()->imageUrl(),
            'media_mime_type' => 'image/jpeg',
        ]);
    }

    /**
     * Estado: Mensaje con documento
     */
    public function withDocument(): static
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => 'document',
            'media_url' => fake()->url(),
            'media_mime_type' => 'application/pdf',
        ]);
    }

    /**
     * Estado: Mensaje interactivo con botones
     */
    public function withButtons(): static
    {
        return $this->state(fn (array $attributes) => [
            'interactive_type' => 'button',
            'interactive_payload' => [
                'buttons' => [
                    ['id' => '1', 'title' => 'Opción 1'],
                    ['id' => '2', 'title' => 'Opción 2'],
                ],
            ],
        ]);
    }

    /**
     * Estado: Mensaje entregado
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
        ]);
    }

    /**
     * Estado: Mensaje leído
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'read',
        ]);
    }

    /**
     * Estado: Mensaje fallido
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
        ]);
    }
}