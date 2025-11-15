<?php

namespace Database\Seeders;

use App\Models\Bot;
use App\Models\KnowledgeBase;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * BotSeeder
 * 
 * Genera bots de prueba para cada tenant existente.
 * 
 * Distribución por plan:
 * - Starter: 1-2 bots (límite: 3)
 * - Professional: 2-3 bots (límite: 10)
 * - Enterprise: 3-5 bots (límite: 50)
 * 
 * Configuraciones variadas:
 * - Diferentes modelos de IA (GPT-4, GPT-4-turbo)
 * - Distintas personalidades y casos de uso
 * - Horarios de atención diversos
 * - Knowledge base activada en algunos
 * - Algunos activos, otros inactivos
 * 
 * IMPORTANTE: También crea Knowledge Bases para bots que las requieren.
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class BotSeeder extends Seeder
{
    /**
     * Personalidades predefinidas para bots
     */
    private array $personalities = [
        'friendly' => [
            'personality' => 'Amigable, cercano y empático. Usa un tono conversacional y cálido.',
            'instructions' => 'Responde de manera amigable y empática. Usa emojis ocasionalmente para dar calidez. Pregunta cómo puedes ayudar mejor.',
        ],
        'professional' => [
            'personality' => 'Profesional, formal y directo. Mantiene un tono corporativo.',
            'instructions' => 'Mantén un tono profesional y formal. Proporciona información precisa y estructurada. Evita usar emojis.',
        ],
        'supportive' => [
            'personality' => 'Servicial, paciente y detallado. Explica paso a paso.',
            'instructions' => 'Sé muy paciente y explica las cosas paso a paso. Pregunta si necesitan más clarificación. Mantén un tono servicial.',
        ],
        'sales' => [
            'personality' => 'Persuasivo, entusiasta y orientado a resultados.',
            'instructions' => 'Destaca los beneficios y características del producto. Maneja objeciones con empatía. Busca cerrar la venta naturalmente.',
        ],
        'tech' => [
            'personality' => 'Técnico, preciso y especializado. Usa terminología apropiada.',
            'instructions' => 'Proporciona respuestas técnicas precisas. Usa terminología adecuada pero explica conceptos complejos cuando sea necesario.',
        ],
    ];

    /**
     * Casos de uso comunes
     */
    private array $useCases = [
        ['name' => 'Soporte al Cliente', 'type' => 'supportive', 'kb' => true],
        ['name' => 'Ventas y Consultas', 'type' => 'sales', 'kb' => true],
        ['name' => 'Asistente de Reservas', 'type' => 'friendly', 'kb' => false],
        ['name' => 'Consultas Técnicas', 'type' => 'tech', 'kb' => true],
        ['name' => 'Atención General', 'type' => 'professional', 'kb' => false],
        ['name' => 'FAQ Automatizado', 'type' => 'friendly', 'kb' => true],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->error('❌ No hay tenants. Ejecuta TenantSeeder primero.');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->createBotsForTenant($tenant);
        }

        $this->command->info('');
        $this->command->info('=== RESUMEN ===');
        $this->command->info('Total bots: ' . Bot::count());
        $this->command->info('Bots activos: ' . Bot::where('is_active', true)->count());
        $this->command->info('Bots inactivos: ' . Bot::where('is_active', false)->count());
        $this->command->info('Con Knowledge Base: ' . Bot::where('use_knowledge_base', true)->count());
        $this->command->info('Knowledge Bases creadas: ' . KnowledgeBase::count());
    }

    /**
     * Crea bots para un tenant específico
     */
    private function createBotsForTenant(Tenant $tenant): void
    {
        // Determinar cantidad de bots según el plan
        $botCount = match($tenant->subscription_plan) {
            'starter' => rand(1, 2),        // 1-2 bots (límite: 3)
            'professional' => rand(2, 3),   // 2-3 bots (límite: 10)
            'enterprise' => rand(3, 5),     // 3-5 bots (límite: 50)
            default => 1,
        };

        // Para tenants suspendidos/cancelados, crear menos bots y todos inactivos
        if (in_array($tenant->subscription_status, ['suspended', 'cancelled'])) {
            $botCount = 1;
        }

        $bots = [];

        for ($i = 0; $i < $botCount; $i++) {
            $useCase = $this->useCases[array_rand($this->useCases)];
            $personality = $this->personalities[$useCase['type']];
            
            // Generar teléfono único
            $phoneNumber = $this->generateUniquePhoneNumber();
            
            // Determinar si está activo (tenants activos: 80% activos, suspendidos: 0%)
            $isActive = $tenant->subscription_status === 'active' 
                ? (rand(1, 100) <= 80)
                : false;

            // Crear el bot
            $bot = Bot::create([
                'tenant_id' => $tenant->id,
                'name' => $useCase['name'] . ' - ' . $tenant->name,
                'description' => "Bot de {$useCase['name']} para {$tenant->name}",
                'phone_number' => $phoneNumber,
                'whatsapp_business_account_id' => 'waba_' . Str::random(20),
                'whatsapp_phone_number_id' => 'phone_' . Str::random(15),
                'ai_model' => $this->selectAiModel($tenant->subscription_plan),
                'personality' => $personality['personality'],
                'instructions' => $personality['instructions'],
                'max_tokens' => $this->getMaxTokens($tenant->subscription_plan),
                'temperature' => $this->getTemperature($useCase['type']),
                'language' => 'es',
                'is_active' => $isActive,
                'fallback_to_human' => rand(0, 100) > 50,
                'inactivity_timeout_minutes' => rand(5, 30),
                'business_hours_start' => '09:00:00',
                'business_hours_end' => $this->getBusinessHoursEnd($useCase['type']),
                'business_days' => $this->getBusinessDays($tenant->subscription_plan),
                'out_of_hours_message' => 'Gracias por contactarnos. Nuestro horario de atención es de lunes a viernes de 9:00 a 18:00. Te responderemos a la brevedad.',
                'use_knowledge_base' => $useCase['kb'] && $tenant->subscription_plan !== 'starter',
                'knowledge_base_results' => 5,
                'knowledge_base_threshold' => 0.75,
                'metadata' => [
                    'use_case' => $useCase['name'],
                    'created_by_seeder' => true,
                    'industry' => $this->getIndustry($useCase['name']),
                ],
            ]);

            // Crear Knowledge Base si el bot la usa
            if ($bot->use_knowledge_base) {
                $this->createKnowledgeBase($bot);
            }

            // Asignar usuarios del tenant al bot con permisos
            $this->assignUsersToBot($bot, $tenant);

            $status = $isActive ? '✓ ACTIVO' : '○ INACTIVO';
            $kb = $bot->use_knowledge_base ? '+ KB' : '';
            $bots[] = "  {$status} {$bot->name} {$kb}";
        }

        $this->command->info("✓ Tenant '{$tenant->name}' ({$tenant->subscription_plan}): {$botCount} bots creados");
        foreach ($bots as $bot) {
            $this->command->line($bot);
        }
    }

    /**
     * Genera un número de teléfono único
     */
    private function generateUniquePhoneNumber(): string
    {
        do {
            $phoneNumber = '+549' . rand(1100000000, 1199999999); // Números argentinos
        } while (Bot::where('phone_number', $phoneNumber)->exists());

        return $phoneNumber;
    }

    /**
     * Selecciona el modelo de IA según el plan
     */
    private function selectAiModel(string $plan): string
    {
        return match($plan) {
            'starter' => 'gpt-4o-mini',
            'professional' => rand(0, 1) ? 'gpt-4o' : 'gpt-4o-mini',
            'enterprise' => 'gpt-4o',
            default => 'gpt-4o-mini',
        };
    }

    /**
     * Obtiene max_tokens según el plan
     */
    private function getMaxTokens(string $plan): int
    {
        return match($plan) {
            'starter' => 500,
            'professional' => 1000,
            'enterprise' => 2000,
            default => 500,
        };
    }

    /**
     * Obtiene temperature según el tipo de bot
     */
    private function getTemperature(string $type): float
    {
        return match($type) {
            'professional', 'tech' => 0.5,  // Más preciso
            'sales' => 0.8,                  // Más creativo
            default => 0.7,                  // Balanceado
        };
    }

    /**
     * Obtiene horario de cierre según tipo
     */
    private function getBusinessHoursEnd(string $type): string
    {
        return match($type) {
            'sales' => '20:00:00',           // Ventas: horario extendido
            'tech' => '17:00:00',            // Tech: horario regular
            default => '18:00:00',           // Estándar
        };
    }

    /**
     * Obtiene días laborables según plan
     */
    private function getBusinessDays(string $plan): array
    {
        if ($plan === 'enterprise') {
            // Enterprise: 24/7
            return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        }

        if ($plan === 'professional') {
            // Professional: Lun-Sáb
            return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        }

        // Starter: Lun-Vie
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    }

    /**
     * Determina industria según caso de uso
     */
    private function getIndustry(string $useCase): string
    {
        return match(true) {
            str_contains($useCase, 'Ventas') => 'sales',
            str_contains($useCase, 'Soporte') => 'support',
            str_contains($useCase, 'Reservas') => 'hospitality',
            str_contains($useCase, 'Técnicas') => 'technology',
            default => 'general',
        };
    }

    /**
     * Crea Knowledge Base para un bot
     */
    private function createKnowledgeBase(Bot $bot): void
    {
        KnowledgeBase::create([
            'bot_id' => $bot->id,
            'name' => "KB - {$bot->name}",
            'description' => "Base de conocimiento para {$bot->name}",
            'is_active' => true,
            'document_count' => 0,
            'total_tokens' => 0,
            'last_trained_at' => null,
            'embedding_model' => 'text-embedding-ada-002',
            'settings' => [
                'chunk_size' => 500,
                'chunk_overlap' => 50,
                'max_results' => 5,
                'similarity_threshold' => 0.75,
            ],
        ]);
    }

    /**
     * Asigna usuarios del tenant al bot con permisos
     */
    private function assignUsersToBot(Bot $bot, Tenant $tenant): void
    {
        // Obtener usuarios del tenant
        $users = User::where('tenant_id', $tenant->id)->get();

        foreach ($users as $user) {
            // Determinar permisos según el rol
            $permissions = $this->getPermissionsByRole($user);

            // Solo asignar si tiene al menos un permiso
            if ($permissions['hasAny']) {
                $bot->users()->attach($user->id, [
                    'can_manage' => $permissions['can_manage'],
                    'can_view_analytics' => $permissions['can_view_analytics'],
                    'can_chat' => $permissions['can_chat'],
                    'can_train_kb' => $permissions['can_train_kb'],
                    'can_delete_data' => $permissions['can_delete_data'],
                    'assigned_at' => now(),
                ]);
            }
        }
    }

    /**
     * Determina permisos según el rol del usuario
     */
    private function getPermissionsByRole(User $user): array
    {
        // Admin y supervisor tienen todos los permisos
        if ($user->hasRole('admin')) {
            return [
                'hasAny' => true,
                'can_manage' => true,
                'can_view_analytics' => true,
                'can_chat' => true,
                'can_train_kb' => true,
                'can_delete_data' => true,
            ];
        }

        if ($user->hasRole('supervisor')) {
            return [
                'hasAny' => true,
                'can_manage' => false,
                'can_view_analytics' => true,
                'can_chat' => true,
                'can_train_kb' => false,
                'can_delete_data' => false,
            ];
        }

        // Agents: permisos limitados
        if ($user->hasRole('agent')) {
            return [
                'hasAny' => true,
                'can_manage' => false,
                'can_view_analytics' => false,
                'can_chat' => true,
                'can_train_kb' => false,
                'can_delete_data' => false,
            ];
        }

        // Sin permisos
        return [
            'hasAny' => false,
            'can_manage' => false,
            'can_view_analytics' => false,
            'can_chat' => false,
            'can_train_kb' => false,
            'can_delete_data' => false,
        ];
    }
}