<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLA: users
     * 
     * IMPORTANTE: tenant_id puede ser NULL para super_admin.
     * Super admins son usuarios globales de la plataforma sin tenant asignado.
     * 
     * ROLES (manejados por Spatie Permission):
     * - super_admin: Sin tenant, acceso total
     * - admin: Con tenant, gestión completa de su tenant
     * - supervisor: Con tenant, solo lectura de su tenant
     * - agent: Con tenant, solo bots asignados
     * - viewer: Con tenant, solo lectura de bots asignados
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Relación con tenant (nullable para super_admin)
            $table->foreignId('tenant_id')
                ->nullable()
                ->constrained('tenants')
                ->onDelete('cascade');
            
            // Información básica
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Información adicional
            $table->string('phone', 20)->nullable();
            $table->string('avatar_url', 500)->nullable();
            
            // Role (DEPRECATED - usar Spatie Permission en su lugar)
            // Mantenido por compatibilidad temporal
            $table->enum('role', ['super_admin', 'admin', 'supervisor', 'agent', 'viewer'])
                ->default('agent');
            
            // Estado del usuario
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            
            // Preferencias del usuario (JSON)
            // Ejemplo: {"theme": "dark", "language": "es", "notifications": true}
            $table->json('preferences')->nullable();
            
            // Laravel defaults
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar queries comunes
            $table->index('tenant_id');      // Filtrar por tenant
            $table->index('role');           // Filtrar por rol
            $table->index('is_active');      // Filtrar activos/inactivos
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};