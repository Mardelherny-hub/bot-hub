<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * BotFactory
 * 
 * Genera bots de prueba con configuraciones variadas.
 * 
 * USO:
 * Bot::factory()->create(); // Bot con valores aleatorios
 * Bot::factory()->active()->create(); // Bot activo
 * Bot::factory()->withKnowledgeBase()->create(); // Bot con KB habilitada
 */
class BotFactory extends Factory
{
    /**
     * Estado por defecto
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->company() . ' Bot',
            'description' => fake()->sentence(),
            'phone_number' => '+549' . fake()->numerify('##########'),
            'whatsapp_business_account_id' => 'waba_' . fake()->uuid(),
            'whatsapp_phone_number_id' => 'phone_' . fake()->uuid(),
            'ai_model' => fake()->randomElement(['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo']),
            'personality' => fake()->randomElement([
                'Eres un asistente amigable y profesional.',
                'Eres un experto en ventas entusiasta.',
                'Eres un agente de soporte técnico paciente.',
                'Eres un consultor de negocios experimentado.',
            ]),
            'instructions' => 'Responde de manera clara y concisa. Si no sabes algo, admítelo.',
            'max_tokens' => fake()->randomElement([300, 500, 800, 1000]),
            'temperature' => fake()->randomFloat(2, 0.5, 1.0),
            'language' => fake()->randomElement(['es', 'en', 'pt']),
            'is_active' => true,
            'fallback_to_human' => true,
            'inactivity_timeout_minutes' => fake()->randomElement([15, 30, 60]),
            'business_hours_start' => '09:00:00',
            'business_hours_end' => '18:00:00',
            'business_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'out_of_hours_message' => 'Gracias por contactarnos. Nuestro horario de atención es de lunes a viernes de 9 a 18hs.',
            'use_knowledge_base' => false,
            'knowledge_base_results' => 3,
            'knowledge_base_threshold' => 0.70,
            'metadata' => null,
        ];
    }

    /**
     * Estado: Bot activo
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Estado: Bot inactivo
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Estado: Bot con knowledge base habilitada
     */
    public function withKnowledgeBase(): static
    {
        return $this->state(fn (array $attributes) => [
            'use_knowledge_base' => true,
            'knowledge_base_results' => fake()->numberBetween(3, 10),
            'knowledge_base_threshold' => fake()->randomFloat(2, 0.6, 0.9),
        ]);
    }

    /**
     * Estado: Bot sin fallback a humano
     */
    public function noFallback(): static
    {
        return $this->state(fn (array $attributes) => [
            'fallback_to_human' => false,
        ]);
    }

    /**
     * Estado: Bot 24/7
     */
    public function alwaysAvailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'business_hours_start' => '00:00:00',
            'business_hours_end' => '23:59:59',
            'business_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
        ]);
    }

    /**
     * Estado: Bot con configuración GPT-4
     */
    public function withGPT4(): static
    {
        return $this->state(fn (array $attributes) => [
            'ai_model' => 'gpt-4',
            'max_tokens' => 800,
            'temperature' => 0.7,
        ]);
    }

    /**
     * Estado: Bot con configuración económica (GPT-3.5)
     */
    public function economic(): static
    {
        return $this->state(fn (array $attributes) => [
            'ai_model' => 'gpt-3.5-turbo',
            'max_tokens' => 300,
            'temperature' => 0.5,
        ]);
    }

    /**
     * Estado: Bot en español
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
        ]);
    }

    /**
     * Estado: Bot en inglés
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
        ]);
    }

    /**
     * Estado: Bot en portugués
     */
    public function portuguese(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'pt',
        ]);
    }
}