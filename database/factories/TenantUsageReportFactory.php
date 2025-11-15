<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantUsageReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TenantUsageReport Factory
 * 
 * Factory para generar reportes de uso de prueba.
 * 
 * Estados disponibles:
 * - pending: Estado pendiente
 * - calculated: Estado calculado
 * - billed: Estado facturado
 * - paid: Estado pagado
 * - overdue: Estado vencido
 * - currentMonth: Reporte del mes actual
 * - lastMonth: Reporte del mes pasado
 * - highUsage: Alto consumo
 * - lowUsage: Bajo consumo
 * - withCosts: Con costos calculados
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantUsageReport>
 */
class TenantUsageReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TenantUsageReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $conversationsUsed = fake()->numberBetween(10, 500);
        $messagesSent = fake()->numberBetween(100, 5000);
        $messagesReceived = fake()->numberBetween(100, 5000);
        $tokensUsed = fake()->numberBetween(1000, 100000);
        $whatsappCost = $conversationsUsed * 0.005; // $0.005 por conversación
        $openaiCost = $tokensUsed * 0.00002; // $0.00002 por token
        
        return [
            'tenant_id' => Tenant::factory(),
            'period' => now()->subMonths(fake()->numberBetween(0, 12))->format('Y-m'),
            'conversations_used' => $conversationsUsed,
            'messages_sent' => $messagesSent,
            'messages_received' => $messagesReceived,
            'tokens_used' => $tokensUsed,
            'bots_active' => fake()->numberBetween(1, 10),
            'users_active' => fake()->numberBetween(1, 20),
            'storage_mb_used' => fake()->randomFloat(2, 10, 5000),
            'whatsapp_cost_usd' => round($whatsappCost, 4),
            'openai_cost_usd' => round($openaiCost, 4),
            'total_cost_usd' => round($whatsappCost + $openaiCost, 4),
            'billing_status' => fake()->randomElement(['pending', 'calculated', 'billed', 'paid']),
            'billed_at' => null,
            'metadata' => [
                'plan' => fake()->randomElement(['free', 'starter', 'pro', 'enterprise']),
            ],
        ];
    }

    /**
     * Estado: Reporte pendiente.
     * 
     * @return static
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_status' => 'pending',
            'billed_at' => null,
        ]);
    }

    /**
     * Estado: Reporte calculado.
     * 
     * @return static
     */
    public function calculated(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_status' => 'calculated',
            'billed_at' => null,
        ]);
    }

    /**
     * Estado: Reporte facturado.
     * 
     * @return static
     */
    public function billed(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_status' => 'billed',
            'billed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Estado: Reporte pagado.
     * 
     * @return static
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_status' => 'paid',
            'billed_at' => fake()->dateTimeBetween('-60 days', '-30 days'),
        ]);
    }

    /**
     * Estado: Reporte vencido.
     * 
     * @return static
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_status' => 'overdue',
            'billed_at' => fake()->dateTimeBetween('-90 days', '-60 days'),
        ]);
    }

    /**
     * Estado: Reporte del mes actual.
     * 
     * @return static
     */
    public function currentMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => now()->format('Y-m'),
        ]);
    }

    /**
     * Estado: Reporte del mes pasado.
     * 
     * @return static
     */
    public function lastMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => now()->subMonth()->format('Y-m'),
        ]);
    }

    /**
     * Estado: Reporte de un período específico.
     * 
     * @param string $period Formato: YYYY-MM
     * @return static
     */
    public function forPeriod(string $period): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => $period,
        ]);
    }

    /**
     * Estado: Alto consumo.
     * 
     * @return static
     */
    public function highUsage(): static
    {
        $conversationsUsed = fake()->numberBetween(1000, 5000);
        $messagesSent = fake()->numberBetween(10000, 50000);
        $messagesReceived = fake()->numberBetween(10000, 50000);
        $tokensUsed = fake()->numberBetween(500000, 2000000);
        $whatsappCost = $conversationsUsed * 0.005;
        $openaiCost = $tokensUsed * 0.00002;
        
        return $this->state(fn (array $attributes) => [
            'conversations_used' => $conversationsUsed,
            'messages_sent' => $messagesSent,
            'messages_received' => $messagesReceived,
            'tokens_used' => $tokensUsed,
            'bots_active' => fake()->numberBetween(10, 50),
            'users_active' => fake()->numberBetween(20, 100),
            'storage_mb_used' => fake()->randomFloat(2, 5000, 50000),
            'whatsapp_cost_usd' => round($whatsappCost, 4),
            'openai_cost_usd' => round($openaiCost, 4),
            'total_cost_usd' => round($whatsappCost + $openaiCost, 4),
        ]);
    }

    /**
     * Estado: Bajo consumo.
     * 
     * @return static
     */
    public function lowUsage(): static
    {
        $conversationsUsed = fake()->numberBetween(1, 50);
        $messagesSent = fake()->numberBetween(10, 500);
        $messagesReceived = fake()->numberBetween(10, 500);
        $tokensUsed = fake()->numberBetween(100, 5000);
        $whatsappCost = $conversationsUsed * 0.005;
        $openaiCost = $tokensUsed * 0.00002;
        
        return $this->state(fn (array $attributes) => [
            'conversations_used' => $conversationsUsed,
            'messages_sent' => $messagesSent,
            'messages_received' => $messagesReceived,
            'tokens_used' => $tokensUsed,
            'bots_active' => fake()->numberBetween(1, 3),
            'users_active' => fake()->numberBetween(1, 5),
            'storage_mb_used' => fake()->randomFloat(2, 1, 100),
            'whatsapp_cost_usd' => round($whatsappCost, 4),
            'openai_cost_usd' => round($openaiCost, 4),
            'total_cost_usd' => round($whatsappCost + $openaiCost, 4),
        ]);
    }

    /**
     * Estado: Sin costos.
     * 
     * @return static
     */
    public function noCosts(): static
    {
        return $this->state(fn (array $attributes) => [
            'whatsapp_cost_usd' => 0,
            'openai_cost_usd' => 0,
            'total_cost_usd' => 0,
        ]);
    }

    /**
     * Estado: Año específico.
     * 
     * @param int $year
     * @return static
     */
    public function forYear(int $year): static
    {
        $month = fake()->numberBetween(1, 12);
        $period = sprintf('%d-%02d', $year, $month);
        
        return $this->state(fn (array $attributes) => [
            'period' => $period,
        ]);
    }
}