<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreBotRequest
 * 
 * Validación de datos para creación de nuevos bots.
 * 
 * CAMPOS VALIDADOS:
 * - name: Nombre del bot (requerido)
 * - description: Descripción (opcional)
 * - phone_number: Número de WhatsApp (requerido, único, formato internacional)
 * - whatsapp_business_account_id: ID de cuenta WhatsApp Business (opcional)
 * - whatsapp_phone_number_id: ID del número en Meta (opcional)
 * - ai_model: Modelo de IA a usar (requerido)
 * - personality: Descripción de personalidad (opcional)
 * - instructions: Instrucciones específicas (opcional)
 * - max_tokens: Límite de tokens por respuesta (opcional)
 * - temperature: Temperature del modelo (opcional, 0.0-2.0)
 * - language: Idioma principal (opcional)
 * - is_active: ¿Bot activo? (opcional, default true)
 * - fallback_to_human: ¿Transferir a humano? (opcional, default true)
 * - inactivity_timeout_minutes: Timeout de inactividad (opcional)
 * - business_hours_start: Hora de inicio (opcional)
 * - business_hours_end: Hora de fin (opcional)
 * - business_days: Días laborables (opcional, array)
 * - out_of_hours_message: Mensaje fuera de horario (opcional)
 * - use_knowledge_base: ¿Usar knowledge base? (opcional)
 * - knowledge_base_results: Cantidad de resultados RAG (opcional)
 * - knowledge_base_threshold: Umbral de similitud (opcional)
 * 
 * @package App\Http\Requests
 */
class StoreBotRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta request
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // Solo admin o supervisor del tenant pueden crear bots
        return $this->user()?->hasRole(['admin', 'supervisor']) ?? false;
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
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+[1-9]\d{1,14}$/', // Formato E.164 (ej: +5492231234567)
                'unique:bots,phone_number', // Debe ser único globalmente
            ],
            'whatsapp_business_account_id' => [
                'nullable',
                'string',
                'max:100',
            ],
            'whatsapp_phone_number_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            // Configuración de IA
            'ai_model' => [
                'required',
                Rule::in(['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo']),
            ],
            'personality' => [
                'nullable',
                'string',
                'max:500',
            ],
            'instructions' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'max_tokens' => [
                'nullable',
                'integer',
                'min:50',
                'max:4000',
            ],
            'temperature' => [
                'nullable',
                'numeric',
                'min:0.0',
                'max:2.0',
            ],
            'language' => [
                'nullable',
                Rule::in(['es', 'en', 'pt', 'fr', 'de', 'it']),
            ],

            // Configuración de comportamiento
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'fallback_to_human' => [
                'nullable',
                'boolean',
            ],
            'inactivity_timeout_minutes' => [
                'nullable',
                'integer',
                'min:5',
                'max:1440', // 24 horas máximo
            ],
            'business_hours_start' => [
                'nullable',
                'date_format:H:i',
            ],
            'business_hours_end' => [
                'nullable',
                'date_format:H:i',
                'after:business_hours_start',
            ],
            'business_days' => [
                'nullable',
                'array',
                'min:1',
            ],
            'business_days.*' => [
                Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            ],
            'out_of_hours_message' => [
                'nullable',
                'string',
                'max:500',
            ],

            // Configuración de Knowledge Base (RAG)
            'use_knowledge_base' => [
                'nullable',
                'boolean',
            ],
            'knowledge_base_results' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
            'knowledge_base_threshold' => [
                'nullable',
                'numeric',
                'min:0.0',
                'max:1.0',
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
            'name.required' => 'El nombre del bot es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',

            // Description
            'description.max' => 'La descripción no puede exceder 1000 caracteres.',

            // Phone number
            'phone_number.required' => 'El número de WhatsApp es obligatorio.',
            'phone_number.regex' => 'El número debe estar en formato internacional (ej: +5492231234567).',
            'phone_number.unique' => 'Este número de WhatsApp ya está registrado.',

            // AI Model
            'ai_model.required' => 'Debes seleccionar un modelo de IA.',
            'ai_model.in' => 'El modelo de IA seleccionado no es válido.',

            // Personality
            'personality.max' => 'La personalidad no puede exceder 500 caracteres.',

            // Instructions
            'instructions.max' => 'Las instrucciones no pueden exceder 2000 caracteres.',

            // Max tokens
            'max_tokens.integer' => 'Los tokens máximos deben ser un número entero.',
            'max_tokens.min' => 'Los tokens máximos deben ser al menos 50.',
            'max_tokens.max' => 'Los tokens máximos no pueden exceder 4000.',

            // Temperature
            'temperature.numeric' => 'La temperatura debe ser un número.',
            'temperature.min' => 'La temperatura debe ser al menos 0.0.',
            'temperature.max' => 'La temperatura no puede exceder 2.0.',

            // Language
            'language.in' => 'El idioma seleccionado no es válido.',

            // Business hours
            'business_hours_start.date_format' => 'La hora de inicio debe estar en formato HH:MM.',
            'business_hours_end.date_format' => 'La hora de fin debe estar en formato HH:MM.',
            'business_hours_end.after' => 'La hora de fin debe ser posterior a la hora de inicio.',

            // Business days
            'business_days.array' => 'Los días laborables deben ser un array.',
            'business_days.min' => 'Debes seleccionar al menos un día laborable.',
            'business_days.*.in' => 'Uno de los días seleccionados no es válido.',

            // Out of hours message
            'out_of_hours_message.max' => 'El mensaje fuera de horario no puede exceder 500 caracteres.',

            // Inactivity timeout
            'inactivity_timeout_minutes.integer' => 'El timeout debe ser un número entero.',
            'inactivity_timeout_minutes.min' => 'El timeout debe ser al menos 5 minutos.',
            'inactivity_timeout_minutes.max' => 'El timeout no puede exceder 24 horas (1440 minutos).',

            // Knowledge base
            'knowledge_base_results.integer' => 'La cantidad de resultados debe ser un número entero.',
            'knowledge_base_results.min' => 'Debe haber al menos 1 resultado.',
            'knowledge_base_results.max' => 'No pueden haber más de 10 resultados.',

            'knowledge_base_threshold.numeric' => 'El umbral debe ser un número.',
            'knowledge_base_threshold.min' => 'El umbral debe ser al menos 0.0.',
            'knowledge_base_threshold.max' => 'El umbral no puede exceder 1.0.',
        ];
    }

    /**
     * Prepara los datos para validación
     * 
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convertir booleanos si vienen como string
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('fallback_to_human')) {
            $this->merge([
                'fallback_to_human' => filter_var($this->fallback_to_human, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('use_knowledge_base')) {
            $this->merge([
                'use_knowledge_base' => filter_var($this->use_knowledge_base, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Normalizar el número de teléfono (remover espacios)
        if ($this->has('phone_number')) {
            $this->merge([
                'phone_number' => preg_replace('/\s+/', '', $this->phone_number),
            ]);
        }
    }

    /**
     * Valores por defecto para campos opcionales
     * 
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Valores por defecto
        $validated['ai_model'] = $validated['ai_model'] ?? 'gpt-4';
        $validated['max_tokens'] = $validated['max_tokens'] ?? 500;
        $validated['temperature'] = $validated['temperature'] ?? 0.70;
        $validated['language'] = $validated['language'] ?? 'es';
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['fallback_to_human'] = $validated['fallback_to_human'] ?? true;
        $validated['inactivity_timeout_minutes'] = $validated['inactivity_timeout_minutes'] ?? 30;
        $validated['use_knowledge_base'] = $validated['use_knowledge_base'] ?? false;
        $validated['knowledge_base_results'] = $validated['knowledge_base_results'] ?? 3;
        $validated['knowledge_base_threshold'] = $validated['knowledge_base_threshold'] ?? 0.70;

        // Horario de atención por defecto (9 AM - 6 PM)
        $validated['business_hours_start'] = $validated['business_hours_start'] ?? '09:00';
        $validated['business_hours_end'] = $validated['business_hours_end'] ?? '18:00';
        
        // Días laborables por defecto (lunes a viernes)
        $validated['business_days'] = $validated['business_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        // Mensaje por defecto fuera de horario
        if (!isset($validated['out_of_hours_message'])) {
            $validated['out_of_hours_message'] = 'Gracias por contactarnos. Nuestro horario de atención es de lunes a viernes de 9 a 18hs. Te responderemos a la brevedad.';
        }

        // Personalidad por defecto
        if (!isset($validated['personality'])) {
            $validated['personality'] = 'Eres un asistente amigable y profesional. Respondes de manera clara y concisa.';
        }

        // Instrucciones por defecto
        if (!isset($validated['instructions'])) {
            $validated['instructions'] = 'Responde de manera clara y concisa. Si no sabes algo, admítelo y ofrece transferir a un humano.';
        }

        return $validated;
    }

    /**
     * Nombres de atributos personalizados
     * 
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del bot',
            'description' => 'descripción',
            'phone_number' => 'número de WhatsApp',
            'whatsapp_business_account_id' => 'ID de cuenta WhatsApp Business',
            'whatsapp_phone_number_id' => 'ID del número de WhatsApp',
            'ai_model' => 'modelo de IA',
            'personality' => 'personalidad',
            'instructions' => 'instrucciones',
            'max_tokens' => 'tokens máximos',
            'temperature' => 'temperature',
            'language' => 'idioma',
            'is_active' => 'activo',
            'fallback_to_human' => 'transferir a humano',
            'inactivity_timeout_minutes' => 'timeout de inactividad',
            'business_hours_start' => 'hora de inicio',
            'business_hours_end' => 'hora de fin',
            'business_days' => 'días laborables',
            'out_of_hours_message' => 'mensaje fuera de horario',
            'use_knowledge_base' => 'usar knowledge base',
            'knowledge_base_results' => 'resultados de knowledge base',
            'knowledge_base_threshold' => 'umbral de knowledge base',
        ];
    }
}