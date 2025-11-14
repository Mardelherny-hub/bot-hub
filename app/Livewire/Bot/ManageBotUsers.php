<?php

namespace App\Livewire\Bot;

use App\Models\Bot;
use App\Models\BotUser;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * ManageBotUsers Component
 * 
 * Componente Livewire para gestionar usuarios asignados a un bot.
 * Permite asignar/remover usuarios y configurar permisos granulares.
 * 
 * FUNCIONALIDADES:
 * - Listar usuarios asignados al bot con sus permisos
 * - Asignar nuevos usuarios con permisos específicos
 * - Actualizar permisos de usuarios existentes
 * - Remover usuarios del bot
 * 
 * AUTORIZACIÓN:
 * Solo admin del tenant y super_admin pueden gestionar usuarios.
 * Se valida con BotPolicy::manageUsers()
 * 
 * USO EN BLADE:
 * <livewire:bot.manage-bot-users :bot="$bot" />
 */
class ManageBotUsers extends Component
{
    use AuthorizesRequests;

    /**
     * Bot a gestionar
     */
    public Bot $bot;

    /**
     * Usuario seleccionado para asignar
     */
    public ?int $selectedUserId = null;

    /**
     * Permisos para el nuevo usuario
     */
    public array $permissions = [
        'can_manage' => false,
        'can_view_analytics' => false,
        'can_chat' => false,
        'can_train_kb' => false,
        'can_delete_data' => false,
    ];

    /**
     * Mensajes de feedback
     */
    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    /**
     * Reglas de validación
     */
    protected $rules = [
        'selectedUserId' => 'required|exists:users,id',
        'permissions.can_manage' => 'boolean',
        'permissions.can_view_analytics' => 'boolean',
        'permissions.can_chat' => 'boolean',
        'permissions.can_train_kb' => 'boolean',
        'permissions.can_delete_data' => 'boolean',
    ];

    /**
     * Mensajes de validación personalizados
     */
    protected $messages = [
        'selectedUserId.required' => 'Debe seleccionar un usuario.',
        'selectedUserId.exists' => 'El usuario seleccionado no existe.',
    ];

    /**
     * Montar componente y verificar autorización
     */
    public function mount(Bot $bot): void
    {
        $this->bot = $bot;
        
        // Verificar autorización para gestionar usuarios del bot
        $this->authorize('manageUsers', $bot);
    }

    /**
     * Obtener usuarios ya asignados al bot
     */
    public function getAssignedUsersProperty()
    {
        return $this->bot->users()
            ->withPivot([
                'can_manage',
                'can_view_analytics',
                'can_chat',
                'can_train_kb',
                'can_delete_data',
                'assigned_at'
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtener usuarios disponibles para asignar (del mismo tenant)
     */
    public function getAvailableUsersProperty()
    {
        // Usuarios del mismo tenant que NO están asignados al bot
        // Excluir super_admin (no tienen tenant_id)
        return User::where('tenant_id', $this->bot->tenant_id)
            ->whereNotIn('id', $this->assignedUsers->pluck('id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Asignar usuario al bot con permisos
     */
    public function assignUser(): void
    {
        $this->validate();

        try {
            // Verificar que el usuario no esté ya asignado
            if ($this->bot->users()->where('user_id', $this->selectedUserId)->exists()) {
                $this->errorMessage = 'El usuario ya está asignado a este bot.';
                return;
            }

            // Asignar usuario con permisos
            $this->bot->users()->attach($this->selectedUserId, [
                'can_manage' => $this->permissions['can_manage'],
                'can_view_analytics' => $this->permissions['can_view_analytics'],
                'can_chat' => $this->permissions['can_chat'],
                'can_train_kb' => $this->permissions['can_train_kb'],
                'can_delete_data' => $this->permissions['can_delete_data'],
                'assigned_at' => now(),
            ]);

            // Obtener nombre del usuario para mensaje
            $user = User::find($this->selectedUserId);

            $this->successMessage = "Usuario {$user->name} asignado correctamente.";
            
            // Resetear formulario
            $this->reset(['selectedUserId', 'permissions', 'errorMessage']);
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al asignar usuario: ' . $e->getMessage();
        }
    }

    /**
     * Actualizar permisos de un usuario
     */
    public function updatePermissions(int $userId, array $permissions): void
    {
        try {
            $this->bot->users()->updateExistingPivot($userId, [
                'can_manage' => $permissions['can_manage'] ?? false,
                'can_view_analytics' => $permissions['can_view_analytics'] ?? false,
                'can_chat' => $permissions['can_chat'] ?? false,
                'can_train_kb' => $permissions['can_train_kb'] ?? false,
                'can_delete_data' => $permissions['can_delete_data'] ?? false,
            ]);

            $user = User::find($userId);
            $this->successMessage = "Permisos de {$user->name} actualizados correctamente.";
            $this->errorMessage = null;
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al actualizar permisos: ' . $e->getMessage();
        }
    }

    /**
     * Remover usuario del bot
     */
    public function removeUser(int $userId): void
    {
        try {
            $user = User::find($userId);
            
            $this->bot->users()->detach($userId);
            
            $this->successMessage = "Usuario {$user->name} removido correctamente.";
            $this->errorMessage = null;
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al remover usuario: ' . $e->getMessage();
        }
    }

    /**
     * Otorgar todos los permisos a un usuario
     */
    public function grantAllPermissions(int $userId): void
    {
        $this->updatePermissions($userId, [
            'can_manage' => true,
            'can_view_analytics' => true,
            'can_chat' => true,
            'can_train_kb' => true,
            'can_delete_data' => true,
        ]);
    }

    /**
     * Revocar todos los permisos de un usuario
     */
    public function revokeAllPermissions(int $userId): void
    {
        $this->updatePermissions($userId, [
            'can_manage' => false,
            'can_view_analytics' => false,
            'can_chat' => false,
            'can_train_kb' => false,
            'can_delete_data' => false,
        ]);
    }

    /**
     * Limpiar mensajes
     */
    public function clearMessages(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    /**
     * Renderizar componente
     */
    public function render()
    {
        return view('livewire.bot.manage-bot-users');
    }
}