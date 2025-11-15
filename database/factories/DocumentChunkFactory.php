<?php

namespace Database\Factories;

use App\Models\DocumentChunk;
use App\Models\KnowledgeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * DocumentChunk Factory
 * 
 * Factory para generar chunks de documentos de prueba.
 * 
 * Estados disponibles:
 * - withEmbedding: Chunk con embedding generado
 * - withoutEmbedding: Chunk sin embedding
 * - firstChunk: Primer chunk del documento (index 0)
 * - longContent: Chunk con contenido extenso
 * - shortContent: Chunk con contenido corto
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentChunk>
 */
class DocumentChunkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DocumentChunk::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'knowledge_document_id' => KnowledgeDocument::factory(),
            'chunk_index' => fake()->numberBetween(0, 20),
            'content' => fake()->paragraphs(3, true),
            'token_count' => fake()->numberBetween(100, 500),
            'embedding' => null,
            'metadata' => [
                'page' => fake()->numberBetween(1, 50),
                'section' => fake()->word(),
            ],
        ];
    }

    /**
     * Estado: Chunk con embedding generado.
     * 
     * @return static
     */
    public function withEmbedding(): static
    {
        return $this->state(fn (array $attributes) => [
            'embedding' => $this->generateFakeEmbedding(),
        ]);
    }

    /**
     * Estado: Chunk sin embedding.
     * 
     * @return static
     */
    public function withoutEmbedding(): static
    {
        return $this->state(fn (array $attributes) => [
            'embedding' => null,
        ]);
    }

    /**
     * Estado: Primer chunk del documento (índice 0).
     * 
     * @return static
     */
    public function firstChunk(): static
    {
        return $this->state(fn (array $attributes) => [
            'chunk_index' => 0,
        ]);
    }

    /**
     * Estado: Chunk con contenido extenso.
     * 
     * @return static
     */
    public function longContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(10, true),
            'token_count' => fake()->numberBetween(800, 1500),
        ]);
    }

    /**
     * Estado: Chunk con contenido corto.
     * 
     * @return static
     */
    public function shortContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->sentence(20),
            'token_count' => fake()->numberBetween(20, 100),
        ]);
    }

    /**
     * Estado: Chunk específico por índice.
     * 
     * @param int $index
     * @return static
     */
    public function atIndex(int $index): static
    {
        return $this->state(fn (array $attributes) => [
            'chunk_index' => $index,
        ]);
    }

    /**
     * Estado: Chunk con metadata específica.
     * 
     * @param array $metadata
     * @return static
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }

    /**
     * Generar un embedding falso de 1536 dimensiones.
     * 
     * @return array
     */
    private function generateFakeEmbedding(): array
    {
        $embedding = [];
        for ($i = 0; $i < 1536; $i++) {
            $embedding[] = fake()->randomFloat(6, -1, 1);
        }
        return $embedding;
    }
}