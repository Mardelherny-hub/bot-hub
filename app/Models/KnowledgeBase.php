<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * KnowledgeBase Model
 * 
 * Base de conocimiento para RAG (Retrieval Augmented Generation).
 * Cada bot tiene una knowledge base que contiene documentos procesados.
 * 
 * RELACIONES:
 * - belongsTo: Bot (1:1)
 * - hasMany: KnowledgeDocument
 * 
 * IMPORTANTE: Esta tabla almacena la configuración de la KB.
 * Los documentos individuales están en knowledge_documents.
 * Los chunks y embeddings están en document_chunks.
 */
class KnowledgeBase extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'bot_id',
        'name',
        'description',
        'is_active',
        'document_count',
        'total_tokens',
        'last_trained_at',
        'embedding_model',
        'settings',
    ];

    /**
     * Casts de atributos
     */
    protected $casts = [
        'is_active' => 'boolean',
        'document_count' => 'integer',
        'total_tokens' => 'integer',
        'last_trained_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Bot al que pertenece la knowledge base
     */
    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }

    /**
     * Documentos de la knowledge base
     */
    public function documents(): HasMany
    {
        return $this->hasMany(KnowledgeDocument::class);
    }

    /**
     * Scope: Knowledge bases activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Knowledge bases con documentos
     */
    public function scopeWithDocuments($query)
    {
        return $query->where('document_count', '>', 0);
    }

    /**
     * Verificar si la KB está activa
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Verificar si tiene documentos
     */
    public function hasDocuments(): bool
    {
        return $this->document_count > 0;
    }

    /**
     * Verificar si fue entrenada recientemente (últimas 24 horas)
     */
    public function wasRecentlyTrained(): bool
    {
        if (!$this->last_trained_at) {
            return false;
        }

        return $this->last_trained_at->isAfter(now()->subDay());
    }

    /**
     * Incrementar contador de documentos
     */
    public function incrementDocumentCount(): void
    {
        $this->increment('document_count');
    }

    /**
     * Decrementar contador de documentos
     */
    public function decrementDocumentCount(): void
    {
        $this->decrement('document_count');
    }

    /**
     * Actualizar contador de tokens totales
     */
    public function updateTotalTokens(): void
    {
        $totalTokens = $this->documents()->sum('token_count');
        $this->update(['total_tokens' => $totalTokens]);
    }

    /**
     * Marcar como entrenada
     */
    public function markAsTrained(): void
    {
        $this->update(['last_trained_at' => now()]);
    }

    /**
     * Obtener configuración específica
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Establecer configuración específica
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }

    /**
     * Obtener chunk_size de configuración
     */
    public function getChunkSize(): int
    {
        return $this->getSetting('chunk_size', 500);
    }

    /**
     * Obtener chunk_overlap de configuración
     */
    public function getChunkOverlap(): int
    {
        return $this->getSetting('chunk_overlap', 50);
    }

    /**
     * Obtener max_results de configuración
     */
    public function getMaxResults(): int
    {
        return $this->getSetting('max_results', 5);
    }

    /**
     * Obtener similarity_threshold de configuración
     */
    public function getSimilarityThreshold(): float
    {
        return $this->getSetting('similarity_threshold', 0.7);
    }

    /**
     * Activar la knowledge base
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Desactivar la knowledge base
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}