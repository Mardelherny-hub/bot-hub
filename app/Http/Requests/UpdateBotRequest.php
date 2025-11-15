<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateBotRequest
 * 
 * Validación de datos para actualización de bots existentes.
 * 
 * DIFERENCIAS CON StoreBotRequest:
 * - Todos los campos son opcionales (PATCH/PUT parcial)
 * - La validación de unique excluye el bot actual
 * - No se valida autorización aquí (ya lo hace el middleware)
 * 
 * @package App\Http\Requests
 */
class UpdateBotRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta request
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // Solo admin o supervisor del tenant pueden actualizar bots
        return $this->user()?->hasRole(['admin', 'supervisor']) ?? false;
    }

    /**
     * Reglas de validación
     * 
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Obtener el ID del bot que se está editando
        $botId = $this->route('bot')->id;

        return [
            // Información básica (todos opcionales en update)
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'phone_number' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'regex:/^\+[1-9]\d{1,14}$/',
                Rule::unique('bots', 'phone_number')->ignore($botId), // Excluir bot actual
            ],
            'whatsapp_business_account_id' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],
            'whatsapp_phone_number_id' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            // Configuración de IA
            'ai_model' => [
                'sometimes',
                'required',
                Rule::in(['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo']),
            ],
            'personality' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],
            'instructions' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],
            'max_tokens' => [
                'sometimes',
                'nullable',
                'integer',
                'min:50',
                'max:4000',
            ],
            'temperature' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0.0',
                'max:2.0',
            ],
            'language' => [
                'sometimes',
                'nullable',
                Rule::in(['es', 'en', 'pt', 'fr', 'de', 'it']),
            ],

            // Configuración de comportamiento
            'is_active' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
            'fallback_to_human' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
            'inactivity_timeout_minutes' => [
                'sometimes',
                'nullable',
                'integer',
                'min:5',
                'max:1440',
            ],
            'business_hours_start' => [
                'sometimes',
                'nullable',
                'date_format:H:i',
            ],
            'business_hours_end' => [
                'sometimes',
                'nullable',
                'date_format:H:i',
                'after:business_hours_start',
            ],
            'business_days' => [
                'sometimes',
                'nullable',
                'array',
                'min:1',
            ],
            'business_days.*' => [
                Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            ],
            'out_of_hours_message' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],

            // Configuración de Knowledge Base (RAG)
            'use_knowledge_base' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
            'knowledge_base_results' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
            'knowledge_base_threshold' => [
                'sometimes',
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
        if ($this->has('phone_number') && $this->phone_number !== null) {
            $this->merge([
                'phone_number' => preg_replace('/\s+/', '', $this->phone_number),
            ]);
        }
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