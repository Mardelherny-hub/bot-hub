<?php

namespace Database\Factories;

use App\Models\KnowledgeBase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * KnowledgeDocumentFactory
 * 
 * Genera documentos de knowledge base de prueba.
 * 
 * USO:
 * KnowledgeDocument::factory()->create(); // Documento básico
 * KnowledgeDocument::factory()->completed()->create(); // Procesado
 * KnowledgeDocument::factory()->uploaded()->create(); // Subido por usuario
 */
class KnowledgeDocumentFactory extends Factory
{
    /**
     * Estado por defecto
     */
    public function definition(): array
    {
        return [
            'knowledge_base_id' => KnowledgeBase::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(10, true),
            'source_type' => 'manual',
            'source_url' => null,
            'file_path' => null,
            'file_size' => null,
            'file_type' => null,
            'chunk_count' => 0,
            'token_count' => 0,
            'embedding_status' => 'pending',
            'processed_at' => null,
            'metadata' => null,
        ];
    }

    /**
     * Estado: Documento completado (procesado)
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'chunk_count' => fake()->numberBetween(5, 50),
            'token_count' => fake()->numberBetween(500, 5000),
            'embedding_status' => 'completed',
            'processed_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Estado: Documento pendiente
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'embedding_status' => 'pending',
            'chunk_count' => 0,
            'token_count' => 0,
            'processed_at' => null,
        ]);
    }

    /**
     * Estado: Documento en procesamiento
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'embedding_status' => 'processing',
            'chunk_count' => 0,
            'token_count' => 0,
            'processed_at' => null,
        ]);
    }

    /**
     * Estado: Documento fallido
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'embedding_status' => 'failed',
            'chunk_count' => 0,
            'token_count' => 0,
            'processed_at' => null,
        ]);
    }

    /**
     * Estado: Documento subido (upload)
     */
    public function uploaded(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'upload',
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
            'file_size' => fake()->numberBetween(10240, 5242880), // 10KB - 5MB
            'file_type' => fake()->randomElement(['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain']),
        ]);
    }

    /**
     * Estado: Documento de URL
     */
    public function fromUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'url',
            'source_url' => fake()->url(),
        ]);
    }

    /**
     * Estado: Documento manual
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'manual',
        ]);
    }

    /**
     * Estado: Documento de API
     */
    public function fromApi(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'api',
        ]);
    }

    /**
     * Estado: Documento PDF
     */
    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'upload',
            'file_path' => 'documents/' . fake()->uuid() . '.pdf',
            'file_size' => fake()->numberBetween(102400, 5242880),
            'file_type' => 'application/pdf',
        ]);
    }

    /**
     * Estado: Documento DOCX
     */
    public function docx(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'upload',
            'file_path' => 'documents/' . fake()->uuid() . '.docx',
            'file_size' => fake()->numberBetween(51200, 2097152),
            'file_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Estado: Documento TXT
     */
    public function txt(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'upload',
            'file_path' => 'documents/' . fake()->uuid() . '.txt',
            'file_size' => fake()->numberBetween(1024, 102400),
            'file_type' => 'text/plain',
        ]);
    }

    /**
     * Estado: Documento grande
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(100, true),
            'chunk_count' => fake()->numberBetween(50, 200),
            'token_count' => fake()->numberBetween(10000, 50000),
            'file_size' => fake()->numberBetween(1048576, 10485760), // 1MB - 10MB
        ]);
    }

    /**
     * Estado: Documento pequeño
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(2, true),
            'chunk_count' => fake()->numberBetween(1, 5),
            'token_count' => fake()->numberBetween(100, 500),
            'file_size' => fake()->numberBetween(1024, 10240), // 1KB - 10KB
        ]);
    }
}