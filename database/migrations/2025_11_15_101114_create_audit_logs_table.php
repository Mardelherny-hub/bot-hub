<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create audit_logs table
 * 
 * Almacena logs de auditoría del sistema para compliance y seguridad.
 * Registra todas las acciones importantes realizadas por usuarios:
 * creación, modificación, eliminación de registros.
 * 
 * Dependencias:
 * - tenants: Tenant al que pertenece el log (opcional)
 * - users: Usuario que realizó la acción (opcional)
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
        Schema::create('audit_logs', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id()->comment('ID único del log');
            
            // FOREIGN KEYS
            $table->foreignId('tenant_id')
                ->nullable()
                ->constrained('tenants')
                ->onDelete('cascade')
                ->comment('Tenant relacionado con la acción (si aplica)');
            
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Usuario que realizó la acción (NULL si fue el sistema)');
            
            // ACTION DETAILS
            $table->string('action', 100)
                ->comment('Acción realizada (created, updated, deleted, etc)');
            
            $table->string('entity_type', 100)
                ->comment('Tipo de entidad afectada (Bot, Conversation, User, etc)');
            
            $table->unsignedBigInteger('entity_id')
                ->nullable()
                ->comment('ID de la entidad afectada');
            
            // CHANGES TRACKING
            $table->json('old_values')
                ->nullable()
                ->comment('Valores anteriores (antes del cambio)');
            
            $table->json('new_values')
                ->nullable()
                ->comment('Valores nuevos (después del cambio)');
            
            // REQUEST METADATA
            $table->string('ip_address', 45)
                ->nullable()
                ->comment('IP desde donde se realizó la acción');
            
            $table->string('user_agent', 500)
                ->nullable()
                ->comment('User agent del navegador/cliente');
            
            // TIMESTAMP
            $table->timestamp('created_at')
                ->useCurrent()
                ->comment('Fecha y hora de la acción');
            
            // INDEXES
            $table->index('tenant_id', 'idx_audit_logs_tenant_id');
            $table->index('user_id', 'idx_audit_logs_user_id');
            $table->index('action', 'idx_audit_logs_action');
            $table->index('entity_type', 'idx_audit_logs_entity_type');
            $table->index(['entity_type', 'entity_id'], 'idx_audit_logs_entity');
            $table->index('created_at', 'idx_audit_logs_created_at');
            $table->index(['tenant_id', 'created_at'], 'idx_audit_logs_tenant_date');
            $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};