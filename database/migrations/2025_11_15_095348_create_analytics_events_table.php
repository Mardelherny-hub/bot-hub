<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create analytics_events table
 * 
 * Almacena eventos de analytics del sistema para métricas y reportes.
 * Registra todas las interacciones importantes: mensajes, conversaciones,
 * uso de IA, tiempos de respuesta, etc.
 * 
 * Dependencias:
 * - tenants: Tenant al que pertenece el evento
 * - bots: Bot relacionado (opcional)
 * - conversations: Conversación relacionada (opcional)
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
        Schema::create('analytics_events', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id()->comment('ID único del evento');
            
            // FOREIGN KEYS
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade')
                ->comment('Tenant al que pertenece el evento');
            
            $table->foreignId('bot_id')
                ->nullable()
                ->constrained('bots')
                ->onDelete('cascade')
                ->comment('Bot relacionado con el evento');
            
            $table->foreignId('conversation_id')
                ->nullable()
                ->constrained('conversations')
                ->onDelete('set null')
                ->comment('Conversación relacionada (si aplica)');
            
            // CORE FIELDS
            $table->string('event_type', 100)
                ->comment('Tipo de evento (message.sent, conversation.started, etc)');
            
            $table->string('event_category', 50)
                ->comment('Categoría del evento (message, conversation, ai, etc)');
            
            $table->json('event_data')
                ->nullable()
                ->comment('Datos adicionales del evento en formato JSON');
            
            // METRICS
            $table->integer('response_time_ms')
                ->nullable()
                ->comment('Tiempo de respuesta en milisegundos');
            
            $table->integer('tokens_used')
                ->nullable()
                ->comment('Tokens de IA consumidos (si aplica)');
            
            $table->decimal('cost_usd', 10, 6)
                ->nullable()
                ->comment('Costo del evento en USD (IA, WhatsApp, etc)');
            
            // STATUS
            $table->boolean('success')
                ->default(true)
                ->comment('¿Evento exitoso?');
            
            $table->string('error_message', 500)
                ->nullable()
                ->comment('Mensaje de error (si falló)');
            
            // METADATA
            $table->string('ip_address', 45)
                ->nullable()
                ->comment('IP de origen del evento');
            
            $table->string('user_agent', 500)
                ->nullable()
                ->comment('User agent del cliente');
            
            // TIMESTAMP
            $table->timestamp('created_at')
                ->useCurrent()
                ->comment('Fecha y hora del evento');
            
            // INDEXES
            $table->index('tenant_id', 'idx_analytics_tenant_id');
            $table->index('bot_id', 'idx_analytics_bot_id');
            $table->index('conversation_id', 'idx_analytics_conversation_id');
            $table->index('event_type', 'idx_analytics_event_type');
            $table->index('event_category', 'idx_analytics_event_category');
            $table->index('created_at', 'idx_analytics_created_at');
            $table->index('success', 'idx_analytics_success');
            $table->index(['tenant_id', 'created_at'], 'idx_analytics_tenant_date');
            $table->index(['bot_id', 'event_type'], 'idx_analytics_bot_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};