<?php

namespace Database\Factories;

use App\Models\Bot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * KnowledgeBaseFactory
 * 
 * Genera knowledge bases de prueba con diferentes configuraciones.
 * 
 * USO:
 * KnowledgeBase::factory()->create(); // KB básica
 * KnowledgeBase::factory()->active()->create(); // KB activa
 * KnowledgeBase::factory()->withDocuments()->create(); // KB con documentos
 */
class KnowledgeBaseFactory extends Factory
{
    /**
     * Estado por defecto
     */
    public function definition(): array
    {
        return [
            'bot_id' => Bot::factory(),
            'name' => fake()->words(3, true) . ' Knowledge Base',
            'description' => fake()->sentence(),
            'is_active' => true,
            'document_count' => 0,
            'total_tokens' => 0,
            'last_trained_at' => null,
            'embedding_model' => 'text-embedding-ada-002',
            'settings' => [
                'chunk_size' => 500,
                'chunk_overlap' => 50,
                'max_results' => 5,
                'similarity_threshold' => 0.7,
            ],
        ];
    }

    /**
     * Estado: KB activa
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Estado: KB inactiva
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Estado: KB con documentos
     */
    public function withDocuments(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_count' => fake()->numberBetween(5, 50),
            'total_tokens' => fake()->numberBetween(10000, 500000),
            'last_trained_at' => now()->subDays(fake()->numberBetween(1, 7)),
        ]);
    }

    /**
     * Estado: KB recién entrenada
     */
    public function recentlyTrained(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_trained_at' => now()->subHours(fake()->numberBetween(1, 12)),
            'document_count' => fake()->numberBetween(5, 20),
            'total_tokens' => fake()->numberBetween(10000, 100000),
        ]);
    }

    /**
     * Estado: KB sin entrenar
     */
    public function untrained(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_trained_at' => null,
            'document_count' => 0,
            'total_tokens' => 0,
        ]);
    }

    /**
     * Estado: KB grande (muchos documentos)
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_count' => fake()->numberBetween(100, 500),
            'total_tokens' => fake()->numberBetween(500000, 2000000),
            'last_trained_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Estado: KB pequeña
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_count' => fake()->numberBetween(1, 5),
            'total_tokens' => fake()->numberBetween(1000, 10000),
            'last_trained_at' => now()->subDays(fake()->numberBetween(1, 7)),
        ]);
    }

    /**
     * Estado: Configuración optimizada para chunks pequeños
     */
    public function smallChunks(): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => [
                'chunk_size' => 300,
                'chunk_overlap' => 30,
                'max_results' => 5,
                'similarity_threshold' => 0.7,
            ],
        ]);
    }

    /**
     * Estado: Configuración optimizada para chunks grandes
     */
    public function largeChunks(): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => [
                'chunk_size' => 800,
                'chunk_overlap' => 100,
                'max_results' => 3,
                'similarity_threshold' => 0.75,
            ],
        ]);
    }

    /**
     * Estado: Configuración con umbral de similitud alto
     */
    public function highSimilarityThreshold(): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => [
                'chunk_size' => 500,
                'chunk_overlap' => 50,
                'max_results' => 5,
                'similarity_threshold' => 0.85,
            ],
        ]);
    }

    /**
     * Estado: Configuración con umbral de similitud bajo
     */
    public function lowSimilarityThreshold(): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => [
                'chunk_size' => 500,
                'chunk_overlap' => 50,
                'max_results' => 10,
                'similarity_threshold' => 0.6,
            ],
        ]);
    }
}