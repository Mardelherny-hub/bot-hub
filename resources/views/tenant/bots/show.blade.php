<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $bot->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('tenant.bots.edit', $bot) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('tenant.bots.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Ver Todos
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

            {{-- Estado y Acciones Rápidas --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            {{-- Estado --}}
                            @if ($bot->is_active)
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <span class="w-2 h-2 bg-green-600 rounded-full mr-2 mt-1 animate-pulse"></span>
                                    Bot Activo
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Bot Inactivo
                                </span>
                            @endif

                            {{-- Modelo de IA --}}
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                {{ $bot->ai_model }}
                            </span>

                            {{-- Knowledge Base --}}
                            @if($bot->use_knowledge_base)
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    KB Activa
                                </span>
                            @endif
                        </div>

                        {{-- Acciones Rápidas --}}
                        <div class="flex gap-2">
                            @if ($bot->is_active)
                                <form method="POST" action="{{ route('tenant.bots.deactivate', $bot) }}">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('¿Desactivar este bot?')"
                                            class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                        Desactivar
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('tenant.bots.activate', $bot) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        Activar
                                    </button>
                                </form>
                            @endif

                            <button type="button" 
                                    onclick="if(confirm('¿Estás seguro de que deseas eliminar este bot? Esta acción no se puede deshacer.')) { document.getElementById('delete-form').submit(); }"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Columna Principal (2/3) --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Información Básica --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                            
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $bot->name }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Número de WhatsApp</dt>
                                    <dd class="mt-1 text-sm text-gray-900 flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                        {{ $bot->phone_number }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Idioma</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ strtoupper($bot->language) }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Creado</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $bot->created_at->format('d/m/Y H:i') }}</dd>
                                </div>

                                @if($bot->description)
                                    <div class="md:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $bot->description }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Configuración de IA --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de IA</h3>
                            
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Modelo</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $bot->ai_model }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Temperature</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $bot->temperature }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tokens Máximos</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $bot->max_tokens }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Timeout Inactividad</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $bot->inactivity_timeout_minutes }} minutos</dd>
                                </div>

                                @if($bot->personality)
                                    <div class="md:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Personalidad</dt>
                                        <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $bot->personality }}</dd>
                                    </div>
                                @endif

                                @if($bot->instructions)
                                    <div class="md:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Instrucciones</dt>
                                        <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $bot->instructions }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Horario de Atención --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Horario de Atención</h3>
                            
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Horario</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ substr($bot->business_hours_start, 0, 5) }} - {{ substr($bot->business_hours_end, 0, 5) }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Días de Atención</dt>
                                    <dd class="mt-1">
                                        @php
                                            $dayLabels = [
                                                'monday' => 'Lunes',
                                                'tuesday' => 'Martes',
                                                'wednesday' => 'Miércoles',
                                                'thursday' => 'Jueves',
                                                'friday' => 'Viernes',
                                                'saturday' => 'Sábado',
                                                'sunday' => 'Domingo',
                                            ];
                                            $businessDays = $bot->business_days ?? [];
                                        @endphp
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($dayLabels as $value => $label)
                                                <span class="px-2 py-1 text-xs rounded {{ in_array($value, $businessDays) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                                    {{ $label }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </dd>
                                </div>

                                @if($bot->out_of_hours_message)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Mensaje Fuera de Horario</dt>
                                        <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $bot->out_of_hours_message }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Configuración Avanzada --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración Avanzada</h3>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-700">Transferir a Humano</span>
                                    @if($bot->fallback_to_human)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Activado
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Desactivado
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-700">Usar Knowledge Base</span>
                                    @if($bot->use_knowledge_base)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Activado
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Desactivado
                                        </span>
                                    @endif
                                </div>

                                @if($bot->use_knowledge_base)
                                    <div class="pl-4 space-y-2 text-sm text-gray-600">
                                        <div class="flex justify-between">
                                            <span>Resultados RAG:</span>
                                            <span class="font-medium">{{ $bot->knowledge_base_results }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Umbral de Similitud:</span>
                                            <span class="font-medium">{{ $bot->knowledge_base_threshold }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Columna Lateral (1/3) --}}
                <div class="space-y-6">
                    
                    {{-- Estadísticas --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Estadísticas</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-2">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Conversaciones</p>
                                        </div>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ $bot->conversations_count ?? 0 }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-2">
                                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Usuarios Asignados</p>
                                        </div>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ $bot->users_count ?? 0 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Integración WhatsApp --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Integración WhatsApp</h3>
                            
                            <dl class="space-y-3 text-sm">
                                @if($bot->whatsapp_business_account_id)
                                    <div>
                                        <dt class="text-gray-500">Business Account ID</dt>
                                        <dd class="mt-1 text-gray-900 font-mono text-xs break-all">{{ $bot->whatsapp_business_account_id }}</dd>
                                    </div>
                                @endif

                                @if($bot->whatsapp_phone_number_id)
                                    <div>
                                        <dt class="text-gray-500">Phone Number ID</dt>
                                        <dd class="mt-1 text-gray-900 font-mono text-xs break-all">{{ $bot->whatsapp_phone_number_id }}</dd>
                                    </div>
                                @endif

                                @if(!$bot->whatsapp_business_account_id && !$bot->whatsapp_phone_number_id)
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                        <p class="text-xs text-yellow-800">
                                            <strong>⚠️ Integración Pendiente</strong><br>
                                            Configura la conexión con WhatsApp Business API para activar este bot.
                                        </p>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Knowledge Base --}}
                    @if($bot->knowledgeBase)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Knowledge Base</h3>
                                <p class="text-sm text-gray-600 mb-3">Base de conocimiento configurada</p>
                                <a href="#" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    Gestionar Documentos →
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Knowledge Base</h3>
                                <p class="text-sm text-gray-600 mb-3">No hay knowledge base configurada</p>
                                <button class="text-sm text-gray-400 cursor-not-allowed">
                                    Disponible en Sprint 3
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Acciones Adicionales --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones</h3>
                            <div class="space-y-2">
                                <button class="w-full text-left px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm cursor-not-allowed opacity-50">
                                    Ver Analytics
                                    <span class="text-xs text-gray-500">(Sprint 4)</span>
                                </button>
                                <button class="w-full text-left px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm cursor-not-allowed opacity-50">
                                    Gestionar Usuarios
                                    <span class="text-xs text-gray-500">(Sprint 2)</span>
                                </button>
                                <button class="w-full text-left px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm cursor-not-allowed opacity-50">
                                    Ver Conversaciones
                                    <span class="text-xs text-gray-500">(Sprint 2)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulario de eliminación (oculto) --}}
    <form id="delete-form" 
          method="POST" 
          action="{{ route('tenant.bots.destroy', $bot) }}" 
          class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-app-layout>