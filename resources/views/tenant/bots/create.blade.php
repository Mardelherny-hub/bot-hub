<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Crear Nuevo Bot') }}
            </h2>
            <a href="{{ route('tenant.bots.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('tenant.bots.store') }}" class="space-y-6">
                        @csrf

                        {{-- Información Básica --}}
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                {{-- Nombre --}}
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre del Bot <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name') }}"
                                           required
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                                           placeholder="Ej: Bot de Atención al Cliente">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Descripción --}}
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                        Descripción
                                    </label>
                                    <textarea name="description" 
                                              id="description" 
                                              rows="3"
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                                              placeholder="Descripción del propósito de este bot...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Número de WhatsApp --}}
                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
                                        Número de WhatsApp <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" 
                                           name="phone_number" 
                                           id="phone_number" 
                                           value="{{ old('phone_number') }}"
                                           required
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone_number') border-red-500 @enderror"
                                           placeholder="+5492231234567">
                                    <p class="mt-1 text-xs text-gray-500">Formato internacional (ej: +5492231234567)</p>
                                    @error('phone_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Idioma --}}
                                <div>
                                    <label for="language" class="block text-sm font-medium text-gray-700 mb-1">
                                        Idioma Principal
                                    </label>
                                    <select name="language" 
                                            id="language"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('language') border-red-500 @enderror">
                                        <option value="es" {{ old('language', 'es') === 'es' ? 'selected' : '' }}>Español</option>
                                        <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="pt" {{ old('language') === 'pt' ? 'selected' : '' }}>Português</option>
                                        <option value="fr" {{ old('language') === 'fr' ? 'selected' : '' }}>Français</option>
                                        <option value="de" {{ old('language') === 'de' ? 'selected' : '' }}>Deutsch</option>
                                        <option value="it" {{ old('language') === 'it' ? 'selected' : '' }}>Italiano</option>
                                    </select>
                                    @error('language')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        {{-- Configuración de IA --}}
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Inteligencia Artificial</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                {{-- Modelo de IA --}}
                                <div>
                                    <label for="ai_model" class="block text-sm font-medium text-gray-700 mb-1">
                                        Modelo de IA <span class="text-red-500">*</span>
                                    </label>
                                    <select name="ai_model" 
                                            id="ai_model"
                                            required
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('ai_model') border-red-500 @enderror">
                                        <option value="gpt-4" {{ old('ai_model', 'gpt-4') === 'gpt-4' ? 'selected' : '' }}>GPT-4 (Recomendado)</option>
                                        <option value="gpt-4-turbo" {{ old('ai_model') === 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo (Más rápido)</option>
                                        <option value="gpt-3.5-turbo" {{ old('ai_model') === 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo (Económico)</option>
                                    </select>
                                    @error('ai_model')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Temperature --}}
                                <div>
                                    <label for="temperature" class="block text-sm font-medium text-gray-700 mb-1">
                                        Creatividad (Temperature)
                                    </label>
                                    <input type="number" 
                                           name="temperature" 
                                           id="temperature" 
                                           value="{{ old('temperature', '0.70') }}"
                                           step="0.01"
                                           min="0"
                                           max="2"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('temperature') border-red-500 @enderror">
                                    <p class="mt-1 text-xs text-gray-500">0.0 = Preciso, 2.0 = Creativo (Recomendado: 0.7)</p>
                                    @error('temperature')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Personalidad --}}
                                <div class="md:col-span-2">
                                    <label for="personality" class="block text-sm font-medium text-gray-700 mb-1">
                                        Personalidad del Bot
                                    </label>
                                    <textarea name="personality" 
                                              id="personality" 
                                              rows="3"
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('personality') border-red-500 @enderror"
                                              placeholder="Ej: Eres un asistente amigable y profesional que ayuda a los clientes...">{{ old('personality') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">Define cómo debe comportarse el bot</p>
                                    @error('personality')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Instrucciones --}}
                                <div class="md:col-span-2">
                                    <label for="instructions" class="block text-sm font-medium text-gray-700 mb-1">
                                        Instrucciones Específicas
                                    </label>
                                    <textarea name="instructions" 
                                              id="instructions" 
                                              rows="4"
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('instructions') border-red-500 @enderror"
                                              placeholder="Ej: Siempre saluda, sé breve, ofrece transferir a humano si no sabes algo...">{{ old('instructions') }}</textarea>
                                    @error('instructions')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        {{-- Horario de Atención --}}
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Horario de Atención</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                {{-- Hora de inicio --}}
                                <div>
                                    <label for="business_hours_start" class="block text-sm font-medium text-gray-700 mb-1">
                                        Hora de Inicio
                                    </label>
                                    <input type="time" 
                                           name="business_hours_start" 
                                           id="business_hours_start" 
                                           value="{{ old('business_hours_start', '09:00') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('business_hours_start') border-red-500 @enderror">
                                    @error('business_hours_start')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Hora de fin --}}
                                <div>
                                    <label for="business_hours_end" class="block text-sm font-medium text-gray-700 mb-1">
                                        Hora de Fin
                                    </label>
                                    <input type="time" 
                                           name="business_hours_end" 
                                           id="business_hours_end" 
                                           value="{{ old('business_hours_end', '18:00') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('business_hours_end') border-red-500 @enderror">
                                    @error('business_hours_end')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Días laborables --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Días de Atención
                                    </label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        @php
                                            $days = [
                                                'monday' => 'Lunes',
                                                'tuesday' => 'Martes',
                                                'wednesday' => 'Miércoles',
                                                'thursday' => 'Jueves',
                                                'friday' => 'Viernes',
                                                'saturday' => 'Sábado',
                                                'sunday' => 'Domingo',
                                            ];
                                            $defaultDays = old('business_days', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
                                        @endphp
                                        @foreach($days as $value => $label)
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="business_days[]" 
                                                       value="{{ $value }}"
                                                       {{ in_array($value, $defaultDays) ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('business_days')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Mensaje fuera de horario --}}
                                <div class="md:col-span-2">
                                    <label for="out_of_hours_message" class="block text-sm font-medium text-gray-700 mb-1">
                                        Mensaje Fuera de Horario
                                    </label>
                                    <textarea name="out_of_hours_message" 
                                              id="out_of_hours_message" 
                                              rows="2"
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('out_of_hours_message') border-red-500 @enderror"
                                              placeholder="Mensaje que se enviará fuera del horario de atención...">{{ old('out_of_hours_message') }}</textarea>
                                    @error('out_of_hours_message')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        {{-- Configuración Avanzada --}}
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración Avanzada</h3>
                            <div class="space-y-4">
                                
                                {{-- Bot activo --}}
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" 
                                               name="is_active" 
                                               id="is_active" 
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_active" class="font-medium text-gray-700">Bot Activo</label>
                                        <p class="text-gray-500">El bot responderá mensajes automáticamente</p>
                                    </div>
                                </div>

                                {{-- Transferir a humano --}}
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" 
                                               name="fallback_to_human" 
                                               id="fallback_to_human" 
                                               value="1"
                                               {{ old('fallback_to_human', true) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="fallback_to_human" class="font-medium text-gray-700">Transferir a Humano</label>
                                        <p class="text-gray-500">Permite transferir conversaciones a un agente humano</p>
                                    </div>
                                </div>

                                {{-- Usar Knowledge Base --}}
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" 
                                               name="use_knowledge_base" 
                                               id="use_knowledge_base" 
                                               value="1"
                                               {{ old('use_knowledge_base') ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="use_knowledge_base" class="font-medium text-gray-700">Usar Base de Conocimiento (RAG)</label>
                                        <p class="text-gray-500">El bot usará documentos cargados para responder (disponible en Sprint 3)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('tenant.bots.index') }}" 
                               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Crear Bot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>