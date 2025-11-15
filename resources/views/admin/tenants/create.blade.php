<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Crear Nuevo Tenant') }}
            </h2>
            <a href="{{ route('admin.tenants.index') }}" 
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
                    <form method="POST" action="{{ route('admin.tenants.store') }}" class="space-y-6">
                        @csrf

                        {{-- Información Básica --}}
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                {{-- Nombre --}}
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre del Tenant <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           value="{{ old('name') }}"
                                           required
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                                           placeholder="Ej: Agencia Marketing Digital">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           value="{{ old('email') }}"
                                           required
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                                           placeholder="contacto@empresa.com">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Teléfono --}}
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Teléfono
                                    </label>
                                    <input type="tel" 
                                           name="phone" 
                                           id="phone" 
                                           value="{{ old('phone') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-500 @enderror"
                                           placeholder="+54 9 223 123-4567">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Slug --}}
                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                                        Slug
                                        <span class="text-xs text-gray-500">(opcional, se genera automático)</span>
                                    </label>
                                    <input type="text" 
                                           name="slug" 
                                           id="slug" 
                                           value="{{ old('slug') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('slug') border-red-500 @enderror"
                                           placeholder="agencia-marketing">
                                    @error('slug')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Website --}}
                                <div>
                                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">
                                        Sitio Web
                                    </label>
                                    <input type="url" 
                                           name="website" 
                                           id="website" 
                                           value="{{ old('website') }}"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('website') border-red-500 @enderror"
                                           placeholder="https://www.empresa.com">
                                    @error('website')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        {{-- Plan y Suscripción --}}
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Plan y Suscripción</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                {{-- Plan --}}
                                <div>
                                    <label for="subscription_plan" class="block text-sm font-medium text-gray-700 mb-1">
                                        Plan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="subscription_plan" 
                                            id="subscription_plan"
                                            required
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('subscription_plan') border-red-500 @enderror">
                                        <option value="">Seleccionar plan...</option>
                                        <option value="starter" {{ old('subscription_plan') === 'starter' ? 'selected' : '' }}>
                                            Starter (3 bots, 1000 conversaciones/mes)
                                        </option>
                                        <option value="professional" {{ old('subscription_plan') === 'professional' ? 'selected' : '' }}>
                                            Professional (10 bots, 10000 conversaciones/mes)
                                        </option>
                                        <option value="enterprise" {{ old('subscription_plan') === 'enterprise' ? 'selected' : '' }}>
                                            Enterprise (50 bots, 100000 conversaciones/mes)
                                        </option>
                                    </select>
                                    @error('subscription_plan')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Estado --}}
                                <div>
                                    <label for="subscription_status" class="block text-sm font-medium text-gray-700 mb-1">
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <select name="subscription_status" 
                                            id="subscription_status"
                                            required
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('subscription_status') border-red-500 @enderror">
                                        <option value="">Seleccionar estado...</option>
                                        <option value="trial" {{ old('subscription_status') === 'trial' ? 'selected' : '' }}>
                                            Trial (14 días)
                                        </option>
                                        <option value="active" {{ old('subscription_status', 'active') === 'active' ? 'selected' : '' }}>
                                            Activo
                                        </option>
                                        <option value="suspended" {{ old('subscription_status') === 'suspended' ? 'selected' : '' }}>
                                            Suspendido
                                        </option>
                                    </select>
                                    @error('subscription_status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        {{-- Límites (Opcional) --}}
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Límites del Plan</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Los límites se establecen automáticamente según el plan, pero puedes personalizarlos aquí.
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                
                                {{-- Límite de Conversaciones --}}
                                <div>
                                    <label for="monthly_conversation_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                        Conversaciones/mes
                                    </label>
                                    <input type="number" 
                                           name="monthly_conversation_limit" 
                                           id="monthly_conversation_limit" 
                                           value="{{ old('monthly_conversation_limit') }}"
                                           min="0"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('monthly_conversation_limit') border-red-500 @enderror"
                                           placeholder="1000">
                                    @error('monthly_conversation_limit')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Límite de Bots --}}
                                <div>
                                    <label for="monthly_bot_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                        Bots
                                    </label>
                                    <input type="number" 
                                           name="monthly_bot_limit" 
                                           id="monthly_bot_limit" 
                                           value="{{ old('monthly_bot_limit') }}"
                                           min="1"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('monthly_bot_limit') border-red-500 @enderror"
                                           placeholder="3">
                                    @error('monthly_bot_limit')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Límite de Usuarios --}}
                                <div>
                                    <label for="monthly_user_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                        Usuarios
                                    </label>
                                    <input type="number" 
                                           name="monthly_user_limit" 
                                           id="monthly_user_limit" 
                                           value="{{ old('monthly_user_limit') }}"
                                           min="1"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('monthly_user_limit') border-red-500 @enderror"
                                           placeholder="5">
                                    @error('monthly_user_limit')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        {{-- Características Adicionales --}}
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Características Adicionales</h3>
                            
                            {{-- White Label --}}
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" 
                                           name="is_white_label" 
                                           id="is_white_label" 
                                           value="1"
                                           {{ old('is_white_label') ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_white_label" class="font-medium text-gray-700">White Label</label>
                                    <p class="text-gray-500">Permite personalizar la marca y ocultar referencias a BotHub</p>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('admin.tenants.index') }}" 
                               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Crear Tenant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>