<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mis Bots de WhatsApp') }}
            </h2>
            @if($bots->total() < $tenant->monthly_bot_limit)
                <a href="{{ route('tenant.bots.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Crear Bot
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                    Límite Alcanzado ({{ $tenant->monthly_bot_limit }})
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Mensajes de éxito/error --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif

            {{-- Filtros de búsqueda --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('tenant.bots.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Búsqueda --}}
                            <div class="md:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                                <input type="text" 
                                       name="search" 
                                       id="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Nombre, teléfono o descripción..." 
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            {{-- Filtro por Estado --}}
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="is_active" 
                                        id="is_active"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Activos</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" 
                                    class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Filtrar
                            </button>
                            <a href="{{ route('tenant.bots.index') }}" 
                               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Grid de bots --}}
            @if($bots->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay bots</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if(request()->has('search') || request()->has('is_active'))
                                No se encontraron bots con los filtros aplicados.
                            @else
                                Comienza creando tu primer bot de WhatsApp.
                            @endif
                        </p>
                        @if(!request()->has('search') && !request()->has('is_active'))
                            <div class="mt-6">
                                <a href="{{ route('tenant.bots.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Crear Mi Primer Bot
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($bots as $bot)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition">
                            <div class="p-6">
                                {{-- Estado --}}
                                <div class="flex items-center justify-between mb-4">
                                    @if ($bot->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <span class="w-2 h-2 bg-green-600 rounded-full mr-1 mt-0.5 animate-pulse"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inactivo
                                        </span>
                                    @endif
                                    <span class="text-xs text-gray-500">{{ $bot->ai_model }}</span>
                                </div>

                                {{-- Nombre y descripción --}}
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $bot->name }}</h3>
                                @if($bot->description)
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $bot->description }}</p>
                                @endif

                                {{-- Teléfono --}}
                                <div class="flex items-center text-sm text-gray-500 mb-4">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $bot->phone_number }}
                                </div>

                                {{-- Idioma --}}
                                <div class="flex items-center text-xs text-gray-500 mb-4">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                    </svg>
                                    {{ strtoupper($bot->language) }}
                                    @if($bot->use_knowledge_base)
                                        <span class="ml-2 px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full text-xs">
                                            KB Activa
                                        </span>
                                    @endif
                                </div>

                                {{-- Acciones --}}
                                <div class="flex gap-2 pt-4 border-t border-gray-200">
                                    <a href="{{ route('tenant.bots.show', $bot) }}" 
                                       class="flex-1 text-center px-3 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition">
                                        Ver Detalles
                                    </a>
                                    <a href="{{ route('tenant.bots.edit', $bot) }}" 
                                       class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300 transition"
                                       title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Paginación --}}
                @if ($bots->hasPages())
                    <div class="mt-6">
                        {{ $bots->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>