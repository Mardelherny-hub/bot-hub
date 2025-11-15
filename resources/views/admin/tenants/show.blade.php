<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalles del Tenant') }}: {{ $tenant->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.tenants.edit', $tenant) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('admin.tenants.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Mensajes de éxito/error --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif

            {{-- Acciones Rápidas --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones Rápidas</h3>
                    <div class="flex flex-wrap gap-3">
                        @if ($tenant->subscription_status === 'active')
                            <form method="POST" action="{{ route('admin.tenants.suspend', $tenant) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('¿Estás seguro de suspender este tenant?')"
                                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Suspender Suscripción
                                </button>
                            </form>
                        @elseif ($tenant->subscription_status === 'suspended')
                            <form method="POST" action="{{ route('admin.tenants.activate', $tenant) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Reactivar Suscripción
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('¿Estás seguro de eliminar este tenant? Esta acción no se puede deshacer.')"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Eliminar Tenant
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Información General --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Información Básica --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $tenant->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Slug</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">/{{ $tenant->slug }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a href="mailto:{{ $tenant->email }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $tenant->email }}
                                    </a>
                                </dd>
                            </div>
                            @if ($tenant->phone)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="tel:{{ $tenant->phone }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $tenant->phone }}
                                        </a>
                                    </dd>
                                </div>
                            @endif
                            @if ($tenant->website)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Sitio Web</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="{{ $tenant->website }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $tenant->website }}
                                            <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">White Label</dt>
                                <dd class="mt-1">
                                    @if ($tenant->is_white_label)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Activado
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            No
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Suscripción --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Suscripción</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Plan</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $tenant->subscription_plan === 'enterprise' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $tenant->subscription_plan === 'professional' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $tenant->subscription_plan === 'starter' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($tenant->subscription_plan) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $tenant->subscription_status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $tenant->subscription_status === 'trial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $tenant->subscription_status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $tenant->subscription_status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($tenant->subscription_status) }}
                                    </span>
                                </dd>
                            </div>
                            @if ($tenant->subscription_started_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Inicio</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $tenant->subscription_started_at->format('d/m/Y') }}</dd>
                                </div>
                            @endif
                            @if ($tenant->subscription_ends_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Vencimiento</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $tenant->subscription_ends_at->format('d/m/Y') }}
                                        @if ($tenant->subscription_ends_at->isPast())
                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Vencida
                                            </span>
                                        @elseif ($tenant->subscription_ends_at->diffInDays(now()) <= 7)
                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Por vencer
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Creado</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $tenant->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Límites y Uso --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Límites del Plan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        {{-- Conversaciones --}}
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 mb-2">Conversaciones/mes</dt>
                            <dd class="text-3xl font-bold text-gray-900">{{ number_format($tenant->monthly_conversation_limit) }}</dd>
                            <p class="mt-1 text-xs text-gray-500">límite mensual</p>
                        </div>

                        {{-- Bots --}}
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 mb-2">Bots</dt>
                            <dd class="text-3xl font-bold text-gray-900">
                                {{ $tenant->bots_count }} / {{ $tenant->monthly_bot_limit }}
                            </dd>
                            <p class="mt-1 text-xs text-gray-500">en uso / límite</p>
                        </div>

                        {{-- Usuarios --}}
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500 mb-2">Usuarios</dt>
                            <dd class="text-3xl font-bold text-gray-900">
                                {{ $tenant->users_count }} / {{ $tenant->monthly_user_limit }}
                            </dd>
                            <p class="mt-1 text-xs text-gray-500">en uso / límite</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Usuarios Recientes --}}
            @if ($tenant->users->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Usuarios Recientes (últimos 10)</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Último Login</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($tenant->users as $user)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucfirst($user->roles->first()?->name ?? 'N/A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($user->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Activo
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Inactivo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $user->last_login_at?->diffForHumans() ?? 'Nunca' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Bots Recientes --}}
            @if ($tenant->bots->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Bots Recientes (últimos 10)</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bot</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($tenant->bots as $bot)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $bot->name }}</div>
                                                @if ($bot->description)
                                                    <div class="text-sm text-gray-500">{{ Str::limit($bot->description, 50) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $bot->phone_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($bot->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Activo
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Inactivo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $bot->created_at->format('d/m/Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>