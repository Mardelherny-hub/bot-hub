<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * DatabaseSeeder Principal
 * 
 * Orquesta todos los seeders para poblar la base de datos completa.
 * 
 * EJECUCIÓN:
 * php artisan migrate:fresh --seed
 * 
 * ORDEN DE EJECUCIÓN:
 * 1. Roles de Spatie Permission
 * 2. Tenants (10-11 tenants con diferentes planes)
 * 3. Users (Super admin + usuarios por tenant)
 * 4. Bots (2-5 bots por tenant + Knowledge Bases)
 * 
 * RESULTADO:
 * - 1 Super Admin global
 * - 10+ Tenants (starter, professional, enterprise)
 * - 40+ Usuarios (distribuidos por tenant y roles)
 * - 25+ Bots (con configuraciones IA variadas)
 * - 15+ Knowledge Bases (vinculadas a bots)
 * - Usuarios asignados a bots con permisos granulares
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🌱 INICIANDO SEEDERS DE BOTHUB');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        // 1. Crear roles de Spatie Permission
        $this->createRoles();

        // 2. Ejecutar seeders en orden
        $this->command->info('📦 Ejecutando seeders...');
        $this->command->newLine();

        $this->call([
            TenantSeeder::class,    // Crea 10-11 tenants
            UserSeeder::class,      // Crea super admin + usuarios por tenant
            BotSeeder::class,       // Crea bots + knowledge bases + asignaciones
        ]);

        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✅ SEEDERS COMPLETADOS EXITOSAMENTE');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();
        
        // Mostrar credenciales y resumen
        $this->showSummary();
        $this->showCredentials();
    }

    /**
     * Crear roles de Spatie Permission si no existen
     */
    private function createRoles(): void
    {
        $this->command->info('🔐 Creando roles de Spatie Permission...');

        $roles = [
            'super_admin' => 'Acceso total a la plataforma',
            'admin' => 'Gestor del tenant',
            'supervisor' => 'Visualización completa del tenant',
            'agent' => 'Usuario operativo',
            'viewer' => 'Solo lectura',
        ];

        foreach ($roles as $roleName => $description) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
                $this->command->line("  ✓ Rol '{$roleName}' creado - {$description}");
            } else {
                $this->command->line("  - Rol '{$roleName}' ya existe");
            }
        }

        $this->command->newLine();
    }

    /**
     * Mostrar resumen de datos creados
     */
    private function showSummary(): void
    {
        $this->command->info('📊 RESUMEN DE DATOS CREADOS');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        // Tenants
        $totalTenants = \App\Models\Tenant::count();
        $activeTenants = \App\Models\Tenant::where('subscription_status', 'active')->count();
        $trialTenants = \App\Models\Tenant::where('subscription_status', 'trial')->count();
        
        $this->command->line("  Tenants:");
        $this->command->line("    Total: {$totalTenants}");
        $this->command->line("    Activos: {$activeTenants}");
        $this->command->line("    Trial: {$trialTenants}");
        
        // Usuarios
        $totalUsers = \App\Models\User::count();
        $superAdmins = \App\Models\User::whereNull('tenant_id')->count();
        $tenantUsers = \App\Models\User::whereNotNull('tenant_id')->count();
        
        $this->command->line("  Usuarios:");
        $this->command->line("    Total: {$totalUsers}");
        $this->command->line("    Super Admins: {$superAdmins}");
        $this->command->line("    Usuarios de Tenants: {$tenantUsers}");
        
        // Bots
        $totalBots = \App\Models\Bot::count();
        $activeBots = \App\Models\Bot::where('is_active', true)->count();
        $botsWithKB = \App\Models\Bot::where('use_knowledge_base', true)->count();
        
        $this->command->line("  Bots:");
        $this->command->line("    Total: {$totalBots}");
        $this->command->line("    Activos: {$activeBots}");
        $this->command->line("    Con Knowledge Base: {$botsWithKB}");
        
        // Knowledge Bases
        $totalKB = \App\Models\KnowledgeBase::count();
        
        $this->command->line("  Knowledge Bases:");
        $this->command->line("    Total: {$totalKB}");
        
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();
    }

    /**
     * Mostrar credenciales de acceso importantes
     */
    private function showCredentials(): void
    {
        $this->command->info('🔑 CREDENCIALES DE ACCESO');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();
        
        $this->command->line('  <fg=green>SUPER ADMIN</> (Acceso global a la plataforma):');
        $this->command->line('    Email: <fg=cyan>admin@bothub.local</>');
        $this->command->line('    Password: <fg=cyan>password</>');
        $this->command->line('    URL: <fg=yellow>http://bothub.local/admin/dashboard</>');
        $this->command->newLine();

        $this->command->line('  <fg=green>TENANT ACME CORP</> (Ejemplo):');
        $this->command->line('    Admin: <fg=cyan>admin@acme-corp.com</> / <fg=cyan>password</>');
        $this->command->line('    Supervisor: <fg=cyan>supervisor@acme-corp.com</> / <fg=cyan>password</>');
        $this->command->line('    Agent: <fg=cyan>agent1@acme-corp.com</> / <fg=cyan>password</>');
        $this->command->line('    URL: <fg=yellow>http://bothub.local/tenant/dashboard</>');
        $this->command->newLine();

        $this->command->line('  <fg=green>OTROS TENANTS</> (Patrón de emails):');
        $this->command->line('    admin@{tenant-slug}.com / password');
        $this->command->line('    supervisor@{tenant-slug}.com / password');
        $this->command->line('    agent1@{tenant-slug}.com / password');
        $this->command->newLine();

        $this->command->line('  <fg=green>TENANTS DISPONIBLES:</>');
        $tenants = \App\Models\Tenant::select('slug', 'name', 'subscription_plan')->get();
        foreach ($tenants as $tenant) {
            $plan = ucfirst($tenant->subscription_plan);
            $this->command->line("    • {$tenant->slug} - {$tenant->name} ({$plan})");
        }
        
        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        $this->command->line('  💡 <fg=yellow>COMANDOS ÚTILES:</>');
        $this->command->line('    Ver usuarios: <fg=cyan>php artisan tinker</> → User::all()');
        $this->command->line('    Ver tenants: <fg=cyan>php artisan tinker</> → Tenant::with("bots")->get()');
        $this->command->line('    Ver bots: <fg=cyan>php artisan tinker</> → Bot::with("tenant")->get()');
        $this->command->newLine();
    }
}