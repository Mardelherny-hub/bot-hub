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
 * BotUserPermissionsTest
 * 
 * Tests específicos para la tabla pivot bot_user y permisos granulares.
 * 
 * COBERTURA:
 * - Asignación de usuarios a bots
 * - Actualización de permisos
 * - Métodos helper del modelo BotUser
 * - Métodos de User para verificar permisos
 * - Factory states (fullAccess, chatOnly, etc)
 */
class BotUserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Bot $bot;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'agent']);

        $this->tenant = Tenant::factory()->create();
        $this->bot = Bot::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->user->assignRole('agent');
    }

    /** @test */
    public function can_assign_user_to_bot_with_custom_permissions()
    {
        $this->bot->users()->attach($this->user->id, [
            'can_manage' => true,
            'can_view_analytics' => false,
            'can_chat' => true,
            'can_train_kb' => false,
            'can_delete_data' => false,
        ]);

        $this->assertTrue($this->bot->users->contains($this->user->id));
        
        $pivot = $this->bot->users()->find($this->user->id)->pivot;
        
        $this->assertTrue($pivot->can_manage);
        $this->assertFalse($pivot->can_view_analytics);
        $this->assertTrue($pivot->can_chat);
        $this->assertFalse($pivot->can_train_kb);
        $this->assertFalse($pivot->can_delete_data);
    }

    /** @test */
    public function can_update_user_permissions_on_existing_assignment()
    {
        // Asignar con permisos iniciales
        $this->bot->users()->attach($this->user->id, [
            'can_manage' => false,
            'can_chat' => true,
        ]);

        // Actualizar permisos
        $this->bot->users()->updateExistingPivot($this->user->id, [
            'can_manage' => true,
            'can_view_analytics' => true,
        ]);

        $pivot = $this->bot->users()->find($this->user->id)->pivot;

        $this->assertTrue($pivot->can_manage);
        $this->assertTrue($pivot->can_view_analytics);
        $this->assertTrue($pivot->can_chat);
    }

    /** @test */
    public function can_remove_user_from_bot()
    {
        $this->bot->users()->attach($this->user->id);

        $this->assertTrue($this->bot->users->contains($this->user->id));

        $this->bot->users()->detach($this->user->id);

        $this->bot->refresh();
        
        $this->assertFalse($this->bot->users->contains($this->user->id));
    }

    /** @test */
    public function bot_user_factory_full_access_state_works()
    {
        $botUser = BotUser::factory()->fullAccess()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($botUser->can_manage);
        $this->assertTrue($botUser->can_view_analytics);
        $this->assertTrue($botUser->can_chat);
        $this->assertTrue($botUser->can_train_kb);
        $this->assertTrue($botUser->can_delete_data);
    }

    /** @test */
    public function bot_user_factory_chat_only_state_works()
    {
        $botUser = BotUser::factory()->chatOnly()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertFalse($botUser->can_manage);
        $this->assertFalse($botUser->can_view_analytics);
        $this->assertTrue($botUser->can_chat);
        $this->assertFalse($botUser->can_train_kb);
        $this->assertFalse($botUser->can_delete_data);
    }

    /** @test */
    public function bot_user_factory_read_only_state_works()
    {
        $botUser = BotUser::factory()->readOnly()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertFalse($botUser->can_manage);
        $this->assertTrue($botUser->can_view_analytics);
        $this->assertFalse($botUser->can_chat);
        $this->assertFalse($botUser->can_train_kb);
        $this->assertFalse($botUser->can_delete_data);
    }

    /** @test */
    public function bot_user_factory_supervisor_state_works()
    {
        $botUser = BotUser::factory()->supervisor()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertFalse($botUser->can_manage);
        $this->assertTrue($botUser->can_view_analytics);
        $this->assertTrue($botUser->can_chat);
        $this->assertFalse($botUser->can_train_kb);
        $this->assertFalse($botUser->can_delete_data);
    }

    /** @test */
    public function bot_user_has_all_permissions_method_works()
    {
        $botUser = BotUser::factory()->fullAccess()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($botUser->hasAllPermissions());

        $botUser->update(['can_chat' => false]);

        $this->assertFalse($botUser->hasAllPermissions());
    }

    /** @test */
    public function bot_user_has_any_permission_method_works()
    {
        $botUser = BotUser::factory()->noPermissions()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertFalse($botUser->hasAnyPermission());

        $botUser->update(['can_chat' => true]);

        $this->assertTrue($botUser->hasAnyPermission());
    }

    /** @test */
    public function bot_user_grant_all_permissions_method_works()
    {
        $botUser = BotUser::factory()->noPermissions()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertFalse($botUser->hasAnyPermission());

        $botUser->grantAllPermissions();

        $this->assertTrue($botUser->fresh()->hasAllPermissions());
    }

    /** @test */
    public function bot_user_revoke_all_permissions_method_works()
    {
        $botUser = BotUser::factory()->fullAccess()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($botUser->hasAllPermissions());

        $botUser->revokeAllPermissions();

        $this->assertFalse($botUser->fresh()->hasAnyPermission());
    }

    /** @test */
    public function user_can_manage_bot_method_works()
    {
        BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
            'can_manage' => true,
        ]);

        $this->user->refresh();

        $this->assertTrue($this->user->canManageBot($this->bot));
    }

    /** @test */
    public function user_can_chat_in_bot_method_works()
    {
        BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
            'can_chat' => true,
        ]);

        $this->user->refresh();

        $this->assertTrue($this->user->canChatInBot($this->bot));
    }

    /** @test */
    public function user_can_view_analytics_method_works()
    {
        BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
            'can_view_analytics' => true,
        ]);

        $this->user->refresh();

        $this->assertTrue($this->user->canViewAnalytics($this->bot));
    }

    /** @test */
    public function user_can_train_knowledge_base_method_works()
    {
        BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
            'can_train_kb' => true,
        ]);

        $this->user->refresh();

        $this->assertTrue($this->user->canTrainKnowledgeBase($this->bot));
    }

    /** @test */
    public function user_without_permissions_returns_false_for_all_checks()
    {
        BotUser::factory()->noPermissions()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        $this->user->refresh();

        $this->assertFalse($this->user->canManageBot($this->bot));
        $this->assertFalse($this->user->canChatInBot($this->bot));
        $this->assertFalse($this->user->canViewAnalytics($this->bot));
        $this->assertFalse($this->user->canTrainKnowledgeBase($this->bot));
    }

    /** @test */
    public function assigned_at_timestamp_is_set_correctly()
    {
        $now = now();

        $botUser = BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
            'assigned_at' => $now,
        ]);

        $this->assertEquals($now->format('Y-m-d H:i:s'), $botUser->assigned_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function can_query_bots_with_pivot_data()
    {
        BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
            'can_manage' => true,
            'can_chat' => false,
        ]);

        $this->user->refresh();

        $userBots = $this->user->bots()
            ->wherePivot('can_manage', true)
            ->get();

        $this->assertCount(1, $userBots);
        $this->assertEquals($this->bot->id, $userBots->first()->id);
    }

    /** @test */
    public function multiple_users_can_be_assigned_to_same_bot()
    {
        $user2 = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $user3 = User::factory()->create(['tenant_id' => $this->tenant->id]);

        BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $user2->id,
        ]);

        BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $user3->id,
        ]);

        $this->assertCount(3, $this->bot->users);
    }

    /** @test */
    public function user_can_be_assigned_to_multiple_bots()
    {
        $bot2 = Bot::factory()->create(['tenant_id' => $this->tenant->id]);
        $bot3 = Bot::factory()->create(['tenant_id' => $this->tenant->id]);

        BotUser::factory()->create([
            'bot_id' => $this->bot->id,
            'user_id' => $this->user->id,
        ]);

        BotUser::factory()->create([
            'bot_id' => $bot2->id,
            'user_id' => $this->user->id,
        ]);

        BotUser::factory()->create([
            'bot_id' => $bot3->id,
            'user_id' => $this->user->id,
        ]);

        $this->user->refresh();

        $this->assertCount(3, $this->user->bots);
    }
}