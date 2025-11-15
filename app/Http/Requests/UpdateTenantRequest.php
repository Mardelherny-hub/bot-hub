<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateTenantRequest
 * 
 * Validación de datos para actualización de tenants existentes.
 * 
 * DIFERENCIAS CON StoreTenantRequest:
 * - Todos los campos son opcionales (PATCH/PUT parcial)
 * - Las reglas de unique excluyen el tenant actual
 * - No se valida autorización aquí (ya lo hace el middleware)
 * 
 * CAMPOS VALIDADOS:
 * - name: Nombre de la agencia/empresa (opcional)
 * - slug: Slug único para URLs (opcional)
 * - email: Email de contacto (opcional, único excluyendo actual)
 * - phone: Teléfono (opcional)
 * - website: Sitio web (opcional, debe ser URL válida)
 * - logo_url: URL del logo (opcional, debe ser URL válida)
 * - subscription_plan: Plan de suscripción (opcional)
 * - subscription_status: Estado de suscripción (opcional)
 * - subscription_ends_at: Fecha de fin de suscripción (opcional)
 * - monthly_conversation_limit: Límite mensual de conversaciones
 * - monthly_bot_limit: Límite de bots
 * - monthly_user_limit: Límite de usuarios
 * - is_white_label: ¿Tiene white-label?
 * - settings: Configuración adicional en JSON (opcional)
 * 
 * @package App\Http\Requests
 */
class UpdateTenantRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta request
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // Solo super_admin puede actualizar tenants
        // El middleware ya valida esto, pero lo confirmamos aquí
        return $this->user()?->hasRole('super_admin') ?? false;
    }

    /**
     * Reglas de validación
     * 
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Obtener el ID del tenant que se está editando
        $tenantId = $this->route('tenant')->id;

        return [
            // Información básica (todos opcionales en update)
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('tenants', 'slug')->ignore($tenantId), // Excluir tenant actual
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('tenants', 'email')->ignore($tenantId), // Excluir tenant actual
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^\+?[0-9\s\-()]+$/',
            ],
            'website' => [
                'sometimes',
                'nullable',
                'url',
                'max:255',
            ],
            'logo_url' => [
                'sometimes',
                'nullable',
                'url',
                'max:500',
            ],

            // Suscripción
            'subscription_plan' => [
                'sometimes',
                'required',
                Rule::in(['starter', 'professional', 'enterprise']),
            ],
            'subscription_status' => [
                'sometimes',
                'required',
                Rule::in(['active', 'suspended', 'cancelled', 'trial']),
            ],
            'subscription_ends_at' => [
                'sometimes',
                'nullable',
                'date',
                'after:today', // La fecha de fin debe ser futura
            ],

            // Límites del plan
            'monthly_conversation_limit' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],
            'monthly_bot_limit' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'monthly_user_limit' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                'max:1000',
            ],

            // Características
            'is_white_label' => [
                'sometimes',
                'nullable',
                'boolean',
            ],

            // Configuración adicional
            'settings' => [
                'sometimes',
                'nullable',
                'json',
            ],
        ];
    }

    /**
     * Mensajes de error personalizados
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Name
            'name.required' => 'El nombre del tenant es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',

            // Slug
            'slug.alpha_dash' => 'El slug solo puede contener letras, números, guiones y guiones bajos.',
            'slug.unique' => 'Este slug ya está en uso. Por favor, elige otro.',

            // Email
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Este email ya está registrado.',

            // Phone
            'phone.regex' => 'El formato del teléfono no es válido. Ejemplo: +54 9 223 123-4567',

            // Website
            'website.url' => 'El sitio web debe ser una URL válida.',

            // Logo URL
            'logo_url.url' => 'La URL del logo debe ser válida.',

            // Subscription plan
            'subscription_plan.required' => 'Debes seleccionar un plan de suscripción.',
            'subscription_plan.in' => 'El plan seleccionado no es válido.',

            // Subscription status
            'subscription_status.required' => 'Debes seleccionar un estado de suscripción.',
            'subscription_status.in' => 'El estado de suscripción no es válido.',

            // Subscription ends at
            'subscription_ends_at.date' => 'La fecha de fin debe ser una fecha válida.',
            'subscription_ends_at.after' => 'La fecha de fin debe ser posterior a hoy.',

            // Limits
            'monthly_conversation_limit.integer' => 'El límite de conversaciones debe ser un número entero.',
            'monthly_conversation_limit.min' => 'El límite de conversaciones no puede ser negativo.',
            'monthly_conversation_limit.max' => 'El límite de conversaciones es demasiado alto.',

            'monthly_bot_limit.integer' => 'El límite de bots debe ser un número entero.',
            'monthly_bot_limit.min' => 'Debe permitirse al menos 1 bot.',
            'monthly_bot_limit.max' => 'El límite de bots es demasiado alto.',

            'monthly_user_limit.integer' => 'El límite de usuarios debe ser un número entero.',
            'monthly_user_limit.min' => 'Debe permitirse al menos 1 usuario.',
            'monthly_user_limit.max' => 'El límite de usuarios es demasiado alto.',

            // Settings
            'settings.json' => 'La configuración debe ser un JSON válido.',
        ];
    }

    /**
     * Prepara los datos para validación
     * 
     * Normaliza ciertos campos antes de validar.
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convertir is_white_label a boolean si viene como string
        if ($this->has('is_white_label')) {
            $this->merge([
                'is_white_label' => filter_var($this->is_white_label, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Normalizar el teléfono (remover espacios extras)
        if ($this->has('phone') && $this->phone !== null) {
            $this->merge([
                'phone' => preg_replace('/\s+/', ' ', trim($this->phone)),
            ]);
        }

        // Normalizar el website (agregar https:// si no tiene protocolo)
        if ($this->filled('website') && !str_starts_with($this->website, 'http')) {
            $this->merge([
                'website' => 'https://' . $this->website,
            ]);
        }
    }

    /**
     * Valores ajustados después de validación
     * 
     * Si se cambia el plan, se ajustan los límites automáticamente.
     * 
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Si se cambió el plan y no se especificaron límites, establecer los del nuevo plan
        if (isset($validated['subscription_plan'])) {
            $plan = $validated['subscription_plan'];

            // Solo establecer límites si no fueron enviados explícitamente
            if (!isset($validated['monthly_conversation_limit'])) {
                $validated['monthly_conversation_limit'] = match($plan) {
                    'starter' => 1000,
                    'professional' => 10000,
                    'enterprise' => 100000,
                    default => 1000,
                };
            }

            if (!isset($validated['monthly_bot_limit'])) {
                $validated['monthly_bot_limit'] = match($plan) {
                    'starter' => 3,
                    'professional' => 10,
                    'enterprise' => 50,
                    default => 3,
                };
            }

            if (!isset($validated['monthly_user_limit'])) {
                $validated['monthly_user_limit'] = match($plan) {
                    'starter' => 5,
                    'professional' => 20,
                    'enterprise' => 100,
                    default => 5,
                };
            }
        }

        // Si se cambió el status a 'active' y no hay fecha de fin, extender 1 mes
        if (isset($validated['subscription_status']) 
            && $validated['subscription_status'] === 'active' 
            && !isset($validated['subscription_ends_at'])) {
            $validated['subscription_ends_at'] = now()->addMonth();
        }

        // Si se cambió a trial y no hay fecha de fin, 14 días
        if (isset($validated['subscription_status']) 
            && $validated['subscription_status'] === 'trial' 
            && !isset($validated['subscription_ends_at'])) {
            $validated['subscription_ends_at'] = now()->addDays(14);
        }

        // Actualizar settings si cambió is_white_label o el plan
        if (isset($validated['is_white_label']) || isset($validated['subscription_plan'])) {
            // Obtener tenant actual para mergear settings existentes
            $tenant = $this->route('tenant');
            $currentSettings = is_string($tenant->settings) 
                ? json_decode($tenant->settings, true) 
                : ($tenant->settings ?? []);

            // Mergear con cambios
            if (isset($validated['is_white_label'])) {
                $currentSettings['features']['white_label'] = $validated['is_white_label'];
            }

            if (isset($validated['subscription_plan'])) {
                $currentSettings['features']['api_access'] = $validated['subscription_plan'] !== 'starter';
                $currentSettings['features']['custom_domain'] = $validated['subscription_plan'] === 'enterprise';
            }

            // Solo actualizar settings si no se enviaron explícitamente
            if (!isset($validated['settings'])) {
                $validated['settings'] = json_encode($currentSettings);
            }
        }

        return $validated;
    }

    /**
     * Nombres de atributos personalizados para mensajes de error
     * 
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del tenant',
            'slug' => 'slug',
            'email' => 'email',
            'phone' => 'teléfono',
            'website' => 'sitio web',
            'logo_url' => 'URL del logo',
            'subscription_plan' => 'plan de suscripción',
            'subscription_status' => 'estado de suscripción',
            'subscription_ends_at' => 'fecha de fin de suscripción',
            'monthly_conversation_limit' => 'límite mensual de conversaciones',
            'monthly_bot_limit' => 'límite de bots',
            'monthly_user_limit' => 'límite de usuarios',
            'is_white_label' => 'white-label',
            'settings' => 'configuración',
        ];
    }
}