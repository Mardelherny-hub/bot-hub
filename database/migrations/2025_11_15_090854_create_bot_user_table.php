<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLA: bot_user (PIVOT)
     * 
     * Relación N:N entre Bot y User con permisos granulares.
     * Permite asignar usuarios a bots con permisos específicos.
     */
    public function up(): void
    {
        Schema::create('bot_user', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('bot_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Permisos granulares
            $table->boolean('can_manage')->default(false);
            $table->boolean('can_view_analytics')->default(false);
            $table->boolean('can_chat')->default(false);
            $table->boolean('can_train_kb')->default(false);
            $table->boolean('can_delete_data')->default(false);
            
            // Metadata
            $table->timestamp('assigned_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->unique(['bot_id', 'user_id']);
            $table->index('bot_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_user');
    }
};