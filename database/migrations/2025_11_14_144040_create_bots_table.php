<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLA: bots
     * 
     * Configuración de bots de WhatsApp con IA.
     * Cada bot pertenece a un tenant y puede tener múltiples usuarios asignados.
     */
    public function up(): void
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->id();
            
            // Relación con tenant
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Información básica
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('phone_number', 20)->unique();
            $table->string('whatsapp_business_account_id')->nullable();
            $table->string('whatsapp_phone_number_id')->nullable();
            
            // Configuración de IA
            $table->string('ai_model', 50)->default('gpt-4');
            $table->text('personality')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('max_tokens')->default(500);
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->string('language', 10)->default('es');
            
            // Configuración de comportamiento
            $table->boolean('is_active')->default(true);
            $table->boolean('fallback_to_human')->default(true);
            $table->integer('inactivity_timeout_minutes')->default(30);
            $table->time('business_hours_start')->default('09:00:00');
            $table->time('business_hours_end')->default('18:00:00');
            $table->json('business_days')->nullable(); // ["monday", "tuesday", ...]
            $table->text('out_of_hours_message')->nullable();
            
            // Configuración de RAG
            $table->boolean('use_knowledge_base')->default(false);
            $table->integer('knowledge_base_results')->default(3);
            $table->decimal('knowledge_base_threshold', 3, 2)->default(0.70);
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // Timestamps y soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('tenant_id');
            $table->index('is_active');
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
};