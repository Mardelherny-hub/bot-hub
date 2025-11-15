<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create tenant_usage_reports table
 * 
 * Almacena reportes mensuales de uso por tenant para billing y analytics.
 * Consolida métricas de consumo: conversaciones, mensajes, tokens de IA,
 * costos de APIs externas, storage, etc.
 * 
 * Dependencias:
 * - tenants: Tenant al que pertenece el reporte
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
        Schema::create('tenant_usage_reports', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id()->comment('ID único del reporte');
            
            // FOREIGN KEYS
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade')
                ->comment('Tenant al que pertenece el reporte');
            
            // PERIOD
            $table->string('period', 7)
                ->comment('Período del reporte (formato: YYYY-MM)');
            
            // USAGE METRICS
            $table->integer('conversations_used')
                ->default(0)
                ->comment('Cantidad de conversaciones en el período');
            
            $table->integer('messages_sent')
                ->default(0)
                ->comment('Mensajes enviados');
            
            $table->integer('messages_received')
                ->default(0)
                ->comment('Mensajes recibidos');
            
            $table->bigInteger('tokens_used')
                ->default(0)
                ->comment('Tokens de IA consumidos');
            
            $table->integer('bots_active')
                ->default(0)
                ->comment('Bots activos en el período');
            
            $table->integer('users_active')
                ->default(0)
                ->comment('Usuarios activos en el período');
            
            $table->decimal('storage_mb_used', 10, 2)
                ->default(0)
                ->comment('Storage usado en MB');
            
            // COST TRACKING
            $table->decimal('whatsapp_cost_usd', 10, 4)
                ->default(0)
                ->comment('Costo WhatsApp API en USD');
            
            $table->decimal('openai_cost_usd', 10, 4)
                ->default(0)
                ->comment('Costo OpenAI API en USD');
            
            $table->decimal('total_cost_usd', 10, 4)
                ->default(0)
                ->comment('Costo total del período en USD');
            
            // BILLING STATUS
            $table->enum('billing_status', ['pending', 'calculated', 'billed', 'paid', 'overdue'])
                ->default('pending')
                ->comment('Estado de facturación del período');
            
            $table->timestamp('billed_at')
                ->nullable()
                ->comment('Fecha en que se facturó');
            
            // METADATA
            $table->json('metadata')
                ->nullable()
                ->comment('Metadata adicional (breakdown por bot, detalles, etc)');
            
            // TIMESTAMPS
            $table->timestamp('created_at')
                ->useCurrent()
                ->comment('Fecha de creación del reporte');
            
            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('Última actualización del reporte');
            
            // INDEXES
            $table->index('tenant_id', 'idx_tenant_usage_tenant_id');
            $table->index('period', 'idx_tenant_usage_period');
            $table->index('billing_status', 'idx_tenant_usage_billing_status');
            $table->index(['tenant_id', 'period'], 'idx_tenant_usage_tenant_period');
            
            // UNIQUE CONSTRAINT
            $table->unique(['tenant_id', 'period'], 'idx_tenant_usage_unique_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_usage_reports');
    }
};