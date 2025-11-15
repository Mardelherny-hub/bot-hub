<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLA: knowledge_bases
     * 
     * Base de conocimiento por bot para RAG (Retrieval Augmented Generation).
     * Cada bot tiene una knowledge base que contiene documentos procesados.
     */
    public function up(): void
    {
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();
            
            // Relación con bot (1:1)
            $table->foreignId('bot_id')->unique()->constrained()->onDelete('cascade');
            
            // Información básica
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Métricas
            $table->integer('document_count')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->timestamp('last_trained_at')->nullable();
            
            // Configuración de embeddings
            $table->string('embedding_model', 50)->default('text-embedding-ada-002');
            
            // Configuración adicional
            $table->json('settings')->nullable();
            
            // Timestamps y soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('bot_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};