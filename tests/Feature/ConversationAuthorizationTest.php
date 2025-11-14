<?php

namespace Tests\Feature;

use App\Models\Bot;
use App\Models\BotUser;
use App\Models\Conversation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * ConversationAuthorizationTest
 * 
 * Tests de autorización para operaciones sobre Conversations.
 * 
 * COBERTURA:
 * - Autorización por roles
 * - Autorización por asignación (assigned_user_id)
 * - Autorización por acceso al bot
 * - Aislamiento por tenant
 */
class ConversationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant1;
    protected Tenant $tenant2;
    protected Bot $bot1;
    protected Bot $bot2;
    protected Conversation $conversation1;
    protected Conversation $conversation2;

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

        $this->conversation1 = Conversation::factory()->create([
            'bot_id' => $this->bot1->id,
        ]);

        $this->conversation2 = Conversation::factory()->create([
            'bot_id' => $this->bot2->id,
        ]);
    }

    /** @test */
    public function super_admin_can_view_any_conversation()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue($superAdmin->can('view', $this->conversation1));
        $this->assertTrue($superAdmin->can('view', $this->conversation2));
    }

    /** @test */
    public function admin_can_view_all_conversations_from_their_tenant()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('view', $this->conversation1));
        $this->assertFalse($admin->can('view', $this->conversation2));
    }

    /** @test */
    public function supervisor_can_view_all_conversations_from_their_tenant()
    {
        $supervisor = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $supervisor->assignRole('supervisor');

        $this->assertTrue($supervisor->can('view', $this->conversation1));
        $this->assertFalse($supervisor->can('view', $this->conversation2));
    }

    /** @test */
    public function agent_can_view_assigned_conversation()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        // Asignar conversación al agente
        $this->conversation1->update(['assigned_user_id' => $agent->id]);

        $this->assertTrue($agent->can('view', $this->conversation1));
    }

    /** @test */
    public function agent_can_view_conversation_if_has_access_to_bot()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        // Asignar agente al bot con can_chat
        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $agent->id,
            'can_chat' => true,
        ]);

        $agent->refresh();

        $this->assertTrue($agent->can('view', $this->conversation1));
    }

    /** @test */
    public function agent_cannot_view_conversation_without_bot_access()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        $this->assertFalse($agent->can('view', $this->conversation1));
    }

    /** @test */
    public function admin_can_update_any_conversation_in_their_tenant()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('update', $this->conversation1));
        $this->assertFalse($admin->can('update', $this->conversation2));
    }

    /** @test */
    public function supervisor_can_update_conversations_in_their_tenant()
    {
        $supervisor = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $supervisor->assignRole('supervisor');

        $this->assertTrue($supervisor->can('update', $this->conversation1));
    }

    /** @test */
    public function user_with_can_manage_on_bot_can_update_conversation()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_manage' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('update', $this->conversation1));
    }

    /** @test */
    public function admin_can_delete_conversations_in_their_tenant()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('delete', $this->conversation1));
    }

    /** @test */
    public function user_with_can_delete_data_permission_can_delete_conversation()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_delete_data' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('delete', $this->conversation1));
    }

    /** @test */
    public function user_without_can_delete_data_permission_cannot_delete_conversation()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_chat' => true,
            'can_delete_data' => false,
        ]);

        $user->refresh();

        $this->assertFalse($user->can('delete', $this->conversation1));
    }

    /** @test */
    public function assigned_user_can_reply_to_conversation()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        $this->conversation1->update(['assigned_user_id' => $agent->id]);

        $this->assertTrue($agent->can('reply', $this->conversation1));
    }

    /** @test */
    public function user_with_can_chat_permission_can_reply_to_conversation()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $agent->id,
            'can_chat' => true,
        ]);

        $agent->refresh();

        $this->assertTrue($agent->can('reply', $this->conversation1));
    }

    /** @test */
    public function user_without_can_chat_permission_cannot_reply_to_conversation()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $agent->id,
            'can_chat' => false,
            'can_view_analytics' => true,
        ]);

        $agent->refresh();

        $this->assertFalse($agent->can('reply', $this->conversation1));
    }

    /** @test */
    public function admin_can_assign_conversation_to_agent()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('assign', $this->conversation1));
    }

    /** @test */
    public function supervisor_can_assign_conversation_to_agent()
    {
        $supervisor = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $supervisor->assignRole('supervisor');

        $this->assertTrue($supervisor->can('assign', $this->conversation1));
    }

    /** @test */
    public function user_with_can_manage_on_bot_can_assign_conversation()
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $user->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $user->id,
            'can_manage' => true,
        ]);

        $user->refresh();

        $this->assertTrue($user->can('assign', $this->conversation1));
    }

    /** @test */
    public function regular_agent_cannot_assign_conversations()
    {
        $agent = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $agent->assignRole('agent');

        BotUser::factory()->create([
            'bot_id' => $this->bot1->id,
            'user_id' => $agent->id,
            'can_chat' => true,
        ]);

        $agent->refresh();

        $this->assertFalse($agent->can('assign', $this->conversation1));
    }

    /** @test */
    public function user_cannot_access_conversation_from_different_tenant()
    {
        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        // Conversación del tenant2
        $this->assertFalse($admin->can('view', $this->conversation2));
        $this->assertFalse($admin->can('update', $this->conversation2));
        $this->assertFalse($admin->can('delete', $this->conversation2));
        $this->assertFalse($admin->can('reply', $this->conversation2));
    }

    /** @test */
    public function only_super_admin_can_force_delete_conversation()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $admin = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $admin->assignRole('admin');

        $this->assertTrue($superAdmin->can('forceDelete', $this->conversation1));
        $this->assertFalse($admin->can('forceDelete', $this->conversation1));
    }
}