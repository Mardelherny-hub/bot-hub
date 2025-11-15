<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLA: conversations
     * 
     * Hilos de conversación entre usuarios finales y bots.
     * Cada conversación pertenece a un bot y puede ser asignada a un agente humano.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            
            // Relación con bot
            $table->foreignId('bot_id')->constrained()->onDelete('cascade');
            
            // Usuario externo (cliente final)
            $table->string('external_user_id'); // Número de teléfono u otro ID
            $table->string('external_user_name')->nullable();
            
            // Canal de comunicación
            $table->string('channel', 50)->default('whatsapp');
            
            // Estado de la conversación
            $table->enum('status', ['active', 'waiting_human', 'with_human', 'resolved', 'closed'])->default('active');
            
            // Handoff a humano
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('handoff_reason')->nullable();
            $table->timestamp('handoff_at')->nullable();
            
            // Métricas
            $table->timestamp('last_message_at')->nullable();
            $table->integer('message_count')->default(0);
            $table->integer('first_response_time_ms')->nullable();
            $table->decimal('sentiment_score', 3, 2)->nullable(); // -1.00 a 1.00
            $table->tinyInteger('satisfaction_rating')->nullable(); // 1-5
            
            // Tags y metadata
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->timestamp('closed_at')->nullable();
            
            // Índices
            $table->index('bot_id');
            $table->index('external_user_id');
            $table->index('status');
            $table->index('assigned_user_id');
            $table->index('last_message_at');
            $table->index(['bot_id', 'status']); // Índice compuesto
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};