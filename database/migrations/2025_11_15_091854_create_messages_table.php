<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLA: messages
     * 
     * Mensajes individuales dentro de conversaciones.
     * Pueden ser enviados por el usuario final, el bot IA, o un agente humano.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            
            // Relación con conversación
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            
            // Dirección y remitente
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('sender_type', ['user', 'bot', 'agent']);
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Contenido del mensaje
            $table->text('content');
            $table->string('content_type', 50)->default('text');
            
            // Mensajes interactivos (botones, listas)
            $table->string('interactive_type', 50)->nullable();
            $table->json('interactive_payload')->nullable();
            
            // Media adjunta
            $table->string('media_url', 500)->nullable();
            $table->string('media_mime_type', 100)->nullable();
            
            // ID externo de WhatsApp
            $table->string('external_message_id')->nullable();
            
            // Estado del mensaje
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->text('error_message')->nullable();
            
            // Metadata de IA
            $table->boolean('ai_generated')->default(false);
            $table->string('ai_model_used', 50)->nullable();
            $table->integer('ai_tokens_used')->nullable();
            $table->integer('processing_time_ms')->nullable();
            
            // Metadata adicional
            $table->json('metadata')->nullable();
            
            // Timestamps (solo created_at)
            $table->timestamp('created_at')->useCurrent();
            
            // Índices
            $table->index('conversation_id');
            $table->index('direction');
            $table->index('sender_type');
            $table->index('status');
            $table->index('created_at');
            $table->index(['conversation_id', 'created_at']); // Índice compuesto
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};