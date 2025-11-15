<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create notifications table
 * 
 * Almacena notificaciones del sistema para usuarios.
 * Notifica eventos importantes: mensajes nuevos, asignaciones,
 * límites de uso, errores críticos, etc.
 * 
 * Dependencias:
 * - users: Usuario destinatario de la notificación
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
        Schema::create('notifications', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id()->comment('ID único de la notificación');
            
            // FOREIGN KEYS
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Usuario destinatario de la notificación');
            
            // CORE FIELDS
            $table->string('type', 100)
                ->comment('Tipo de notificación (message.new, conversation.assigned, etc)');
            
            $table->string('title', 255)
                ->comment('Título de la notificación');
            
            $table->text('message')
                ->comment('Mensaje descriptivo de la notificación');
            
            $table->string('action_url', 500)
                ->nullable()
                ->comment('URL de acción (redirección al hacer click)');
            
            // READ STATUS
            $table->boolean('is_read')
                ->default(false)
                ->comment('¿Notificación leída?');
            
            $table->timestamp('read_at')
                ->nullable()
                ->comment('Momento en que fue leída');
            
            // PRIORITY
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])
                ->default('normal')
                ->comment('Prioridad de la notificación');
            
            // METADATA
            $table->json('metadata')
                ->nullable()
                ->comment('Metadata adicional (IDs relacionados, datos extra)');
            
            // TIMESTAMP
            $table->timestamp('created_at')
                ->useCurrent()
                ->comment('Fecha de creación de la notificación');
            
            // INDEXES
            $table->index('user_id', 'idx_notifications_user_id');
            $table->index('is_read', 'idx_notifications_is_read');
            $table->index(['user_id', 'is_read'], 'idx_notifications_user_read');
            $table->index('created_at', 'idx_notifications_created_at');
            $table->index('priority', 'idx_notifications_priority');
            $table->index(['user_id', 'created_at'], 'idx_notifications_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};