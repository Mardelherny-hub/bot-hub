<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * TenantSeeder
 * 
 * Genera 10 tenants de prueba con distribución realista de planes:
 * - 40% Starter (4 tenants)
 * - 30% Professional (3 tenants)
 * - 30% Enterprise (3 tenants)
 * 
 * Estados:
 * - 60% Active (6 tenants)
 * - 20% Trial (2 tenants)
 * - 20% Suspended/Cancelled (2 tenants)
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTA: El tenant "ACME Corp" ya existe del DatabaseSeeder original
        // Lo verificamos para no duplicar
        if (!Tenant::where('slug', 'acme-corp')->exists()) {
            $this->createAcmeTenant();
        }

        // Crear 10 tenants adicionales con distribución realista
        $this->createTenants();
    }

    /**
     * Crea el tenant ACME Corp (tenant principal de prueba)
     */
    private function createAcmeTenant(): void
    {
        Tenant::create([
            'name' => 'ACME Corp',
            'slug' => 'acme-corp',
            'email' => 'contact@acme.com',
            'phone' => '+1-555-0100',
            'website' => 'https://acme.com',
            'logo_url' => null,
            'subscription_plan' => 'professional',
            'subscription_status' => 'active',
            'subscription_started_at' => now()->subMonths(3),
            'subscription_ends_at' => now()->addMonth(),
            'monthly_conversation_limit' => 10000,
            'monthly_bot_limit' => 10,
            'monthly_user_limit' => 20,
            'is_white_label' => false,
            'settings' => [
                'timezone' => 'America/New_York',
                'language' => 'en',
                'notification_email' => 'notifications@acme.com',
            ],
        ]);

        $this->command->info('✓ Tenant ACME Corp creado');
    }

    /**
     * Crea 10 tenants de prueba con datos realistas
     */
    private function createTenants(): void
    {
        $companies = [
            // STARTER (4 tenants - 40%)
            [
                'name' => 'TechStart Solutions',
                'slug' => 'techstart',
                'email' => 'info@techstart.io',
                'phone' => '+1-555-0101',
                'website' => 'https://techstart.io',
                'plan' => 'starter',
                'status' => 'trial',
                'started' => now()->subDays(7),
                'ends' => now()->addDays(7),
                'white_label' => false,
            ],
            [
                'name' => 'Digital Marketing Pro',
                'slug' => 'digitalmarketing',
                'email' => 'hello@digitalmarketingpro.com',
                'phone' => '+1-555-0102',
                'website' => 'https://digitalmarketingpro.com',
                'plan' => 'starter',
                'status' => 'active',
                'started' => now()->subMonths(2),
                'ends' => now()->addMonth(),
                'white_label' => false,
            ],
            [
                'name' => 'QuickSupport Agency',
                'slug' => 'quicksupport',
                'email' => 'contact@quicksupport.net',
                'phone' => '+1-555-0103',
                'website' => 'https://quicksupport.net',
                'plan' => 'starter',
                'status' => 'active',
                'started' => now()->subMonth(),
                'ends' => now()->addMonth(),
                'white_label' => false,
            ],
            [
                'name' => 'Local Biz Hub',
                'slug' => 'localbizhub',
                'email' => 'admin@localbizhub.com',
                'phone' => '+1-555-0104',
                'website' => null,
                'plan' => 'starter',
                'status' => 'suspended',
                'started' => now()->subMonths(4),
                'ends' => now()->subDays(5),
                'white_label' => false,
            ],

            // PROFESSIONAL (3 tenants - 30%)
            [
                'name' => 'Enterprise Solutions Inc',
                'slug' => 'enterprise-solutions',
                'email' => 'info@enterprisesolutions.com',
                'phone' => '+1-555-0201',
                'website' => 'https://enterprisesolutions.com',
                'plan' => 'professional',
                'status' => 'active',
                'started' => now()->subMonths(6),
                'ends' => now()->addMonths(2),
                'white_label' => true,
            ],
            [
                'name' => 'Global Chat Services',
                'slug' => 'globalchat',
                'email' => 'support@globalchatservices.io',
                'phone' => '+1-555-0202',
                'website' => 'https://globalchatservices.io',
                'plan' => 'professional',
                'status' => 'active',
                'started' => now()->subMonths(4),
                'ends' => now()->addMonth(),
                'white_label' => false,
            ],
            [
                'name' => 'Customer First Agency',
                'slug' => 'customerfirst',
                'email' => 'hello@customerfirstagency.com',
                'phone' => '+1-555-0203',
                'website' => 'https://customerfirstagency.com',
                'plan' => 'professional',
                'status' => 'trial',
                'started' => now()->subDays(10),
                'ends' => now()->addDays(4),
                'white_label' => false,
            ],

            // ENTERPRISE (3 tenants - 30%)
            [
                'name' => 'MegaCorp Communications',
                'slug' => 'megacorp',
                'email' => 'contact@megacorp.com',
                'phone' => '+1-555-0301',
                'website' => 'https://megacorp.com',
                'plan' => 'enterprise',
                'status' => 'active',
                'started' => now()->subYear(),
                'ends' => now()->addMonths(6),
                'white_label' => true,
            ],
            [
                'name' => 'International Marketing Group',
                'slug' => 'img-global',
                'email' => 'info@img-global.com',
                'phone' => '+1-555-0302',
                'website' => 'https://img-global.com',
                'plan' => 'enterprise',
                'status' => 'active',
                'started' => now()->subMonths(8),
                'ends' => now()->addMonths(3),
                'white_label' => true,
            ],
            [
                'name' => 'NextGen Support Systems',
                'slug' => 'nextgen',
                'email' => 'admin@nextgensupport.io',
                'phone' => '+1-555-0303',
                'website' => 'https://nextgensupport.io',
                'plan' => 'enterprise',
                'status' => 'cancelled',
                'started' => now()->subMonths(10),
                'ends' => now()->subDays(10),
                'white_label' => false,
            ],
        ];

        foreach ($companies as $company) {
            // Establecer límites según el plan
            [$conversationLimit, $botLimit, $userLimit] = $this->getPlanLimits($company['plan']);

            Tenant::create([
                'name' => $company['name'],
                'slug' => $company['slug'],
                'email' => $company['email'],
                'phone' => $company['phone'],
                'website' => $company['website'],
                'logo_url' => null,
                'subscription_plan' => $company['plan'],
                'subscription_status' => $company['status'],
                'subscription_started_at' => $company['started'],
                'subscription_ends_at' => $company['ends'],
                'monthly_conversation_limit' => $conversationLimit,
                'monthly_bot_limit' => $botLimit,
                'monthly_user_limit' => $userLimit,
                'is_white_label' => $company['white_label'],
                'settings' => [
                    'timezone' => 'America/Los_Angeles',
                    'language' => 'en',
                    'notification_email' => $company['email'],
                    'features' => $this->getPlanFeatures($company['plan']),
                ],
            ]);

            $this->command->info("✓ Tenant {$company['name']} ({$company['plan']}) creado");
        }

        $this->command->info('');
        $this->command->info('=== RESUMEN ===');
        $this->command->info('Total tenants: ' . Tenant::count());
        $this->command->info('Starter: ' . Tenant::where('subscription_plan', 'starter')->count());
        $this->command->info('Professional: ' . Tenant::where('subscription_plan', 'professional')->count());
        $this->command->info('Enterprise: ' . Tenant::where('subscription_plan', 'enterprise')->count());
        $this->command->info('Active: ' . Tenant::where('subscription_status', 'active')->count());
        $this->command->info('Trial: ' . Tenant::where('subscription_status', 'trial')->count());
        $this->command->info('Suspended/Cancelled: ' . Tenant::whereIn('subscription_status', ['suspended', 'cancelled'])->count());
    }

    /**
     * Obtiene los límites según el plan
     */
    private function getPlanLimits(string $plan): array
    {
        return match($plan) {
            'starter' => [1000, 3, 5],
            'professional' => [10000, 10, 20],
            'enterprise' => [100000, 50, 100],
            default => [1000, 3, 5],
        };
    }

    /**
     * Obtiene features habilitadas según el plan
     */
    private function getPlanFeatures(string $plan): array
    {
        $baseFeatures = [
            'whatsapp_integration' => true,
            'ai_conversations' => true,
            'knowledge_base' => true,
        ];

        $professionalFeatures = array_merge($baseFeatures, [
            'custom_branding' => true,
            'advanced_analytics' => true,
            'api_access' => true,
        ]);

        $enterpriseFeatures = array_merge($professionalFeatures, [
            'priority_support' => true,
            'custom_integrations' => true,
            'sla_guarantee' => true,
            'dedicated_manager' => true,
        ]);

        return match($plan) {
            'starter' => $baseFeatures,
            'professional' => $professionalFeatures,
            'enterprise' => $enterpriseFeatures,
            default => $baseFeatures,
        };
    }
}