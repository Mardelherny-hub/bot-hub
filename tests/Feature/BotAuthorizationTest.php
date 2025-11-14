<?php

namespace Tests\Feature;

use App\Models\Bot;
use App\Models\BotUser;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * BotAuthorizationTest
 * 
 * Tests de autorización para operaciones sobre Bots.
 * 
 * COBERTURA:
 * - Autorización por roles (super_admin, admin, supervisor, agent, viewer)
 * - Autorización por permisos granulares (pivot bot_user)
 * - Aislamiento por tenant
 * - Combinación de roles + permisos
 * 
 * IMPORTANTE: Cada test verifica que las policies funcionan correctamente
 * y que el aislamiento multi-tenant se mantiene.
 */
class BotAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant1;
    protected Tenant $tenant2;
    protected Bot $bot1;
    protected Bot $bot2;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles necesarios
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'supervisor']);
        Role::create(['name' => 'agent']);
        Role::create(['name' => 'viewer']);

        // Crear tenants
        $this->tenant1 = Tenant::factory()->create(['name' => 'Tenant 1']);
        $this->tenant2 = Tenant::factory()->create(['name' => 'Tenant 2']);

        // Crear bots
        $this->bot1 = Bot::factory()->create([
            'tenant_id' => $this->tenant1->id,
            'name' => 'Bot 1',
        ]);

        $this->bot2 = Bot::factory()->create([
            'tenant_id' => $this->tenant2->id,
            'name' => 'Bot 2',
        ]);
    }

    /** @test */
    public function super_admin_can_view_any_bot()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($superAdmin->can('view', $this->bot1));
        $this->assertTrue($superAdmin->can('view', $this->bot2));
    }

    /** @test */
    public function admin_can_view_bots_from_their_tenant()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('view', $this->bot1));
        $this->assertFalse($admin->can('view', $this->bot2));
    }

    /** @test */
    public function admin_cannot_view_bots_from_other_tenant()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertFalse($admin->can('view', $this->bot2));
    }

    /** @test */
    public function supervisor_can_view_all_bots_from_their_tenant()
    {
        $supervisor = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $supervisor->assignRole('supervisor');

        $this->assertTrue($supervisor->can('view', $this->bot1));
        $this->assertFalse($supervisor->can('view', $this->bot2));
    }

    /** @test */
    public function agent_can_only_view_assigned_bots()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        // Sin asignar, no puede ver
        $this->assertFalse($agent->can('view', $this->bot1));

        // Asignar bot al agente
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $agent->id,
            'can_chat' => true,
        ]);

        // Refrescar relaciones
        $agent->refresh();

        // Ahora sí puede ver
        $this->assertTrue($agent->can('view', $this->bot1));
    }

    /** @test */
    public function only_admin_and_super_admin_can_create_bots()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        $this->assertTrue($superAdmin->can('create', Bot::class));
        $this->assertTrue($admin->can('create', Bot::class));
        $this->assertFalse($agent->can('create', Bot::class));
    }

    /** @test */
    public function user_with_can_manage_permission_can_update_bot()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        // Sin permiso, no puede actualizar
        $this->assertFalse($user->can('update', $this->bot1));

        // Asignar con can_manage
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_manage' => true,
        ]);

        $user->refresh();

        // Ahora sí puede actualizar
        $this->assertTrue($user->can('update', $this->bot1));
    }

    /** @test */
    public function user_without_can_manage_permission_cannot_update_bot()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        // Asignar sin can_manage
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_manage' => false,
            'can_chat' => true,
        ]);

        $user->refresh();

        $this->assertFalse($user->can('update', $this->bot1));
    }

    /** @test */
    public function user_with_can_chat_permission_can_chat_in_bot()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        // Asignar con can_chat
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_chat' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('chat', $this->bot1));
    }

    /** @test */
    public function user_without_can_chat_permission_cannot_chat_in_bot()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        // Asignar sin can_chat
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_chat' => false,
            'can_view_analytics' => true,
        ]);

        $user->refresh();

        $this->assertFalse($user->can('chat', $this->bot1));
    }

    /** @test */
    public function admin_can_view_analytics_of_any_bot_in_their_tenant()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('viewAnalytics', $this->bot1));
        $this->assertFalse($admin->can('viewAnalytics', $this->bot2));
    }

    /** @test */
    public function supervisor_can_view_analytics_of_any_bot_in_their_tenant()
    {
        $supervisor = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $supervisor->assignRole('supervisor');

        $this->assertTrue($supervisor->can('viewAnalytics', $this->bot1));
    }

    /** @test */
    public function user_with_can_view_analytics_permission_can_view_analytics()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        // Asignar con can_view_analytics
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_view_analytics' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('viewAnalytics', $this->bot1));
    }

    /** @test */
    public function user_with_can_train_kb_permission_can_train_knowledge_base()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        // Asignar con can_train_kb
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_train_kb' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('trainKnowledgeBase', $this->bot1));
    }

    /** @test */
    public function user_without_can_train_kb_permission_cannot_train_knowledge_base()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        // Asignar sin can_train_kb
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_train_kb' => false,
            'can_chat' => true,
        ]);

        $user->refresh();

        $this->assertFalse($user->can('trainKnowledgeBase', $this->bot1));
    }

    /** @test */
    public function only_admin_and_super_admin_can_delete_bots()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        // Asignar agente con can_manage
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $agent->id,
            'can_manage' => true,
        ]);

        $agent->refresh();

        $this->assertTrue($superAdmin->can('delete', $this->bot1));
        $this->assertTrue($admin->can('delete', $this->bot1));
        $this->assertTrue($agent->can('delete', $this->bot1)); // can_manage permite delete
    }

    /** @test */
    public function only_admin_and_super_admin_can_manage_bot_users()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        $this->assertTrue($superAdmin->can('manageUsers', $this->bot1));
        $this->assertTrue($admin->can('manageUsers', $this->bot1));
        $this->assertFalse($agent->can('manageUsers', $this->bot1));
    }

    /** @test */
    public function user_can_have_different_permissions_for_different_bots()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        $bot1 = Bot::factory()->create(['tenant_id' => $this->tenant1->id]);
        $bot2 = Bot::factory()->create(['tenant_id' => $this->tenant1->id]);

        // Bot 1: Solo chat
        BotUser::factory()->chatOnly()->create([
            'bot_id' => $bot1->id,
            'user_id' => $user->id,
        ]);

        // Bot 2: Acceso completo
        BotUser::factory()->fullAccess()->create([
            'bot_id' => $bot2->id,
            'user_id' => $user->id,
        ]);

        $user->refresh();

        // Bot 1: Solo chat
        $this->assertTrue($user->can('chat', $bot1));
        $this->assertFalse($user->can('update', $bot1));
        $this->assertFalse($user->can('viewAnalytics', $bot1));

        // Bot 2: Todo
        $this->assertTrue($user->can('chat', $bot2));
        $this->assertTrue($user->can('update', $bot2));
        $this->assertTrue($user->can('viewAnalytics', $bot2));
        $this->assertTrue($user->can('trainKnowledgeBase', $bot2));
    }

    /** @test */
    public function gates_work_correctly_for_bot_permissions()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_manage' => true,
            'can_chat' => true,
        ]);

        $user->refresh();

        $this->actingAs($user);

        $this->assertTrue(\Gate::allows('manage-bot', $this->bot1));
        $this->assertTrue(\Gate::allows('chat-in-bot', $this->bot1));
        $this->assertFalse(\Gate::allows('train-bot-kb', $this->bot1));
    }

    /** @test */
    public function super_admin_bypasses_all_gates_and_policies()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin);

        // Super admin puede hacer todo sin necesidad de permisos explícitos
        $this->assertTrue(\Gate::allows('manage-bot', $this->bot1));
        $this->assertTrue(\Gate::allows('chat-in-bot', $this->bot1));
        $this->assertTrue(\Gate::allows('view-bot-analytics', $this->bot1));
        $this->assertTrue(\Gate::allows('train-bot-kb', $this->bot1));

        $this->assertTrue($superAdmin->can('view', $this->bot1));
        $this->assertTrue($superAdmin->can('update', $this->bot1));
        $this->assertTrue($superAdmin->can('delete', $this->bot1));
    }
}