<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'subscription_plan' => fake()->randomElement(['starter', 'professional', 'enterprise']),
            'subscription_status' => 'active',
            'subscription_started_at' => now(),
            'subscription_ends_at' => now()->addYear(),
            'monthly_conversation_limit' => 1000,
            'monthly_bot_limit' => 3,
            'monthly_user_limit' => 1,
            'is_white_label' => false,
            'settings' => null,
        ];
    }
}