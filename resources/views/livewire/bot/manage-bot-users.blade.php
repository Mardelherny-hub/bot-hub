<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Gestionar Usuarios del Bot
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Asigna usuarios y configura permisos específicos para este bot
            </p>
        </div>
    </div>

    {{-- Mensajes de feedback --}}
    @if($successMessage)
        <div class="flex items-center gap-3 rounded-lg bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
            <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-sm text-green-800 dark:text-green-200">{{ $successMessage }}</p>
            <button wire:click="clearMessages" class="ml-auto text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if($errorMessage)
        <div class="flex items-center gap-3 rounded-lg bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
            <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-red-800 dark:text-red-200">{{ $errorMessage }}</p>
            <button wire:click="clearMessages" class="ml-auto text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Usuarios Asignados --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                Usuarios Asignados ({{ $this->assignedUsers->count() }})
            </h4>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($this->assignedUsers as $user)
                <div class="px-6 py-4">
                    <div class="flex items-start justify-between">
                        {{-- Info del usuario --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                                    @if($user->pivot->assigned_at)
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                            Asignado: {{ $user->pivot->assigned_at->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Permisos --}}
                            <div class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-3">
                                <label class="flex items-center gap-2 text-sm">
                                    <input 
                                        type="checkbox" 
                                        wire:change="updatePermissions({{ $user->id }}, { can_manage: $event.target.checked, can_view_analytics: {{ $user->pivot->can_view_analytics ? 'true' : 'false' }}, can_chat: {{ $user->pivot->can_chat ? 'true' : 'false' }}, can_train_kb: {{ $user->pivot->can_train_kb ? 'true' : 'false' }}, can_delete_data: {{ $user->pivot->can_delete_data ? 'true' : 'false' }} })"
                                        {{ $user->pivot->can_manage ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <span class="text-gray-700 dark:text-gray-300">Gestionar</span>
                                </label>

                                <label class="flex items-center gap-2 text-sm">
                                    <input 
                                        type="checkbox" 
                                        wire:change="updatePermissions({{ $user->id }}, { can_manage: {{ $user->pivot->can_manage ? 'true' : 'false' }}, can_view_analytics: $event.target.checked, can_chat: {{ $user->pivot->can_chat ? 'true' : 'false' }}, can_train_kb: {{ $user->pivot->can_train_kb ? 'true' : 'false' }}, can_delete_data: {{ $user->pivot->can_delete_data ? 'true' : 'false' }} })"
                                        {{ $user->pivot->can_view_analytics ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <span class="text-gray-700 dark:text-gray-300">Analytics</span>
                                </label>

                                <label class="flex items-center gap-2 text-sm">
                                    <input 
                                        type="checkbox" 
                                        wire:change="updatePermissions({{ $user->id }}, { can_manage: {{ $user->pivot->can_manage ? 'true' : 'false' }}, can_view_analytics: {{ $user->pivot->can_view_analytics ? 'true' : 'false' }}, can_chat: $event.target.checked, can_train_kb: {{ $user->pivot->can_train_kb ? 'true' : 'false' }}, can_delete_data: {{ $user->pivot->can_delete_data ? 'true' : 'false' }} })"
                                        {{ $user->pivot->can_chat ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <span class="text-gray-700 dark:text-gray-300">Chatear</span>
                                </label>

                                <label class="flex items-center gap-2 text-sm">
                                    <input 
                                        type="checkbox" 
                                        wire:change="updatePermissions({{ $user->id }}, { can_manage: {{ $user->pivot->can_manage ? 'true' : 'false' }}, can_view_analytics: {{ $user->pivot->can_view_analytics ? 'true' : 'false' }}, can_chat: {{ $user->pivot->can_chat ? 'true' : 'false' }}, can_train_kb: $event.target.checked, can_delete_data: {{ $user->pivot->can_delete_data ? 'true' : 'false' }} })"
                                        {{ $user->pivot->can_train_kb ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <span class="text-gray-700 dark:text-gray-300">Entrenar KB</span>
                                </label>

                                <label class="flex items-center gap-2 text-sm">
                                    <input 
                                        type="checkbox" 
                                        wire:change="updatePermissions({{ $user->id }}, { can_manage: {{ $user->pivot->can_manage ? 'true' : 'false' }}, can_view_analytics: {{ $user->pivot->can_view_analytics ? 'true' : 'false' }}, can_chat: {{ $user->pivot->can_chat ? 'true' : 'false' }}, can_train_kb: {{ $user->pivot->can_train_kb ? 'true' : 'false' }}, can_delete_data: $event.target.checked })"
                                        {{ $user->pivot->can_delete_data ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <span class="text-gray-700 dark:text-gray-300">Eliminar</span>
                                </label>
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex items-center gap-2 ml-4">
                            <button 
                                wire:click="grantAllPermissions({{ $user->id }})"
                                class="text-xs px-3 py-1 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded hover:bg-green-100 dark:hover:bg-green-900/30"
                                title="Otorgar todos los permisos"
                            >
                                Todo
                            </button>
                            <button 
                                wire:click="revokeAllPermissions({{ $user->id }})"
                                class="text-xs px-3 py-1 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 rounded hover:bg-orange-100 dark:hover:bg-orange-900/30"
                                title="Revocar todos los permisos"
                            >
                                Ninguno
                            </button>
                            <button 
                                wire:click="removeUser({{ $user->id }})"
                                wire:confirm="¿Estás seguro de remover a {{ $user->name }} de este bot?"
                                class="text-xs px-3 py-1 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded hover:bg-red-100 dark:hover:bg-red-900/30"
                                title="Remover usuario"
                            >
                                Remover
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No hay usuarios asignados a este bot</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Asignar Nuevo Usuario --}}
    @if($this->availableUsers->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Asignar Nuevo Usuario
                </h4>
            </div>

            <div class="p-6 space-y-4">
                {{-- Selector de usuario --}}
                <div>
                    <label for="selectedUserId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Seleccionar Usuario
                    </label>
                    <select 
                        wire:model="selectedUserId" 
                        id="selectedUserId"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">-- Seleccionar usuario --</option>
                        @foreach($this->availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    @error('selectedUserId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Checkboxes de permisos --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Permisos
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                wire:model="permissions.can_manage"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Gestionar:</span> Configurar bot (nombre, teléfono, personalidad)
                            </span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                wire:model="permissions.can_view_analytics"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Ver Analytics:</span> Acceso a métricas y reportes
                            </span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                wire:model="permissions.can_chat"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Chatear:</span> Usar chat en vivo (handoff)
                            </span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                wire:model="permissions.can_train_kb"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Entrenar KB:</span> Subir documentos y entrenar knowledge base
                            </span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                wire:model="permissions.can_delete_data"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Eliminar Datos:</span> Borrar conversaciones y documentos
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Botón asignar --}}
                <div class="flex justify-end pt-4">
                    <button 
                        wire:click="assignUser"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!selectedUserId"
                    >
                        Asignar Usuario
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                No hay más usuarios disponibles para asignar a este bot
            </p>
        </div>
    @endif
</div>