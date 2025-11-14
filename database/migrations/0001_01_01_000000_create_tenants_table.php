<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            
            // Información básica
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('website')->nullable();
            $table->string('logo_url', 500)->nullable();
            
            // Suscripción
            $table->enum('subscription_plan', ['starter', 'professional', 'enterprise'])->default('starter');
            $table->enum('subscription_status', ['active', 'suspended', 'cancelled', 'trial'])->default('active');
            $table->timestamp('subscription_started_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            
            // Límites
            $table->integer('monthly_conversation_limit')->default(1000);
            $table->integer('monthly_bot_limit')->default(3);
            $table->integer('monthly_user_limit')->default(1);
            
            // Características
            $table->boolean('is_white_label')->default(false);
            
            // Configuración adicional (JSON)
            $table->json('settings')->nullable();
            
            // Timestamps y soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};