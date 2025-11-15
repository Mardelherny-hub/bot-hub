<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreTenantRequest
 * 
 * Validación de datos para creación de nuevos tenants.
 * 
 * CAMPOS VALIDADOS:
 * - name: Nombre de la agencia/empresa (requerido)
 * - slug: Slug único para URLs (opcional, se genera automático)
 * - email: Email de contacto (requerido, único)
 * - phone: Teléfono (opcional)
 * - website: Sitio web (opcional, debe ser URL válida)
 * - logo_url: URL del logo (opcional, debe ser URL válida)
 * - subscription_plan: Plan de suscripción (requerido)
 * - subscription_status: Estado de suscripción (requerido)
 * - monthly_conversation_limit: Límite mensual de conversaciones
 * - monthly_bot_limit: Límite de bots
 * - monthly_user_limit: Límite de usuarios
 * - is_white_label: ¿Tiene white-label?
 * - settings: Configuración adicional en JSON (opcional)
 * 
 * @package App\Http\Requests
 */
class StoreTenantRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta request
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // Solo super_admin puede crear tenants
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
        return [
            // Información básica
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash', // Solo letras, números, guiones y guiones bajos
                'unique:tenants,slug', // Debe ser único
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:tenants,email', // Debe ser único
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\+?[0-9\s\-()]+$/', // Formato de teléfono internacional
            ],
            'website' => [
                'nullable',
                'url',
                'max:255',
            ],
            'logo_url' => [
                'nullable',
                'url',
                'max:500',
            ],

            // Suscripción
            'subscription_plan' => [
                'required',
                Rule::in(['starter', 'professional', 'enterprise']),
            ],
            'subscription_status' => [
                'required',
                Rule::in(['active', 'suspended', 'cancelled', 'trial']),
            ],

            // Límites del plan
            'monthly_conversation_limit' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],
            'monthly_bot_limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'monthly_user_limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000',
            ],

            // Características
            'is_white_label' => [
                'nullable',
                'boolean',
            ],

            // Configuración adicional
            'settings' => [
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
        if ($this->has('phone')) {
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
     * Valores por defecto para campos opcionales
     * 
     * Se aplican después de la validación, antes de guardar.
     * 
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Establecer límites por defecto según el plan si no se proporcionaron
        if (!isset($validated['monthly_conversation_limit'])) {
            $validated['monthly_conversation_limit'] = match($validated['subscription_plan']) {
                'starter' => 1000,
                'professional' => 10000,
                'enterprise' => 100000,
                default => 1000,
            };
        }

        if (!isset($validated['monthly_bot_limit'])) {
            $validated['monthly_bot_limit'] = match($validated['subscription_plan']) {
                'starter' => 3,
                'professional' => 10,
                'enterprise' => 50,
                default => 3,
            };
        }

        if (!isset($validated['monthly_user_limit'])) {
            $validated['monthly_user_limit'] = match($validated['subscription_plan']) {
                'starter' => 5,
                'professional' => 20,
                'enterprise' => 100,
                default => 5,
            };
        }

        // Valor por defecto para is_white_label
        if (!isset($validated['is_white_label'])) {
            $validated['is_white_label'] = false;
        }

        // Configuración por defecto
        if (!isset($validated['settings'])) {
            $validated['settings'] = json_encode([
                'timezone' => 'America/Argentina/Buenos_Aires',
                'date_format' => 'd/m/Y',
                'currency' => 'USD',
                'features' => [
                    'white_label' => $validated['is_white_label'],
                    'api_access' => $validated['subscription_plan'] !== 'starter',
                    'custom_domain' => $validated['subscription_plan'] === 'enterprise',
                ],
            ]);
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
            'monthly_conversation_limit' => 'límite mensual de conversaciones',
            'monthly_bot_limit' => 'límite de bots',
            'monthly_user_limit' => 'límite de usuarios',
            'is_white_label' => 'white-label',
            'settings' => 'configuración',
        ];
    }
}