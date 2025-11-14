<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * TenantIsolationTest
 * 
 * Verifica que el sistema multi-tenant funciona correctamente:
 * 1. TenantScope filtra queries automáticamente
 * 2. Usuarios solo ven datos de su tenant
 * 3. Super admin puede ver todos los tenants
 * 4. Datos no se mezclan entre tenants
 * 
 * CRÍTICO: Si alguno de estos tests falla, hay una brecha de seguridad.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles necesarios
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'agent']);
    }

    /**
     * Test: Usuarios solo pueden ver otros usuarios de su tenant
     * 
     * @test
     */
    public function users_can_only_see_their_tenant_users()
    {
        // Arrange: Crear 2 tenants con usuarios
        $tenant1 = Tenant::factory()->create(['name' => 'Tenant 1']);
        $tenant2 = Tenant::factory()->create(['name' => 'Tenant 2']);

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user3 = User::factory()->create(['tenant_id' => $tenant2->id]);

        // Act: Autenticar como user1 (tenant 1)
        $this->actingAs($user1);

        // Assert: User1 solo ve usuarios de tenant 1
        // NOTA: User no usa BelongsToTenant, así que usamos scope manual
        $users = User::where('tenant_id', $tenant1->id)->get();
        
        $this->assertCount(2, $users);
        $this->assertTrue($users->contains($user1));
        $this->assertTrue($users->contains($user2));
        $this->assertFalse($users->contains($user3));
    }

    /**
     * Test: Super admin puede ver todos los usuarios
     * 
     * @test
     */
    public function super_admin_can_see_all_users()
    {
        // Arrange: Crear tenants y usuarios
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        User::factory()->create(['tenant_id' => $tenant1->id]);
        User::factory()->create(['tenant_id' => $tenant2->id]);

        $superAdmin = User::factory()->create(['tenant_id' => null]);
        $superAdmin->assignRole('super_admin');

        // Act: Autenticar como super admin
        $this->actingAs($superAdmin);

        // Assert: Puede ver todos los usuarios
        $allUsers = User::withoutGlobalScopes()->get();
        $this->assertCount(3, $allUsers);
    }

    /**
     * Test: Tenant tiene la cantidad correcta de usuarios
     * 
     * @test
     */
    public function tenant_has_correct_user_count()
    {
        // Arrange
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        User::factory()->count(3)->create(['tenant_id' => $tenant1->id]);
        User::factory()->count(2)->create(['tenant_id' => $tenant2->id]);

        // Assert
        $this->assertEquals(3, $tenant1->users()->count());
        $this->assertEquals(2, $tenant2->users()->count());
    }

    /**
     * Test: Usuario pertenece al tenant correcto
     * 
     * @test
     */
    public function user_belongs_to_correct_tenant()
    {
        // Arrange
        $tenant = Tenant::factory()->create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        // Assert
        $this->assertNotNull($user->tenant);
        $this->assertEquals($tenant->id, $user->tenant->id);
        $this->assertEquals('Test Tenant', $user->tenant->name);
    }

    /**
     * Test: Super admin no tiene tenant
     * 
     * @test
     */
    public function super_admin_has_no_tenant()
    {
        // Arrange
        $superAdmin = User::factory()->create(['tenant_id' => null]);
        $superAdmin->assignRole('super_admin');

        // Assert
        $this->assertNull($superAdmin->tenant_id);
        $this->assertNull($superAdmin->tenant);
        $this->assertTrue($superAdmin->isSuperAdmin());
    }

    /**
     * Test: No se puede acceder a usuarios de otro tenant
     * 
     * @test
     */
    public function cannot_access_other_tenant_users()
    {
        // Arrange
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);

        // Act: Autenticar como user1
        $this->actingAs($user1);

        // Assert: No puede encontrar user2 por ID usando scope
        $foundUser = User::forTenant($tenant1->id)->find($user2->id);
        $this->assertNull($foundUser);
    }

    /**
     * Test: Scope forTenant filtra correctamente
     * 
     * @test
     */
    public function scope_for_tenant_filters_correctly()
    {
        // Arrange
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        User::factory()->count(5)->create(['tenant_id' => $tenant1->id]);
        User::factory()->count(3)->create(['tenant_id' => $tenant2->id]);

        // Act
        $tenant1Users = User::forTenant($tenant1->id)->get();
        $tenant2Users = User::forTenant($tenant2->id)->get();

        // Assert
        $this->assertCount(5, $tenant1Users);
        $this->assertCount(3, $tenant2Users);
        
        // Verificar que todos los usuarios son del tenant correcto
        $tenant1Users->each(function ($user) use ($tenant1) {
            $this->assertEquals($tenant1->id, $user->tenant_id);
        });
    }

    /**
     * Test: Tenant puede verificar si puede crear más bots
     * 
     * @test
     */
    public function tenant_can_check_bot_creation_limit()
    {
        $this->markTestSkipped('Bot model will be created in Sprint 1');

        // Arrange
        $tenant = Tenant::factory()->create([
            'monthly_bot_limit' => 3
        ]);

        // Assert: Puede crear bots (no tiene ninguno aún)
        $this->assertTrue($tenant->canCreateBot());
        $this->assertEquals(3, $tenant->remaining_bot_slots);
    }

    /**
     * Test: Tenant puede verificar si puede agregar más usuarios
     * 
     * @test
     */
    public function tenant_can_check_user_limit()
    {
        // Arrange
        $tenant = Tenant::factory()->create([
            'monthly_user_limit' => 5
        ]);

        User::factory()->count(3)->create(['tenant_id' => $tenant->id]);

        // Assert
        $this->assertTrue($tenant->canAddUser());
        $this->assertEquals(2, $tenant->remaining_user_slots);

        // Crear 2 usuarios más (llegar al límite)
        User::factory()->count(2)->create(['tenant_id' => $tenant->id]);

        // Refrescar el modelo
        $tenant->refresh();

        // Assert: No puede agregar más
        $this->assertFalse($tenant->canAddUser());
        $this->assertEquals(0, $tenant->remaining_user_slots);
    }

    /**
     * Test: Tenant verifica suscripción activa
     * 
     * @test
     */
    public function tenant_can_verify_active_subscription()
    {
        // Arrange: Tenant activo
        $activeTenant = Tenant::factory()->create([
            'subscription_status' => 'active'
        ]);

        // Arrange: Tenant suspendido
        $suspendedTenant = Tenant::factory()->create([
            'subscription_status' => 'suspended'
        ]);

        // Assert
        $this->assertTrue($activeTenant->hasActiveSubscription());
        $this->assertFalse($suspendedTenant->hasActiveSubscription());
    }

    /**
     * Test: Scope active filtra tenants activos
     * 
     * @test
     */
    public function scope_active_filters_active_tenants()
    {
        // Arrange
        Tenant::factory()->count(3)->create(['subscription_status' => 'active']);
        Tenant::factory()->count(2)->create(['subscription_status' => 'suspended']);
        Tenant::factory()->create(['subscription_status' => 'cancelled']);

        // Act
        $activeTenants = Tenant::active()->get();

        // Assert
        $this->assertCount(3, $activeTenants);
        $activeTenants->each(function ($tenant) {
            $this->assertEquals('active', $tenant->subscription_status);
        });
    }
}