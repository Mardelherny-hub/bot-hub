<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * KnowledgeDocument Model
 * 
 * Representa un documento individual dentro de una knowledge base.
 * 
 * FUENTES:
 * - upload: Archivo subido (PDF, DOCX, TXT)
 * - url: Contenido extraído de URL
 * - manual: Agregado manualmente por usuario
 * - api: Agregado vía API
 * 
 * ESTADOS DE EMBEDDING:
 * - pending: Esperando procesamiento
 * - processing: Generando embeddings
 * - completed: Procesado y listo para búsqueda
 * - failed: Error en procesamiento
 */
class KnowledgeDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos asignables en masa
     */
    protected $fillable = [
        'knowledge_base_id',
        'title',
        'content',
        'source_type',
        'source_url',
        'file_path',
        'file_size',
        'file_type',
        'chunk_count',
        'token_count',
        'embedding_status',
        'processed_at',
        'metadata',
    ];

    /**
     * Casts de atributos
     */
    protected $casts = [
        'processed_at' => 'datetime',
        'metadata' => 'array',
        'file_size' => 'integer',
        'chunk_count' => 'integer',
        'token_count' => 'integer',
    ];

    /**
     * Knowledge base a la que pertenece
     */
    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }

    /**
     * Chunks del documento (para búsqueda vectorial)
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }

    /**
     * Scope: Documentos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('embedding_status', 'completed');
    }

    /**
     * Scope: Documentos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('embedding_status', 'pending');
    }

    /**
     * Scope: Documentos en procesamiento
     */
    public function scopeProcessing($query)
    {
        return $query->where('embedding_status', 'processing');
    }

    /**
     * Scope: Documentos fallidos
     */
    public function scopeFailed($query)
    {
        return $query->where('embedding_status', 'failed');
    }

    /**
     * Scope: Por tipo de fuente
     */
    public function scopeBySourceType($query, string $type)
    {
        return $query->where('source_type', $type);
    }

    /**
     * Verificar si está completado
     */
    public function isCompleted(): bool
    {
        return $this->embedding_status === 'completed';
    }

    /**
     * Verificar si está pendiente
     */
    public function isPending(): bool
    {
        return $this->embedding_status === 'pending';
    }

    /**
     * Verificar si está en procesamiento
     */
    public function isProcessing(): bool
    {
        return $this->embedding_status === 'processing';
    }

    /**
     * Verificar si falló
     */
    public function isFailed(): bool
    {
        return $this->embedding_status === 'failed';
    }

    /**
     * Marcar como procesando
     */
    public function markAsProcessing(): void
    {
        $this->update(['embedding_status' => 'processing']);
    }

    /**
     * Marcar como completado
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'embedding_status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Marcar como fallido
     */
    public function markAsFailed(): void
    {
        $this->update(['embedding_status' => 'failed']);
    }

    /**
     * Incrementar contador de chunks
     */
    public function incrementChunkCount(): void
    {
        $this->increment('chunk_count');
    }

    /**
     * Actualizar contador de tokens
     */
    public function updateTokenCount(int $tokens): void
    {
        $this->update(['token_count' => $tokens]);
    }

    /**
     * Obtener tamaño en formato legible
     */
    public function getReadableFileSize(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}