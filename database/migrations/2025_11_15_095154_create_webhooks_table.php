<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create webhooks table
 * 
 * Almacena configuración de webhooks para notificar eventos externos.
 * Permite a los bots enviar notificaciones a sistemas de terceros
 * cuando ocurren eventos específicos (nuevo mensaje, conversación cerrada, etc).
 * 
 * Dependencias:
 * - bots: Bot propietario del webhook
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
        Schema::create('webhooks', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id()->comment('ID único del webhook');
            
            // FOREIGN KEYS
            $table->foreignId('bot_id')
                ->constrained('bots')
                ->onDelete('cascade')
                ->comment('Bot al que pertenece el webhook');
            
            // CORE FIELDS
            $table->string('name', 255)
                ->comment('Nombre descriptivo del webhook');
            
            $table->string('url', 500)
                ->comment('URL de destino del webhook');
            
            $table->enum('method', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])
                ->default('POST')
                ->comment('Método HTTP del webhook');
            
            $table->json('headers')
                ->nullable()
                ->comment('Headers HTTP adicionales (ej: Authorization)');
            
            $table->json('events')
                ->comment('Array de eventos que disparan el webhook');
            
            $table->boolean('is_active')
                ->default(true)
                ->comment('¿Webhook activo?');
            
            // RETRY & RELIABILITY
            $table->integer('max_retries')
                ->default(3)
                ->comment('Intentos máximos en caso de fallo');
            
            $table->integer('timeout_seconds')
                ->default(30)
                ->comment('Timeout de la petición HTTP en segundos');
            
            // STATS & MONITORING
            $table->integer('success_count')
                ->default(0)
                ->comment('Cantidad de envíos exitosos');
            
            $table->integer('failure_count')
                ->default(0)
                ->comment('Cantidad de envíos fallidos');
            
            $table->timestamp('last_triggered_at')
                ->nullable()
                ->comment('Última vez que se disparó');
            
            $table->timestamp('last_success_at')
                ->nullable()
                ->comment('Último envío exitoso');
            
            $table->timestamp('last_failure_at')
                ->nullable()
                ->comment('Último envío fallido');
            
            $table->text('last_error')
                ->nullable()
                ->comment('Último mensaje de error');
            
            // SECURITY
            $table->string('secret', 100)
                ->nullable()
                ->comment('Secret para firmar el payload (HMAC)');
            
            // TIMESTAMPS
            $table->timestamp('created_at')
                ->useCurrent()
                ->comment('Fecha de creación');
            
            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('Última actualización');
            
            $table->timestamp('deleted_at')
                ->nullable()
                ->comment('Soft delete');
            
            // INDEXES
            $table->index('bot_id', 'idx_webhooks_bot_id');
            $table->index('is_active', 'idx_webhooks_is_active');
            $table->index(['bot_id', 'is_active'], 'idx_webhooks_bot_active');
            $table->index('last_triggered_at', 'idx_webhooks_last_triggered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};