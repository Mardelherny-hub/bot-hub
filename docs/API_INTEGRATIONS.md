# BOTHUB - API INTEGRATIONS

**Versión:** 1.0.0  
**Última actualización:** 13 de Noviembre, 2025  
**Estado:** En desarrollo

---

## 📋 TABLA DE CONTENIDOS

1. [WhatsApp Business API](#whatsapp-business-api)
2. [OpenAI API](#openai-api)
3. [Twilio API](#twilio-api-backup)
4. [Pusher/Laravel Reverb](#pusher--laravel-reverb)
5. [Variables de Entorno](#variables-de-entorno)
6. [Rate Limits y Costos](#rate-limits-y-costos)
7. [Manejo de Errores](#manejo-de-errores)

---

## 📱 WHATSAPP BUSINESS API

### Información General

**Proveedor:** Meta (Facebook)  
**Documentación oficial:** https://developers.facebook.com/docs/whatsapp  
**Tipo:** REST API  
**Autenticación:** Bearer Token  
**Formato:** JSON

### Configuración Inicial

#### 1. Crear Cuenta WhatsApp Business

1. Ir a: https://business.facebook.com
2. Crear cuenta de Meta Business
3. Agregar WhatsApp Business Platform
4. Verificar número de teléfono
5. Obtener credenciales:
   - `WHATSAPP_ACCESS_TOKEN`
   - `WHATSAPP_PHONE_NUMBER_ID`
   - `WHATSAPP_BUSINESS_ACCOUNT_ID`
   - `WHATSAPP_WEBHOOK_VERIFY_TOKEN` (crear uno propio)

#### 2. Configurar Webhook

**URL del webhook:** `https://tu-dominio.com/api/webhooks/whatsapp`

**Eventos a suscribir:**
- `messages` (mensajes entrantes)
- `message_status` (estados de entrega)

**Verify Token:** Debe coincidir con `WHATSAPP_WEBHOOK_VERIFY_TOKEN` en `.env`

### Endpoints Principales

#### Base URL
```
https://graph.facebook.com/v18.0
```

#### 1. Enviar Mensaje de Texto

**Endpoint:**
```
POST /{phone_number_id}/messages
```

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Body (Texto):**
```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "5492231234567",
  "type": "text",
  "text": {
    "preview_url": false,
    "body": "Hola, ¿en qué puedo ayudarte?"
  }
}
```

**Respuesta Exitosa:**
```json
{
  "messaging_product": "whatsapp",
  "contacts": [
    {
      "input": "5492231234567",
      "wa_id": "5492231234567"
    }
  ],
  "messages": [
    {
      "id": "wamid.HBgLNTQ5MjIzMTIzNDU2NxUCABIYIDNBN0Y5QjY4QjQ2M0Y4RkE4QzVEOEQ3RTYzRjRBODAzAA=="
    }
  ]
}
```

#### 2. Enviar Imagen

**Body:**
```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "5492231234567",
  "type": "image",
  "image": {
    "link": "https://example.com/image.jpg",
    "caption": "Descripción de la imagen"
  }
}
```

#### 3. Enviar Documento

**Body:**
```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "5492231234567",
  "type": "document",
  "document": {
    "link": "https://example.com/document.pdf",
    "filename": "documento.pdf",
    "caption": "Tu documento solicitado"
  }
}
```

#### 4. Enviar Template (Mensajes Pre-aprobados)

**Body:**
```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "5492231234567",
  "type": "template",
  "template": {
    "name": "hello_world",
    "language": {
      "code": "es"
    }
  }
}
```

#### 5. Enviar Template con Variables

**Body:**
```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "5492231234567",
  "type": "template",
  "template": {
    "name": "order_confirmation",
    "language": {
      "code": "es"
    },
    "components": [
      {
        "type": "body",
        "parameters": [
          {
            "type": "text",
            "text": "Víctor"
          },
          {
            "type": "text",
            "text": "Tu pedido #12345 está listo"
          }
        ]
      }
    ]
  }
}
```

#### 6. Mensajes Interactivos - Botones

**Body:**
```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "5492231234567",
  "type": "interactive",
  "interactive": {
    "type": "button",
    "body": {
      "text": "¿Deseas hablar con un agente?"
    },
    "action": {
      "buttons": [
        {
          "type": "reply",
          "reply": {
            "id": "yes_agent",
            "title": "Sí, por favor"
          }
        },
        {
          "type": "reply",
          "reply": {
            "id": "no_agent",
            "title": "No, gracias"
          }
        }
      ]
    }
  }
}
```

#### 7. Mensajes Interactivos - Lista

**Body:**
```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "5492231234567",
  "type": "interactive",
  "interactive": {
    "type": "list",
    "header": {
      "type": "text",
      "text": "Nuestros servicios"
    },
    "body": {
      "text": "Selecciona una opción:"
    },
    "footer": {
      "text": "BotHub"
    },
    "action": {
      "button": "Ver opciones",
      "sections": [
        {
          "title": "Servicios",
          "rows": [
            {
              "id": "service_1",
              "title": "Consulta técnica",
              "description": "Problemas técnicos"
            },
            {
              "id": "service_2",
              "title": "Ventas",
              "description": "Información comercial"
            },
            {
              "id": "service_3",
              "title": "Soporte",
              "description": "Ayuda general"
            }
          ]
        }
      ]
    }
  }
}
```

#### 8. Marcar Mensaje como Leído

**Body:**
```json
{
  "messaging_product": "whatsapp",
  "status": "read",
  "message_id": "wamid.HBgLNTQ5..."
}
```

### Webhook - Mensajes Entrantes

#### Seguridad: Verificación de Firma

**CRÍTICO:** Siempre verificar la firma del webhook en producción.

WhatsApp envía header `X-Hub-Signature-256` con el hash del payload:

```php
// En WhatsAppWebhookController
public function handle(Request $request)
{
    // 1. Verificar firma
    $signature = $request->header('X-Hub-Signature-256');
    $payload = $request->getContent();
    $appSecret = config('services.whatsapp.app_secret');
    
    $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);
    
    if (!hash_equals($expectedSignature, $signature)) {
        Log::warning('Invalid webhook signature');
        return response('Forbidden', 403);
    }
    
    // 2. Procesar webhook
    $data = $request->json()->all();
    // ... resto del código
}
```

**Configurar en .env:**
```
WHATSAPP_APP_SECRET=xxxxx
```

#### Estructura del Webhook

**Verificación (GET):**
```php
// GET /api/webhooks/whatsapp
// Query params: hub.mode, hub.challenge, hub.verify_token

if (request('hub.verify_token') === config('services.whatsapp.webhook_verify_token')) {
    return response(request('hub.challenge'), 200);
}
return response('Forbidden', 403);
```

**Recepción de Mensajes (POST):**
```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "id": "123456789",
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "15551234567",
              "phone_number_id": "123456789"
            },
            "contacts": [
              {
                "profile": {
                  "name": "Juan Pérez"
                },
                "wa_id": "5492231234567"
              }
            ],
            "messages": [
              {
                "from": "5492231234567",
                "id": "wamid.HBgLNTQ5...",
                "timestamp": "1699901234",
                "type": "text",
                "text": {
                  "body": "Hola, necesito ayuda"
                }
              }
            ]
          },
          "field": "messages"
        }
      ]
    }
  ]
}
```

#### Tipos de Mensajes Entrantes

**Texto:**
```json
{
  "type": "text",
  "text": {
    "body": "Contenido del mensaje"
  }
}
```

**Imagen:**
```json
{
  "type": "image",
  "image": {
    "mime_type": "image/jpeg",
    "sha256": "abc123...",
    "id": "media_id_123"
  }
}
```

**Documento:**
```json
{
  "type": "document",
  "document": {
    "filename": "documento.pdf",
    "mime_type": "application/pdf",
    "sha256": "abc123...",
    "id": "media_id_456"
  }
}
```

**Audio:**
```json
{
  "type": "audio",
  "audio": {
    "mime_type": "audio/ogg; codecs=opus",
    "sha256": "abc123...",
    "id": "media_id_789",
    "voice": true
  }
}
```

**Ubicación:**
```json
{
  "type": "location",
  "location": {
    "latitude": -38.0054771,
    "longitude": -57.5426106,
    "name": "Mar del Plata",
    "address": "Buenos Aires, Argentina"
  }
}
```

#### Estados de Mensajes

**Webhook de Estado:**
```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "id": "123456789",
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "15551234567",
              "phone_number_id": "123456789"
            },
            "statuses": [
              {
                "id": "wamid.HBgLNTQ5...",
                "status": "delivered",
                "timestamp": "1699901234",
                "recipient_id": "5492231234567",
                "conversation": {
                  "id": "conversation_id",
                  "origin": {
                    "type": "business_initiated"
                  }
                },
                "pricing": {
                  "billable": true,
                  "pricing_model": "CBP",
                  "category": "business_initiated"
                }
              }
            ]
          },
          "field": "messages"
        }
      ]
    }
  ]
}
```

**Estados posibles:**
- `sent`: Enviado al servidor de WhatsApp
- `delivered`: Entregado al dispositivo del usuario
- `read`: Leído por el usuario
- `failed`: Falló el envío

### Descargar Media

**Endpoint:**
```
GET /{media_id}
```

**Headers:**
```
Authorization: Bearer {access_token}
```

**Respuesta:**
```json
{
  "url": "https://lookaside.fbsbx.com/whatsapp_business/attachments/?mid=abc123...",
  "mime_type": "image/jpeg",
  "sha256": "abc123...",
  "file_size": 123456,
  "id": "media_id_123",
  "messaging_product": "whatsapp"
}
```

Luego descargar desde la URL (válida por 5 minutos):
```
GET {url}
Authorization: Bearer {access_token}
```

### Implementación en Laravel

**WhatsAppService.php:**
```php
<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl = 'https://graph.facebook.com/v18.0';
    protected string $accessToken;
    protected string $phoneNumberId;

    public function __construct()
    {
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
    }

    /**
     * Enviar mensaje de texto
     */
    public function sendTextMessage(string $to, string $message): array
    {
        $url = "{$this->baseUrl}/{$this->phoneNumberId}/messages";
        
        $response = Http::withToken($this->accessToken)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message
                ]
            ]);

        if ($response->failed()) {
            Log::error('WhatsApp API Error', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            throw new \Exception('Failed to send WhatsApp message');
        }

        return $response->json();
    }

    /**
     * Enviar imagen
     */
    public function sendImage(string $to, string $imageUrl, ?string $caption = null): array
    {
        $url = "{$this->baseUrl}/{$this->phoneNumberId}/messages";
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'image',
            'image' => [
                'link' => $imageUrl
            ]
        ];

        if ($caption) {
            $payload['image']['caption'] = $caption;
        }

        $response = Http::withToken($this->accessToken)->post($url, $payload);

        if ($response->failed()) {
            throw new \Exception('Failed to send WhatsApp image');
        }

        return $response->json();
    }

    /**
     * Marcar mensaje como leído
     */
    public function markAsRead(string $messageId): array
    {
        $url = "{$this->baseUrl}/{$this->phoneNumberId}/messages";
        
        $response = Http::withToken($this->accessToken)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'status' => 'read',
                'message_id' => $messageId
            ]);

        return $response->json();
    }

    /**
     * Descargar media
     */
    public function downloadMedia(string $mediaId): string
    {
        // Paso 1: Obtener URL del media
        $response = Http::withToken($this->accessToken)
            ->get("{$this->baseUrl}/{$mediaId}");

        $mediaUrl = $response->json('url');

        // Paso 2: Descargar el archivo
        $fileResponse = Http::withToken($this->accessToken)
            ->get($mediaUrl);

        // Guardar en storage
        $filename = 'whatsapp_media_' . time() . '_' . uniqid();
        Storage::disk('public')->put($filename, $fileResponse->body());

        return Storage::disk('public')->url($filename);
    }
}
```

**WebhookHandler.php:**
```php
<?php

namespace App\Services\Messaging;

use App\Jobs\ProcessIncomingMessage;
use App\Models\Bot;
use Illuminate\Support\Facades\Log;

class WebhookHandler
{
    /**
     * Manejar webhook de WhatsApp
     */
    public function handle(array $payload): void
    {
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) return;

        $changes = $entry['changes'][0] ?? null;
        if (!$changes) return;

        $value = $changes['value'];
        
        // Manejar mensajes entrantes
        if (isset($value['messages'])) {
            $this->handleIncomingMessages($value);
        }

        // Manejar estados de mensajes
        if (isset($value['statuses'])) {
            $this->handleMessageStatuses($value);
        }
    }

    /**
     * Procesar mensajes entrantes
     */
    protected function handleIncomingMessages(array $value): void
    {
        $phoneNumberId = $value['metadata']['phone_number_id'];
        $messages = $value['messages'];
        $contacts = $value['contacts'] ?? [];

        // Encontrar el bot por phone_number_id
        $bot = Bot::where('phone_number_id', $phoneNumberId)
            ->where('is_active', true)
            ->first();

        if (!$bot) {
            Log::warning('Bot not found for phone_number_id', ['id' => $phoneNumberId]);
            return;
        }

        foreach ($messages as $message) {
            $contact = collect($contacts)->firstWhere('wa_id', $message['from']);
            
            ProcessIncomingMessage::dispatch([
                'bot_id' => $bot->id,
                'external_user_id' => $message['from'],
                'external_user_name' => $contact['profile']['name'] ?? null,
                'external_message_id' => $message['id'],
                'message_type' => $message['type'],
                'message_data' => $message,
                'timestamp' => $message['timestamp']
            ]);
        }
    }

    /**
     * Procesar estados de mensajes
     */
    protected function handleMessageStatuses(array $value): void
    {
        $statuses = $value['statuses'];

        foreach ($statuses as $status) {
            // Actualizar estado del mensaje en BD
            \App\Models\Message::where('external_message_id', $status['id'])
                ->update([
                    'status' => $status['status'],
                    'updated_at' => now()
                ]);
        }
    }
}
```

### Errores Comunes

| Código | Error | Solución |
|--------|-------|----------|
| 100 | Invalid parameter | Verificar formato del JSON |
| 131030 | Recipient phone number not valid | Verificar formato del número (con código país) |
| 131031 | Could not send message | Número no tiene WhatsApp o bloqueó el bot |
| 131047 | Re-engagement message | Usuario debe iniciar conversación primero |
| 130472 | User's number is part of an experiment | Número en experimento de Meta, probar con otro |

### Rate Limits

**Límites por defecto:**
- 1,000 mensajes por segundo (por número de teléfono)
- Sin límite de mensajes totales por día

**Límites de conversación gratuita:**
- 1,000 conversaciones gratuitas por mes
- Después se cobra según categoría

### Costos (Aproximados)

**Conversaciones business-initiated:**
- Argentina: ~$0.0485 USD por conversación

**Conversaciones user-initiated:**
- Argentina: ~$0.0291 USD por conversación

**Nota:** Precios pueden variar, verificar en: https://developers.facebook.com/docs/whatsapp/pricing

---

## 🤖 OPENAI API

### Información General

**Proveedor:** OpenAI  
**Documentación oficial:** https://platform.openai.com/docs  
**Tipo:** REST API  
**Autenticación:** Bearer Token (API Key)  
**Formato:** JSON

### Configuración Inicial

1. Crear cuenta en: https://platform.openai.com
2. Generar API key: https://platform.openai.com/api-keys
3. Configurar en `.env`:
   ```
   OPENAI_API_KEY=sk-proj-xxxxx
   ```

### Endpoints Principales

#### Base URL
```
https://api.openai.com/v1
```

#### 1. Chat Completions (GPT-4)

**Endpoint:**
```
POST /chat/completions
```

**Headers:**
```
Authorization: Bearer {api_key}
Content-Type: application/json
```

**Body:**
```json
{
  "model": "gpt-4",
  "messages": [
    {
      "role": "system",
      "content": "Eres un asistente de atención al cliente profesional y amable."
    },
    {
      "role": "user",
      "content": "¿Cuál es tu horario de atención?"
    }
  ],
  "temperature": 0.7,
  "max_tokens": 500,
  "top_p": 1,
  "frequency_penalty": 0,
  "presence_penalty": 0
}
```

**Respuesta:**
```json
{
  "id": "chatcmpl-123",
  "object": "chat.completion",
  "created": 1699901234,
  "model": "gpt-4-0613",
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "Nuestro horario de atención es de lunes a viernes de 9:00 a 18:00 hs."
      },
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": 45,
    "completion_tokens": 28,
    "total_tokens": 73
  }
}
```

#### 2. Embeddings (para RAG)

**Endpoint:**
```
POST /embeddings
```

**Body:**
```json
{
  "model": "text-embedding-ada-002",
  "input": "Este es el texto para convertir en embedding"
}
```

**Respuesta:**
```json
{
  "object": "list",
  "data": [
    {
      "object": "embedding",
      "index": 0,
      "embedding": [
        -0.006929283,
        -0.005336422,
        0.018114548,
        // ... 1536 valores en total
      ]
    }
  ],
  "model": "text-embedding-ada-002-v2",
  "usage": {
    "prompt_tokens": 8,
    "total_tokens": 8
  }
}
```

### Implementación en Laravel

**OpenAIService.php:**
```php
<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected string $baseUrl = 'https://api.openai.com/v1';
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    /**
     * Generar respuesta usando GPT-4
     */
    public function generateResponse(
        array $messages,
        float $temperature = 0.7,
        int $maxTokens = 500,
        string $model = 'gpt-4'
    ): array {
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0
            ]);

        if ($response->failed()) {
            Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            throw new \Exception('Failed to generate AI response');
        }

        return $response->json();
    }

    /**
     * Crear embedding para RAG
     */
    public function createEmbedding(string $text): array
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/embeddings", [
                'model' => 'text-embedding-ada-002',
                'input' => $text
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to create embedding');
        }

        return $response->json('data.0.embedding');
    }

    /**
     * Crear embeddings en batch
     */
    public function createEmbeddings(array $texts): array
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/embeddings", [
                'model' => 'text-embedding-ada-002',
                'input' => $texts
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to create embeddings');
        }

        return collect($response->json('data'))
            ->pluck('embedding')
            ->toArray();
    }
}
```

**PromptBuilder.php:**
```php
<?php

namespace App\Services\AI;

use App\Models\Bot;
use App\Models\Conversation;

class PromptBuilder
{
    /**
     * Construir prompt del sistema
     */
    public function buildSystemPrompt(Bot $bot): string
    {
        $prompt = "Eres un asistente virtual de {$bot->name}. ";
        
        if ($bot->personality) {
            $prompt .= $bot->personality . " ";
        }

        $prompt .= "Debes responder de manera clara, concisa y profesional. ";
        $prompt .= "Si no sabes la respuesta, admítelo honestamente. ";
        $prompt .= "Responde siempre en {$this->getLanguageName($bot->language)}.";

        return $prompt;
    }

    /**
     * Construir contexto de conversación
     */
    public function buildConversationContext(Conversation $conversation, int $limit = 10): array
    {
        $messages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse();

        return $messages->map(function ($message) {
            return [
                'role' => $this->mapSenderTypeToRole($message->sender_type),
                'content' => $message->content
            ];
        })->toArray();
    }

    /**
     * Agregar conocimiento del RAG
     */
    public function addKnowledgeContext(string $userMessage, array $knowledgeResults): string
    {
        if (empty($knowledgeResults)) {
            return $userMessage;
        }

        $context = "Información relevante de nuestra base de conocimiento:\n\n";
        
        foreach ($knowledgeResults as $result) {
            $context .= "- {$result['content']}\n";
        }

        $context .= "\nPregunta del usuario: {$userMessage}";

        return $context;
    }

    protected function mapSenderTypeToRole(string $senderType): string
    {
        return match($senderType) {
            'user' => 'user',
            'bot' => 'assistant',
            'agent' => 'assistant',
            default => 'user'
        };
    }

    protected function getLanguageName(string $code): string
    {
        return match($code) {
            'es' => 'español',
            'en' => 'inglés',
            'pt' => 'portugués',
            default => 'español'
        };
    }
}
```

### Modelos Disponibles

| Modelo | Tokens | Uso recomendado | Costo (aprox) |
|--------|--------|-----------------|---------------|
| gpt-4 | 8,192 | Máxima calidad | $0.03/1K input, $0.06/1K output |
| gpt-4-turbo | 128,000 | Balance calidad/velocidad | $0.01/1K input, $0.03/1K output |
| gpt-3.5-turbo | 4,096 | Alta velocidad, bajo costo | $0.0005/1K input, $0.0015/1K output |

**Recomendación para BotHub:** Usar `gpt-4` o `gpt-4-turbo` para mejor calidad de respuestas.

### Rate Limits

**Por defecto (Tier 1):**
- 3,500 requests por minuto
- 90,000 tokens por minuto

**Límites aumentan con uso:**
- Tier 2, 3, 4, 5 disponibles

### Errores Comunes

| Código | Error | Solución |
|--------|-------|----------|
| 401 | Invalid Authentication | Verificar API key |
| 429 | Rate limit exceeded | Implementar retry con backoff |
| 500 | Server error | Reintentar después de unos segundos |
| context_length_exceeded | Demasiados tokens | Reducir contexto o usar modelo con más tokens |

---

## 📞 TWILIO API (Backup)

### Información General

**Proveedor:** Twilio  
**Documentación oficial:** https://www.twilio.com/docs  
**Uso en BotHub:** Backup para WhatsApp y SMS  
**Autenticación:** Basic Auth (Account SID + Auth Token)

### Configuración

```
TWILIO_ACCOUNT_SID=ACxxxxx
TWILIO_AUTH_TOKEN=xxxxx
TWILIO_WHATSAPP_NUMBER=whatsapp:+14155238886
```

### Enviar WhatsApp (Twilio Sandbox)

**Endpoint:**
```
POST https://api.twilio.com/2010-04-01/Accounts/{AccountSid}/Messages.json
```

**Body (form-urlencoded):**
```
From=whatsapp:+14155238886
To=whatsapp:+5492231234567
Body=Tu mensaje aquí
```

**Implementación:**
```php
use Twilio\Rest\Client;

$client = new Client(
    config('services.twilio.sid'),
    config('services.twilio.token')
);

$message = $client->messages->create(
    'whatsapp:+5492231234567',
    [
        'from' => config('services.twilio.whatsapp_number'),
        'body' => 'Tu mensaje aquí'
    ]
);
```

---

## 🔔 PUSHER / LARAVEL REVERB

### Información General

**Opciones:**
1. **Pusher:** Servicio cloud (más fácil, costo mensual)
2. **Laravel Reverb:** Self-hosted (gratis, requiere servidor con WebSockets)

### Opción 1: Pusher

**Configuración:**
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=xxxxx
PUSHER_APP_KEY=xxxxx
PUSHER_APP_SECRET=xxxxx
PUSHER_APP_CLUSTER=us2
```

**Instalación:**
```bash
composer require pusher/pusher-php-server
npm install --save-dev laravel-echo pusher-js
```

**Uso:**
```php
// Enviar evento
broadcast(new MessageReceived($message));

// Frontend (JavaScript)
Echo.channel('conversations.' + conversationId)
    .listen('MessageReceived', (e) => {
        console.log(e.message);
    });
```

### Opción 2: Laravel Reverb

**Configuración:**
```
BROADCAST_DRIVER=reverb
REVERB_APP_ID=xxxxx
REVERB_APP_KEY=xxxxx
REVERB_APP_SECRET=xxxxx
REVERB_HOST=localhost
REVERB_PORT=8080
```

**Instalación:**
```bash
php artisan install:broadcasting
php artisan reverb:start
```

---

## 📊 LARAVEL HORIZON

### Información General

**Proveedor:** Laravel  
**Documentación oficial:** https://laravel.com/docs/11.x/horizon  
**Uso en BotHub:** Monitoreo y gestión de queues

### Instalación y Configuración

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

**Configuración:**
```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'high', 'low'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
            'timeout' => 60,
        ],
    ],
],
```

**Iniciar Horizon:**
```bash
php artisan horizon
```

**Dashboard:**
```
https://tu-dominio.com/horizon
```

**Proteger Dashboard:**
```php
// App\Providers\HorizonServiceProvider
Gate::define('viewHorizon', function ($user) {
    return in_array($user->email, [
        'admin@bothub.com',
    ]);
});
```

### Métricas que Proporciona

- Jobs procesados por minuto
- Tiempo promedio de procesamiento
- Jobs fallidos
- Throughput por queue
- Memoria usada por workers
- Estado de supervisores

### Supervisión con Supervisor

```bash
# /etc/supervisor/conf.d/horizon.conf
[program:horizon]
process_name=%(program_name)s
command=php /path/to/bothub/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/bothub/storage/logs/horizon.log
stopwaitsecs=3600
```

---

## 🔐 VARIABLES DE ENTORNO

**Archivo `.env` completo:**
```bash
# App
APP_NAME=BotHub
APP_ENV=production
APP_KEY=base64:xxxxx
APP_DEBUG=false
APP_URL=https://bothub.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bothub
DB_USERNAME=bothub_user
DB_PASSWORD=xxxxx

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# WhatsApp Business API
WHATSAPP_ACCESS_TOKEN=xxxxx
WHATSAPP_PHONE_NUMBER_ID=xxxxx
WHATSAPP_BUSINESS_ACCOUNT_ID=xxxxx
WHATSAPP_APP_SECRET=xxxxx
WHATSAPP_WEBHOOK_VERIFY_TOKEN=xxxxx

# OpenAI
OPENAI_API_KEY=sk-proj-xxxxx

# Twilio (Backup)
TWILIO_ACCOUNT_SID=ACxxxxx
TWILIO_AUTH_TOKEN=xxxxx
TWILIO_WHATSAPP_NUMBER=whatsapp:+14155238886

# Broadcasting
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=xxxxx
PUSHER_APP_KEY=xxxxx
PUSHER_APP_SECRET=xxxxx
PUSHER_APP_CLUSTER=us2

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxxx
MAIL_PASSWORD=xxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@bothub.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 💰 RATE LIMITS Y COSTOS

### Resumen de Costos Mensuales

**Por bot con 1,000 conversaciones/mes:**

| Servicio | Costo Mensual | Detalles |
|----------|---------------|----------|
| WhatsApp API | ~$30-50 USD | Varía por país |
| OpenAI (GPT-4) | ~$20-40 USD | Depende de tokens |
| Pusher | $0-49 USD | Plan gratuito hasta 200k msgs |
| Hosting | $50-100 USD | HostGator o similar |
| **Total** | **$100-240 USD** | Por bot/1K conversaciones |

**Márgenes:**
- Plan Starter ($99/mes): Break-even o pérdida inicial
- Plan Professional ($299/mes): ~50% margen
- Plan Enterprise ($799/mes): ~70% margen

---

## ⚠️ MANEJO DE ERRORES

### Estrategia de Retry

**Implementación en Laravel:**
```php
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class ResilientApiClient
{
    public function sendWithRetry(
        callable $apiCall,
        int $maxRetries = 3,
        int $delayMs = 1000
    ) {
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            try {
                return $apiCall();
            } catch (RequestException $e) {
                $attempt++;
                
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                
                // Exponential backoff
                $delay = $delayMs * pow(2, $attempt - 1);
                usleep($delay * 1000);
            }
        }
    }
}
```

### Circuit Breaker Pattern

**Para prevenir cascada de fallos:**
```php
use Illuminate\Support\Facades\Cache;

class CircuitBreaker
{
    protected string $serviceName;
    protected int $failureThreshold = 5;
    protected int $timeout = 60; // segundos

    public function call(callable $callback)
    {
        $key = "circuit_breaker:{$this->serviceName}";
        
        if (Cache::get("{$key}:open")) {
            throw new \Exception('Circuit breaker is open');
        }

        try {
            $result = $callback();
            Cache::forget("{$key}:failures");
            return $result;
        } catch (\Exception $e) {
            $failures = Cache::increment("{$key}:failures");
            
            if ($failures >= $this->failureThreshold) {
                Cache::put("{$key}:open", true, $this->timeout);
            }
            
            throw $e;
        }
    }
}
```

---

**Fin del documento de integraciones v1.0.0**

*Actualizar este documento cuando se agreguen nuevas integraciones.*
