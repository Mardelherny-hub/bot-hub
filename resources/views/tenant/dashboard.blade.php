<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de') }} {{ $tenant->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Bienvenida --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-2">¡Bienvenido, {{ auth()->user()->name }}!</h3>
                    <p class="text-gray-600">Gestiona tus bots de WhatsApp desde aquí.</p>
                </div>
            </div>

            {{-- Información del Plan --}}
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold">Plan {{ ucfirst($tenant->subscription_plan) }}</h3>
                            <p class="text-indigo-100 mt-1">
                                Estado: 
                                <span class="font-semibold">{{ ucfirst($tenant->subscription_status) }}</span>
                            </p>
                            @if($tenant->subscription_ends_at)
                                <p class="text-indigo-100 text-sm mt-1">
                                    Vence: {{ $tenant->subscription_ends_at->format('d/m/Y') }}
                                </p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-indigo-100">Límites del plan:</p>
                            <p class="text-lg font-bold">{{ $tenant->monthly_bot_limit }} bots</p>
                            <p class="text-lg font-bold">{{ number_format($tenant->monthly_conversation_limit) }} conversaciones/mes</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estadísticas Rápidas --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                
                {{-- Mis Bots --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Mis Bots</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-3xl font-bold text-gray-900">
                                            {{ \App\Models\Bot::count() }}
                                        </div>
                                        <div class="ml-2 text-sm text-gray-500">
                                            / {{ $tenant->monthly_bot_limit }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bots Activos --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Activos</dt>
                                    <dd class="text-3xl font-bold text-gray-900">
                                        {{ \App\Models\Bot::where('is_active', true)->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Conversaciones (placeholder) --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Conversaciones</dt>
                                    <dd class="text-3xl font-bold text-gray-900">
                                        {{ \App\Models\Conversation::count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Accesos Rápidos --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Accesos Rápidos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        {{-- Gestionar Bots --}}
                        <a href="{{ route('tenant.bots.index') }}" 
                           class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-900">Gestionar Bots</h4>
                                <p class="text-sm text-gray-500">Ver y administrar tus bots</p>
                            </div>
                        </a>

                        {{-- Crear Bot --}}
                        @if(\App\Models\Bot::count() < $tenant->monthly_bot_limit)
                            <a href="{{ route('tenant.bots.create') }}" 
                               class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">Crear Nuevo Bot</h4>
                                    <p class="text-sm text-gray-500">Configura un nuevo bot de WhatsApp</p>
                                </div>
                            </a>
                        @else
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg opacity-50 cursor-not-allowed">
                                <div class="flex-shrink-0 bg-gray-400 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">Límite Alcanzado</h4>
                                    <p class="text-sm text-gray-500">Actualiza tu plan para crear más bots</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Mis Bots Recientes --}}
            @php
                $recentBots = \App\Models\Bot::latest()->limit(5)->get();
            @endphp

            @if ($recentBots->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Mis Bots Recientes</h3>
                            <a href="{{ route('tenant.bots.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                Ver todos →
                            </a>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bot</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modelo IA</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($recentBots as $bot)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $bot->name }}</div>
                                                @if($bot->description)
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
                                                {{ $bot->ai_model }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('tenant.bots.show', $bot) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">
                                                    Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tienes bots aún</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza creando tu primer bot de WhatsApp.</p>
                        <div class="mt-6">
                            <a href="{{ route('tenant.bots.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Crear Bot
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>