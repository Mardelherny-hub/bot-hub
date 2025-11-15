<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create document_chunks table
 * 
 * Almacena fragmentos (chunks) de documentos procesados para RAG.
 * Cada chunk contiene un fragmento de texto con su embedding vectorial
 * generado por OpenAI para búsqueda semántica.
 * 
 * Dependencias:
 * - knowledge_documents: Documento padre del chunk
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_chunks', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id()->comment('ID único del chunk');
            
            // FOREIGN KEYS
            $table->foreignId('knowledge_document_id')
                ->constrained('knowledge_documents')
                ->onDelete('cascade')
                ->comment('Documento al que pertenece el chunk');
            
            // CORE FIELDS
            $table->integer('chunk_index')
                ->comment('Índice del chunk dentro del documento (0, 1, 2...)');
            
            $table->text('content')
                ->comment('Contenido textual del chunk');
            
            $table->integer('token_count')
                ->default(0)
                ->comment('Cantidad de tokens del chunk');
            
            // EMBEDDING VECTOR
            $table->json('embedding')
                ->nullable()
                ->comment('Vector de embeddings (1536 dims para text-embedding-ada-002)');
            
            // METADATA
            $table->json('metadata')
                ->nullable()
                ->comment('Metadata adicional del chunk (página, sección, etc)');
            
            // TIMESTAMPS
            $table->timestamp('created_at')
                ->useCurrent()
                ->comment('Fecha de creación del chunk');
            
            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('Última actualización del chunk');
            
            // INDEXES
            $table->index('knowledge_document_id', 'idx_chunks_document_id');
            $table->index('chunk_index', 'idx_chunks_chunk_index');
            $table->index(['knowledge_document_id', 'chunk_index'], 'idx_chunks_document_index');
            
            // UNIQUE CONSTRAINT
            $table->unique(['knowledge_document_id', 'chunk_index'], 'idx_chunks_unique_document_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
    }
};