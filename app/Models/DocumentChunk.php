<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DocumentChunk Model
 * 
 * Representa un fragmento (chunk) de un documento procesado para RAG.
 * Cada chunk contiene texto y su embedding vectorial para búsqueda semántica.
 * 
 * Relaciones:
 * - BelongsTo: KnowledgeDocument (documento padre)
 * 
 * @property int $id
 * @property int $knowledge_document_id
 * @property int $chunk_index
 * @property string $content
 * @property int $token_count
 * @property array|null $embedding Vector de 1536 dimensiones
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class DocumentChunk extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_chunks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'knowledge_document_id',
        'chunk_index',
        'content',
        'token_count',
        'embedding',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'chunk_index' => 'integer',
        'token_count' => 'integer',
        'embedding' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'embedding', // Ocultar vector por ser muy grande
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Documento al que pertenece este chunk.
     * 
     * @return BelongsTo
     */
    public function knowledgeDocument(): BelongsTo
    {
        return $this->belongsTo(KnowledgeDocument::class, 'knowledge_document_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Chunks con embeddings generados.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithEmbedding($query)
    {
        return $query->whereNotNull('embedding');
    }

    /**
     * Scope: Chunks sin embeddings.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutEmbedding($query)
    {
        return $query->whereNull('embedding');
    }

    /**
     * Scope: Ordenar por índice del chunk.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('chunk_index', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS DE NEGOCIO
    |--------------------------------------------------------------------------
    */

    /**
     * Verificar si el chunk tiene embedding generado.
     * 
     * @return bool
     */
    public function hasEmbedding(): bool
    {
        return !is_null($this->embedding) && !empty($this->embedding);
    }

    /**
     * Obtener el tamaño del embedding.
     * 
     * @return int
     */
    public function getEmbeddingSize(): int
    {
        return $this->hasEmbedding() ? count($this->embedding) : 0;
    }

    /**
     * Verificar si el embedding tiene el tamaño correcto (1536 para OpenAI).
     * 
     * @return bool
     */
    public function hasValidEmbedding(): bool
    {
        return $this->getEmbeddingSize() === 1536;
    }
}