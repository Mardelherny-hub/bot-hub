<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * UserSeeder
 * 
 * Genera usuarios de prueba para cada tenant existente.
 * 
 * Estructura por tenant:
 * - 1 Admin (gestor principal del tenant)
 * - 1 Supervisor (visualización completa, sin edición)
 * - 1-2 Agents (usuarios operativos)
 * 
 * NOTA: El super_admin ya existe del DatabaseSeeder original
 * 
 * @version 1.0.0
 * @since Sprint 1
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan los roles
        $this->ensureRolesExist();

        // Crear super admin si no existe
        $this->createSuperAdmin();

        // Crear usuarios para cada tenant
        $this->createTenantUsers();
    }

    /**
     * Asegura que existan todos los roles necesarios
     */
    private function ensureRolesExist(): void
    {
        $roles = ['super_admin', 'admin', 'supervisor', 'agent', 'viewer'];

        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
                $this->command->warn("⚠️  Rol '{$roleName}' creado (no existía)");
            }
        }
    }

    /**
     * Crea el super admin global (sin tenant)
     */
    private function createSuperAdmin(): void
    {
        if (User::where('email', 'admin@bothub.local')->exists()) {
            $this->command->info('✓ Super Admin ya existe');
            return;
        }

        $superAdmin = User::create([
            'tenant_id' => null, // Super admin NO tiene tenant
            'name' => 'Super Administrator',
            'email' => 'admin@bothub.local',
            'password' => Hash::make('password'),
            'phone' => '+1-555-0000',
            'avatar_url' => null,
            'is_active' => true,
            'last_login_at' => now()->subDays(2),
            'preferences' => [
                'theme' => 'dark',
                'language' => 'en',
                'notifications' => true,
            ],
        ]);

        $superAdmin->assignRole('super_admin');

        $this->command->info('✓ Super Admin creado: admin@bothub.local');
    }

    /**
     * Crea usuarios para todos los tenants
     */
    private function createTenantUsers(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->error('❌ No hay tenants. Ejecuta TenantSeeder primero.');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->createUsersForTenant($tenant);
        }

        $this->command->info('');
        $this->command->info('=== RESUMEN ===');
        $this->command->info('Total usuarios: ' . User::count());
        $this->command->info('Super Admins: ' . User::whereNull('tenant_id')->count());
        $this->command->info('Tenant Users: ' . User::whereNotNull('tenant_id')->count());
        $this->command->info('  - Admins: ' . User::role('admin')->count());
        $this->command->info('  - Supervisors: ' . User::role('supervisor')->count());
        $this->command->info('  - Agents: ' . User::role('agent')->count());
    }

    /**
     * Crea usuarios específicos para un tenant
     */
    private function createUsersForTenant(Tenant $tenant): void
    {
        // Determinar cantidad de usuarios según el plan
        $userCount = match($tenant->subscription_plan) {
            'starter' => 3,        // 1 admin + 1 supervisor + 1 agent
            'professional' => 4,   // 1 admin + 1 supervisor + 2 agents
            'enterprise' => 5,     // 1 admin + 1 supervisor + 3 agents
            default => 3,
        };

        $users = [];

        // 1. ADMIN (gestor principal del tenant)
        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => ucfirst($tenant->slug) . ' Admin',
            'email' => "admin@{$tenant->slug}.com",
            'password' => Hash::make('password'),
            'phone' => $this->generatePhone(),
            'avatar_url' => null,
            'is_active' => true,
            'last_login_at' => now()->subDays(rand(1, 7)),
            'preferences' => [
                'theme' => 'light',
                'language' => 'en',
                'notifications' => true,
                'dashboard_layout' => 'grid',
            ],
        ]);
        $admin->assignRole('admin');
        $users[] = "admin@{$tenant->slug}.com (admin)";

        // 2. SUPERVISOR (visualización completa)
        $supervisor = User::create([
            'tenant_id' => $tenant->id,
            'name' => ucfirst($tenant->slug) . ' Supervisor',
            'email' => "supervisor@{$tenant->slug}.com",
            'password' => Hash::make('password'),
            'phone' => $this->generatePhone(),
            'avatar_url' => null,
            'is_active' => true,
            'last_login_at' => now()->subDays(rand(1, 5)),
            'preferences' => [
                'theme' => 'light',
                'language' => 'en',
                'notifications' => true,
                'dashboard_layout' => 'list',
            ],
        ]);
        $supervisor->assignRole('supervisor');
        $users[] = "supervisor@{$tenant->slug}.com (supervisor)";

        // 3. AGENTS (usuarios operativos)
        $agentCount = $userCount - 2; // Restamos admin y supervisor
        for ($i = 1; $i <= $agentCount; $i++) {
            $agent = User::create([
                'tenant_id' => $tenant->id,
                'name' => ucfirst($tenant->slug) . " Agent {$i}",
                'email' => "agent{$i}@{$tenant->slug}.com",
                'password' => Hash::make('password'),
                'phone' => $this->generatePhone(),
                'avatar_url' => null,
                'is_active' => $i === 1 ? true : (rand(0, 100) > 20), // 80% activos
                'last_login_at' => rand(0, 100) > 30 ? now()->subDays(rand(1, 14)) : null,
                'preferences' => [
                    'theme' => rand(0, 1) ? 'light' : 'dark',
                    'language' => 'en',
                    'notifications' => rand(0, 1) ? true : false,
                    'dashboard_layout' => 'grid',
                ],
            ]);
            $agent->assignRole('agent');
            $users[] = "agent{$i}@{$tenant->slug}.com (agent)";
        }

        $this->command->info("✓ Tenant '{$tenant->name}' ({$tenant->subscription_plan}): {$userCount} usuarios creados");
        foreach ($users as $user) {
            $this->command->line("  - {$user}");
        }
    }

    /**
     * Genera un número de teléfono fake
     */
    private function generatePhone(): string
    {
        return '+1-555-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }
}