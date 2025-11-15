<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLA: knowledge_documents
     * 
     * Documentos individuales dentro de una knowledge base.
     * Pueden ser subidos (PDF, DOCX, TXT) o agregados manualmente.
     */
    public function up(): void
    {
        Schema::create('knowledge_documents', function (Blueprint $table) {
            $table->id();
            
            // Relación con knowledge base
            $table->foreignId('knowledge_base_id')->constrained()->onDelete('cascade');
            
            // Información del documento
            $table->string('title');
            $table->longText('content');
            
            // Fuente del documento
            $table->enum('source_type', ['upload', 'url', 'manual', 'api']);
            $table->string('source_url', 500)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->integer('file_size')->nullable();
            $table->string('file_type', 50)->nullable();
            
            // Métricas de procesamiento
            $table->integer('chunk_count')->default(0);
            $table->integer('token_count')->default(0);
            
            // Estado del embedding
            $table->enum('embedding_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            
            // Metadata adicional
            $table->json('metadata')->nullable();
            
            // Timestamps y soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('knowledge_base_id');
            $table->index('source_type');
            $table->index('embedding_status');
            $table->index(['knowledge_base_id', 'embedding_status']); // Índice compuesto
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_documents');
    }
};