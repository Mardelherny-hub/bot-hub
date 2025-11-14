<?php

namespace Database\Factories;

use App\Models\Bot;
use App\Models\BotUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para BotUser
 * 
 * Genera asignaciones de usuarios a bots con permisos aleatorios.
 * Útil para testing y seeders.
 * 
 * USO:
 * BotUser::factory()->create(); // Permisos aleatorios
 * BotUser::factory()->fullAccess()->create(); // Todos los permisos
 * BotUser::factory()->chatOnly()->create(); // Solo chat
 */
class BotUserFactory extends Factory
{
    protected $model = BotUser::class;

    /**
     * Estado por defecto: permisos aleatorios
     */
    public function definition(): array
    {
        return [
            'bot_id' => Bot::factory(),
            'user_id' => User::factory(),
            'can_manage' => fake()->boolean(30), // 30% probabilidad
            'can_view_analytics' => fake()->boolean(50),
            'can_chat' => fake()->boolean(70), // Más común
            'can_train_kb' => fake()->boolean(20),
            'can_delete_data' => fake()->boolean(10), // Menos común
            'assigned_at' => now(),
        ];
    }

    /**
     * Estado: Acceso completo (admin del bot)
     */
    public function fullAccess(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_manage' => true,
            'can_view_analytics' => true,
            'can_chat' => true,
            'can_train_kb' => true,
            'can_delete_data' => true,
        ]);
    }

    /**
     * Estado: Solo chat (agente típico)
     */
    public function chatOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_manage' => false,
            'can_view_analytics' => false,
            'can_chat' => true,
            'can_train_kb' => false,
            'can_delete_data' => false,
        ]);
    }

    /**
     * Estado: Solo lectura (viewer)
     */
    public function readOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_manage' => false,
            'can_view_analytics' => true,
            'can_chat' => false,
            'can_train_kb' => false,
            'can_delete_data' => false,
        ]);
    }

    /**
     * Estado: Supervisor (ve y chatea, no modifica)
     */
    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_manage' => false,
            'can_view_analytics' => true,
            'can_chat' => true,
            'can_train_kb' => false,
            'can_delete_data' => false,
        ]);
    }

    /**
     * Estado: Sin permisos (bloqueado)
     */
    public function noPermissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_manage' => false,
            'can_view_analytics' => false,
            'can_chat' => false,
            'can_train_kb' => false,
            'can_delete_data' => false,
        ]);
    }
}