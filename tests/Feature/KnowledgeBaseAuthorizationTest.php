<?php

namespace Tests\Feature;

use App\Models\Bot;
use App\Models\BotUser;
use App\Models\KnowledgeBase;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * KnowledgeBaseAuthorizationTest
 * 
 * Tests de autorización para operaciones sobre Knowledge Bases.
 * 
 * COBERTURA:
 * - Autorización por roles
 * - Autorización por permiso can_train_kb (crítico)
 * - Autorización por permiso can_delete_data
 * - Aislamiento por tenant
 * 
 * IMPORTANTE: Entrenar KB es una operación sensible que afecta
 * las respuestas del bot, por eso tiene permisos estrictos.
 */
class KnowledgeBaseAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant1;
    protected Tenant $tenant2;
    protected Bot $bot1;
    protected Bot $bot2;
    protected KnowledgeBase $kb1;
    protected KnowledgeBase $kb2;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'supervisor']);
        Role::create(['name' => 'agent']);

        $this->tenant1 = Tenant::factory()->create();
        $this->tenant2 = Tenant::factory()->create();

        $this->bot1 = Bot::factory()->create(['tenant_id' => $this->tenant1->id]);
        $this->bot2 = Bot::factory()->create(['tenant_id' => $this->tenant2->id]);

        $this->kb1 = KnowledgeBase::factory()->create(['bot_id' => $this->bot1->id]);
        $this->kb2 = KnowledgeBase::factory()->create(['bot_id' => $this->bot2->id]);
    }

    /** @test */
    public function super_admin_can_view_any_knowledge_base()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($superAdmin->can('view', $this->kb1));
        $this->assertTrue($superAdmin->can('view', $this->kb2));
    }

    /** @test */
    public function admin_can_view_knowledge_bases_from_their_tenant()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('view', $this->kb1));
        $this->assertFalse($admin->can('view', $this->kb2));
    }

    /** @test */
    public function supervisor_can_view_knowledge_bases_from_their_tenant()
    {
        $supervisor = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $supervisor->assignRole('supervisor');

        $this->assertTrue($supervisor->can('view', $this->kb1));
        $this->assertFalse($supervisor->can('view', $this->kb2));
    }

    /** @test */
    public function agent_with_bot_access_can_view_knowledge_base()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        // Asignar agente al bot
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $agent->id,
            'can_chat' => true,
        ]);

        $agent->refresh();

        $this->assertTrue($agent->can('view', $this->kb1));
    }

    /** @test */
    public function agent_without_bot_access_cannot_view_knowledge_base()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        $this->assertFalse($agent->can('view', $this->kb1));
    }

    /** @test */
    public function only_admin_and_super_admin_can_create_knowledge_base()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        $this->assertTrue($superAdmin->can('create', KnowledgeBase::class));
        $this->assertTrue($admin->can('create', KnowledgeBase::class));
        $this->assertFalse($agent->can('create', KnowledgeBase::class));
    }

    /** @test */
    public function admin_can_update_knowledge_base_configuration()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('update', $this->kb1));
        $this->assertFalse($admin->can('update', $this->kb2));
    }

    /** @test */
    public function user_with_can_manage_on_bot_can_update_knowledge_base()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_manage' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('update', $this->kb1));
    }

    /** @test */
    public function only_admin_and_super_admin_can_delete_knowledge_base()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        $this->assertTrue($superAdmin->can('delete', $this->kb1));
        $this->assertTrue($admin->can('delete', $this->kb1));
        $this->assertFalse($agent->can('delete', $this->kb1));
    }

    /** @test */
    public function admin_can_train_knowledge_base()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('train', $this->kb1));
    }

    /** @test */
    public function user_with_can_train_kb_permission_can_train_knowledge_base()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_train_kb' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('train', $this->kb1));
    }

    /** @test */
    public function user_without_can_train_kb_permission_cannot_train_knowledge_base()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_chat' => true,
            'can_train_kb' => false,
        ]);

        $user->refresh();

        $this->assertFalse($user->can('train', $this->kb1));
    }

    /** @test */
    public function supervisor_cannot_train_knowledge_base_by_default()
    {
        $supervisor = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $supervisor->assignRole('supervisor');

        // Supervisor puede ver, pero no entrenar sin permiso explícito
        $this->assertFalse($supervisor->can('train', $this->kb1));
    }

    /** @test */
    public function any_user_with_bot_access_can_view_documents()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $agent->id,
            'can_chat' => true,
        ]);

        $agent->refresh();

        $this->assertTrue($agent->can('viewDocuments', $this->kb1));
    }

    /** @test */
    public function user_with_can_delete_data_can_delete_documents()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_delete_data' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('deleteDocuments', $this->kb1));
    }

    /** @test */
    public function user_with_can_train_kb_can_delete_documents()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_train_kb' => true,
            'can_delete_data' => false,
        ]);

        $user->refresh();

        // can_train_kb también permite borrar documentos
        $this->assertTrue($user->can('deleteDocuments', $this->kb1));
    }

    /** @test */
    public function user_without_delete_permissions_cannot_delete_documents()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_chat' => true,
            'can_delete_data' => false,
            'can_train_kb' => false,
        ]);

        $user->refresh();

        $this->assertFalse($user->can('deleteDocuments', $this->kb1));
    }

    /** @test */
    public function any_user_with_bot_access_can_download_documents()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $agent->id,
            'can_chat' => true,
        ]);

        $agent->refresh();

        $this->assertTrue($agent->can('downloadDocuments', $this->kb1));
    }

    /** @test */
    public function admin_can_view_metrics()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('viewMetrics', $this->kb1));
    }

    /** @test */
    public function supervisor_can_view_metrics()
    {
        $supervisor = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $supervisor->assignRole('supervisor');

        $this->assertTrue($supervisor->can('viewMetrics', $this->kb1));
    }

    /** @test */
    public function user_with_can_view_analytics_can_view_metrics()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_view_analytics' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('viewMetrics', $this->kb1));
    }

    /** @test */
    public function user_without_can_view_analytics_cannot_view_metrics()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_chat' => true,
            'can_view_analytics' => false,
        ]);

        $user->refresh();

        $this->assertFalse($user->can('viewMetrics', $this->kb1));
    }

    /** @test */
    public function user_cannot_access_knowledge_base_from_different_tenant()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertFalse($admin->can('view', $this->kb2));
        $this->assertFalse($admin->can('update', $this->kb2));
        $this->assertFalse($admin->can('train', $this->kb2));
        $this->assertFalse($admin->can('deleteDocuments', $this->kb2));
    }

    /** @test */
    public function only_super_admin_can_force_delete_knowledge_base()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($superAdmin->can('forceDelete', $this->kb1));
        $this->assertFalse($admin->can('forceDelete', $this->kb1));
    }
}