<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create api_keys table
 * 
 * Almacena API keys para integraciones externas.
 * Permite a los tenants crear keys para acceder a la API de BotHub
 * desde sus propias aplicaciones.
 * 
 * Dependencias:
 * - tenants: Tenant propietario de la API key
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
        Schema::create('api_keys', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id()->comment('ID único de la API key');
            
            // FOREIGN KEYS
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade')
                ->comment('Tenant propietario de la API key');
            
            // CORE FIELDS
            $table->string('name', 255)
                ->comment('Nombre descriptivo de la API key');
            
            $table->string('key', 100)
                ->unique()
                ->comment('API key hasheada (no almacenar en texto plano)');
            
            $table->string('key_preview', 20)
                ->comment('Primeros caracteres de la key (para mostrar en UI)');
            
            // PERMISSIONS
            $table->json('permissions')
                ->nullable()
                ->comment('Permisos específicos de la key (recursos y acciones)');
            
            // STATUS
            $table->boolean('is_active')
                ->default(true)
                ->comment('¿API key activa?');
            
            // USAGE TRACKING
            $table->timestamp('last_used_at')
                ->nullable()
                ->comment('Última vez que se usó la key');
            
            $table->integer('usage_count')
                ->default(0)
                ->comment('Cantidad total de usos');
            
            // RATE LIMITING
            $table->integer('rate_limit_per_minute')
                ->nullable()
                ->comment('Límite de requests por minuto (NULL = sin límite)');
            
            // EXPIRATION
            $table->timestamp('expires_at')
                ->nullable()
                ->comment('Fecha de expiración (NULL = nunca expira)');
            
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
            $table->index('tenant_id', 'idx_api_keys_tenant_id');
            $table->index('is_active', 'idx_api_keys_is_active');
            $table->index(['tenant_id', 'is_active'], 'idx_api_keys_tenant_active');
            $table->index('expires_at', 'idx_api_keys_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};