# BOTHUB - DATABASE SCHEMA

**Versión:** 1.0.0  
**Última actualización:** 13 de Noviembre, 2025  
**Motor de BD:** MySQL 8.0  
**Charset:** utf8mb4  
**Collation:** utf8mb4_unicode_ci

---

## 📋 TABLA DE CONTENIDOS

1. [Diagrama de Relaciones](#diagrama-de-relaciones)
2. [Tablas del Sistema](#tablas-del-sistema)
3. [Índices y Constraints](#índices-y-constraints)
4. [Diccionario de Datos](#diccionario-de-datos)

---

## 🗺️ DIAGRAMA DE RELACIONES

```
┌─────────────┐
│   tenants   │ (Agencias o Empresas)
└──────┬──────┘
       │ 1:N
       ├──────────┐
       │          │
       ▼          ▼
┌─────────┐  ┌──────────┐
│  users  │  │   bots   │
└────┬────┘  └─────┬────┘
     │ N:N        │ 1:N
     │            │
     │            ├─────────────┬─────────────┬──────────────┐
     │            │             │             │              │
     │            ▼             ▼             ▼              ▼
     │    ┌───────────────┐ ┌─────────┐ ┌──────────┐ ┌──────────────┐
     │    │ conversations │ │webhooks │ │bot_config│ │knowledge_bases│
     │    └───────┬───────┘ └─────────┘ └──────────┘ └──────┬───────┘
     │            │ 1:N                                      │ 1:N
     │            ▼                                          ▼
     │      ┌──────────┐                            ┌───────────────────┐
     │      │ messages │                            │knowledge_documents│
     │      └────┬─────┘                            └───────────────────┘
     │           │ 1:N
     │           ▼
     │    ┌────────────────┐
     │    │message_metadata│
     │    └────────────────┘
     │
     └──────────┐
                ▼
         ┌────────────┐
         │  bot_user  │ (Pivot: asignación bots a usuarios)
         └────────────┘
```

---

## 📊 TABLAS DEL SISTEMA

### 1. **tenants**
Representa agencias o empresas que usan la plataforma.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `name` | VARCHAR(255) | NO | - | Nombre de la agencia/empresa |
| `slug` | VARCHAR(255) | NO | - | Slug único para URLs |
| `email` | VARCHAR(255) | NO | - | Email de contacto |
| `phone` | VARCHAR(20) | YES | NULL | Teléfono de contacto |
| `website` | VARCHAR(255) | YES | NULL | Sitio web |
| `logo_url` | VARCHAR(500) | YES | NULL | URL del logo |
| `subscription_plan` | ENUM | NO | 'starter' | Plan: starter, professional, enterprise |
| `subscription_status` | ENUM | NO | 'active' | Estado: active, suspended, cancelled |
| `subscription_started_at` | TIMESTAMP | YES | NULL | Inicio de suscripción |
| `subscription_ends_at` | TIMESTAMP | YES | NULL | Fin de suscripción |
| `monthly_conversation_limit` | INT | NO | 1000 | Límite mensual de conversaciones |
| `monthly_bot_limit` | INT | NO | 3 | Límite de bots |
| `monthly_user_limit` | INT | NO | 1 | Límite de usuarios |
| `is_white_label` | BOOLEAN | NO | FALSE | ¿Tiene white-label? |
| `settings` | JSON | YES | NULL | Configuración adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `idx_tenants_slug` (`slug`)
- UNIQUE KEY `idx_tenants_email` (`email`)
- INDEX `idx_tenants_subscription_status` (`subscription_status`)

**Valores de ENUM:**
- `subscription_plan`: 'starter', 'professional', 'enterprise'
- `subscription_status`: 'active', 'suspended', 'cancelled', 'trial'

---

### 2. **users**
Usuarios del sistema (admins, agentes, clientes).

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `tenant_id` | BIGINT UNSIGNED | NO | - | Tenant al que pertenece |
| `name` | VARCHAR(255) | NO | - | Nombre completo |
| `email` | VARCHAR(255) | NO | - | Email único |
| `email_verified_at` | TIMESTAMP | YES | NULL | Verificación de email |
| `password` | VARCHAR(255) | NO | - | Password hasheado |
| `phone` | VARCHAR(20) | YES | NULL | Teléfono |
| `avatar_url` | VARCHAR(500) | YES | NULL | URL del avatar |
| `role` | ENUM | NO | 'agent' | Rol del usuario |
| `is_active` | BOOLEAN | NO | TRUE | ¿Usuario activo? |
| `last_login_at` | TIMESTAMP | YES | NULL | Último login |
| `preferences` | JSON | YES | NULL | Preferencias del usuario |
| `remember_token` | VARCHAR(100) | YES | NULL | Token de sesión |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `idx_users_email` (`email`)
- INDEX `idx_users_tenant_id` (`tenant_id`)
- INDEX `idx_users_role` (`role`)
- FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE

**Valores de ENUM:**
- `role`: 'super_admin', 'admin', 'agent', 'viewer'

---

### 3. **bots**
Configuración de cada bot de la plataforma.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `tenant_id` | BIGINT UNSIGNED | NO | - | Tenant propietario |
| `name` | VARCHAR(255) | NO | - | Nombre del bot |
| `description` | TEXT | YES | NULL | Descripción del bot |
| `phone_number` | VARCHAR(20) | NO | - | Número de WhatsApp asociado |
| `phone_number_id` | VARCHAR(100) | YES | NULL | ID de Meta para el número |
| `whatsapp_business_account_id` | VARCHAR(100) | YES | NULL | ID cuenta WhatsApp Business |
| `is_active` | BOOLEAN | NO | TRUE | ¿Bot activo? |
| `personality` | TEXT | YES | NULL | Descripción de personalidad/tono |
| `language` | VARCHAR(10) | NO | 'es' | Idioma principal (ISO 639-1) |
| `timezone` | VARCHAR(50) | NO | 'UTC' | Zona horaria del bot |
| `welcome_message` | TEXT | YES | NULL | Mensaje de bienvenida |
| `offline_message` | TEXT | YES | NULL | Mensaje fuera de horario |
| `fallback_message` | TEXT | YES | NULL | Mensaje cuando no sabe responder |
| `handoff_threshold` | INT | NO | 3 | Intentos antes de handoff |
| `business_hours_start` | TIME | YES | NULL | Inicio horario atención |
| `business_hours_end` | TIME | YES | NULL | Fin horario atención |
| `business_days` | JSON | YES | NULL | Días de atención (array) |
| `max_conversation_length` | INT | NO | 50 | Máx mensajes en contexto |
| `ai_model` | VARCHAR(50) | NO | 'gpt-4' | Modelo de IA a usar |
| `ai_temperature` | DECIMAL(3,2) | NO | 0.70 | Temperature del modelo |
| `ai_max_tokens` | INT | NO | 500 | Máximo de tokens por respuesta |
| `metadata` | JSON | YES | NULL | Metadata adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `idx_bots_phone_number` (`phone_number`)
- INDEX `idx_bots_tenant_id` (`tenant_id`)
- INDEX `idx_bots_is_active` (`is_active`)
- FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE

---

### 4. **conversations**
Hilos de conversación entre usuarios finales y bots.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `bot_id` | BIGINT UNSIGNED | NO | - | Bot que maneja la conversación |
| `external_user_id` | VARCHAR(255) | NO | - | ID del usuario externo (phone, etc) |
| `external_user_name` | VARCHAR(255) | YES | NULL | Nombre del usuario externo |
| `channel` | VARCHAR(50) | NO | 'whatsapp' | Canal de comunicación |
| `status` | ENUM | NO | 'active' | Estado de la conversación |
| `assigned_user_id` | BIGINT UNSIGNED | YES | NULL | Usuario asignado (handoff) |
| `handoff_reason` | TEXT | YES | NULL | Razón del handoff |
| `handoff_at` | TIMESTAMP | YES | NULL | Momento del handoff |
| `last_message_at` | TIMESTAMP | YES | NULL | Último mensaje recibido |
| `message_count` | INT | NO | 0 | Cantidad de mensajes |
| `first_response_time_ms` | INT | YES | NULL | Tiempo primera respuesta (ms) |
| `sentiment_score` | DECIMAL(3,2) | YES | NULL | Score de sentimiento (-1 a 1) |
| `satisfaction_rating` | TINYINT | YES | NULL | Rating de satisfacción (1-5) |
| `tags` | JSON | YES | NULL | Tags de la conversación |
| `metadata` | JSON | YES | NULL | Metadata adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |
| `closed_at` | TIMESTAMP | YES | NULL | Momento de cierre |

**Índices:**
- PRIMARY KEY (`id`)
- INDEX `idx_conversations_bot_id` (`bot_id`)
- INDEX `idx_conversations_external_user_id` (`external_user_id`)
- INDEX `idx_conversations_status` (`status`)
- INDEX `idx_conversations_assigned_user_id` (`assigned_user_id`)
- INDEX `idx_conversations_last_message_at` (`last_message_at`)
- FOREIGN KEY (`bot_id`) REFERENCES `bots`(`id`) ON DELETE CASCADE
- FOREIGN KEY (`assigned_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL

**Valores de ENUM:**
- `status`: 'active', 'waiting_human', 'with_human', 'resolved', 'closed'

---

### 5. **messages**
Mensajes individuales dentro de conversaciones.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `conversation_id` | BIGINT UNSIGNED | NO | - | Conversación a la que pertenece |
| `direction` | ENUM | NO | - | Dirección del mensaje |
| `sender_type` | ENUM | NO | - | Tipo de remitente |
| `sender_id` | BIGINT UNSIGNED | YES | NULL | ID del remitente (si aplica) |
| `content` | TEXT | NO | - | Contenido del mensaje |
| `content_type` | VARCHAR(50) | NO | 'text' | Tipo de contenido |
| `interactive_type` | VARCHAR(50) | YES | NULL | Tipo interactivo (button, list, etc) |
| `interactive_payload` | JSON | YES | NULL | Payload del mensaje interactivo |
| `media_url` | VARCHAR(500) | YES | NULL | URL de media adjunta |
| `media_mime_type` | VARCHAR(100) | YES | NULL | MIME type del media |
| `external_message_id` | VARCHAR(255) | YES | NULL | ID externo (de WhatsApp, etc) |
| `status` | ENUM | NO | 'sent' | Estado del mensaje |
| `error_message` | TEXT | YES | NULL | Mensaje de error si falló |
| `ai_generated` | BOOLEAN | NO | FALSE | ¿Generado por IA? |
| `ai_model_used` | VARCHAR(50) | YES | NULL | Modelo de IA usado |
| `ai_tokens_used` | INT | YES | NULL | Tokens consumidos |
| `processing_time_ms` | INT | YES | NULL | Tiempo de procesamiento (ms) |
| `metadata` | JSON | YES | NULL | Metadata adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |

**Índices:**
- PRIMARY KEY (`id`)
- INDEX `idx_messages_conversation_id` (`conversation_id`)
- INDEX `idx_messages_direction` (`direction`)
- INDEX `idx_messages_sender_type` (`sender_type`)
- INDEX `idx_messages_status` (`status`)
- INDEX `idx_messages_created_at` (`created_at`)
- FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE
- FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE SET NULL

**Valores de ENUM:**
- `direction`: 'inbound', 'outbound'
- `sender_type`: 'user', 'bot', 'agent'
- `status`: 'sent', 'delivered', 'read', 'failed'
- `content_type`: 'text', 'image', 'document', 'audio', 'video', 'location'

---

### 6. **knowledge_bases**
Base de conocimiento por bot.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `bot_id` | BIGINT UNSIGNED | NO | - | Bot al que pertenece |
| `name` | VARCHAR(255) | NO | - | Nombre de la KB |
| `description` | TEXT | YES | NULL | Descripción |
| `is_active` | BOOLEAN | NO | TRUE | ¿KB activa? |
| `document_count` | INT | NO | 0 | Cantidad de documentos |
| `total_tokens` | INT | NO | 0 | Total de tokens procesados |
| `last_trained_at` | TIMESTAMP | YES | NULL | Última vez entrenada |
| `embedding_model` | VARCHAR(50) | NO | 'text-embedding-ada-002' | Modelo de embeddings |
| `settings` | JSON | YES | NULL | Configuración adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (`id`)
- INDEX `idx_knowledge_bases_bot_id` (`bot_id`)
- INDEX `idx_knowledge_bases_is_active` (`is_active`)
- FOREIGN KEY (`bot_id`) REFERENCES `bots`(`id`) ON DELETE CASCADE

---

### 7. **knowledge_documents**
Documentos individuales dentro de una knowledge base.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `knowledge_base_id` | BIGINT UNSIGNED | NO | - | KB a la que pertenece |
| `title` | VARCHAR(255) | NO | - | Título del documento |
| `content` | LONGTEXT | NO | - | Contenido extraído |
| `source_type` | ENUM | NO | - | Tipo de fuente |
| `source_url` | VARCHAR(500) | YES | NULL | URL de origen (si aplica) |
| `file_path` | VARCHAR(500) | YES | NULL | Path del archivo (si aplica) |
| `file_size` | INT | YES | NULL | Tamaño en bytes |
| `file_type` | VARCHAR(50) | YES | NULL | Tipo de archivo |
| `chunk_count` | INT | NO | 0 | Cantidad de chunks |
| `token_count` | INT | NO | 0 | Cantidad de tokens |
| `embedding_status` | ENUM | NO | 'pending' | Estado del embedding |
| `processed_at` | TIMESTAMP | YES | NULL | Momento de procesamiento |
| `metadata` | JSON | YES | NULL | Metadata adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (`id`)
- INDEX `idx_knowledge_documents_kb_id` (`knowledge_base_id`)
- INDEX `idx_knowledge_documents_source_type` (`source_type`)
- INDEX `idx_knowledge_documents_embedding_status` (`embedding_status`)
- FOREIGN KEY (`knowledge_base_id`) REFERENCES `knowledge_bases`(`id`) ON DELETE CASCADE

**Valores de ENUM:**
- `source_type`: 'upload', 'url', 'manual', 'api'
- `embedding_status`: 'pending', 'processing', 'completed', 'failed'

---

### 8. **document_chunks**
Chunks de documentos para RAG (búsqueda vectorial).

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `knowledge_document_id` | BIGINT UNSIGNED | NO | - | Documento al que pertenece |
| `content` | TEXT | NO | - | Contenido del chunk |
| `chunk_index` | INT | NO | - | Índice del chunk |
| `token_count` | INT | NO | 0 | Cantidad de tokens |
| `embedding` | JSON | YES | NULL | Vector de embedding |
| `metadata` | JSON | YES | NULL | Metadata adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |

**Índices:**
- PRIMARY KEY (`id`)
- INDEX `idx_document_chunks_document_id` (`knowledge_document_id`)
- INDEX `idx_document_chunks_chunk_index` (`chunk_index`)
- FOREIGN KEY (`knowledge_document_id`) REFERENCES `knowledge_documents`(`id`) ON DELETE CASCADE

**Nota:** Para búsqueda vectorial eficiente, considerar usar extensión MySQL Vector o servicio externo como Pinecone/Weaviate en producción.

---

### 9. **webhooks**
Configuración de webhooks para cada bot.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `bot_id` | BIGINT UNSIGNED | NO | - | Bot al que pertenece |
| `name` | VARCHAR(255) | NO | - | Nombre del webhook |
| `event_type` | VARCHAR(100) | NO | - | Tipo de evento |
| `url` | VARCHAR(500) | NO | - | URL del webhook |
| `method` | ENUM | NO | 'POST' | Método HTTP |
| `headers` | JSON | YES | NULL | Headers personalizados |
| `is_active` | BOOLEAN | NO | TRUE | ¿Webhook activo? |
| `retry_on_failure` | BOOLEAN | NO | TRUE | ¿Reintentar si falla? |
| `max_retries` | INT | NO | 3 | Máximo de reintentos |
| `timeout_seconds` | INT | NO | 30 | Timeout en segundos |
| `last_triggered_at` | TIMESTAMP | YES | NULL | Última ejecución |
| `success_count` | INT | NO | 0 | Cantidad de éxitos |
| `failure_count` | INT | NO | 0 | Cantidad de fallos |
| `metadata` | JSON | YES | NULL | Metadata adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (`id`)
- INDEX `idx_webhooks_bot_id` (`bot_id`)
- INDEX `idx_webhooks_event_type` (`event_type`)
- INDEX `idx_webhooks_is_active` (`is_active`)
- FOREIGN KEY (`bot_id`) REFERENCES `bots`(`id`) ON DELETE CASCADE

**Valores de ENUM:**
- `method`: 'GET', 'POST', 'PUT', 'PATCH'

**Event types comunes:**
- `message.received`
- `message.sent`
- `conversation.started`
- `conversation.closed`
- `handoff.requested`
- `handoff.completed`

---

### 10. **analytics_events**
Eventos para analytics y métricas.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `tenant_id` | BIGINT UNSIGNED | NO | - | Tenant al que pertenece |
| `bot_id` | BIGINT UNSIGNED | YES | NULL | Bot relacionado |
| `conversation_id` | BIGINT UNSIGNED | YES | NULL | Conversación relacionada |
| `event_type` | VARCHAR(100) | NO | - | Tipo de evento |
| `event_category` | VARCHAR(50) | NO | - | Categoría del evento |
| `event_data` | JSON | YES | NULL | Datos del evento |
| `value` | DECIMAL(10,2) | YES | NULL | Valor numérico (si aplica) |
| `user_agent` | VARCHAR(500) | YES | NULL | User agent (si aplica) |
| `ip_address` | VARCHAR(45) | YES | NULL | IP del origen |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha del evento |

**Índices:**
- PRIMARY KEY (`id`)
- INDEX `idx_analytics_events_tenant_id` (`tenant_id`)
- INDEX `idx_analytics_events_bot_id` (`bot_id`)
- INDEX `idx_analytics_events_conversation_id` (`conversation_id`)
- INDEX `idx_analytics_events_event_type` (`event_type`)
- INDEX `idx_analytics_events_event_category` (`event_category`)
- INDEX `idx_analytics_events_created_at` (`created_at`)
- FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
- FOREIGN KEY (`bot_id`) REFERENCES `bots`(`id`) ON DELETE CASCADE
- FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE

**Event categories:**
- `message`: Eventos relacionados con mensajes
- `conversation`: Eventos de conversaciones
- `user`: Eventos de usuarios
- `system`: Eventos del sistema
- `billing`: Eventos de facturación

---

### 11. **bot_user** (Pivot Table)
Relación muchos-a-muchos entre bots y usuarios (asignación de bots a agentes).

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `bot_id` | BIGINT UNSIGNED | NO | - | Bot asignado |
| `user_id` | BIGINT UNSIGNED | NO | - | Usuario asignado |
| `can_manage` | BOOLEAN | NO | TRUE | ¿Puede gestionar el bot? |
| `can_view_analytics` | BOOLEAN | NO | TRUE | ¿Puede ver analytics? |
| `can_chat` | BOOLEAN | NO | TRUE | ¿Puede chatear? |
| `can_train_kb` | BOOLEAN | NO | FALSE | ¿Puede entrenar knowledge base? |
| `can_delete_data` | BOOLEAN | NO | FALSE | ¿Puede borrar datos? |
| `assigned_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de asignación |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |

**Índices:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `idx_bot_user_unique` (`bot_id`, `user_id`)
- INDEX `idx_bot_user_bot_id` (`bot_id`)
- INDEX `idx_bot_user_user_id` (`user_id`)
- FOREIGN KEY (`bot_id`) REFERENCES `bots`(`id`) ON DELETE CASCADE
- FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE

---

### 12. **api_keys**
API keys para integraciones externas.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `tenant_id` | BIGINT UNSIGNED | NO | - | Tenant propietario |
| `name` | VARCHAR(255) | NO | - | Nombre de la API key |
| `key` | VARCHAR(100) | NO | - | API key (hasheada) |
| `key_preview` | VARCHAR(20) | NO | - | Primeros chars (para UI) |
| `permissions` | JSON | YES | NULL | Permisos de la key |
| `is_active` | BOOLEAN | NO | TRUE | ¿Key activa? |
| `last_used_at` | TIMESTAMP | YES | NULL | Último uso |
| `usage_count` | INT | NO | 0 | Cantidad de usos |
| `rate_limit_per_minute` | INT | YES | NULL | Límite de requests/min |
| `expires_at` | TIMESTAMP | YES | NULL | Fecha de expiración |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Índices:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `idx_api_keys_key` (`key`)
- INDEX `idx_api_keys_tenant_id` (`tenant_id`)
- INDEX `idx_api_keys_is_active` (`is_active`)
- FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE

---

### 13. **notifications**
Notificaciones del sistema para usuarios.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `user_id` | BIGINT UNSIGNED | NO | - | Usuario destinatario |
| `type` | VARCHAR(100) | NO | - | Tipo de notificación |
| `title` | VARCHAR(255) | NO | - | Título |
| `message` | TEXT | NO | - | Mensaje |
| `action_url` | VARCHAR(500) | YES | NULL | URL de acción |
| `is_read` | BOOLEAN | NO | FALSE | ¿Leída? |
| `read_at` | TIMESTAMP | YES | NULL | Momento de lectura |
| `priority` | ENUM | NO | 'normal' | Prioridad |
| `metadata` | JSON | YES | NULL | Metadata adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |

**Índices:**
- PRIMARY KEY (`id`)
- INDEX `idx_notifications_user_id` (`user_id`)
- INDEX `idx_notifications_is_read` (`is_read`)
- INDEX `idx_notifications_created_at` (`created_at`)
- FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE

**Valores de ENUM:**
- `priority`: 'low', 'normal', 'high', 'urgent'

---

### 14. **audit_logs**
Logs de auditoría del sistema.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `tenant_id` | BIGINT UNSIGNED | YES | NULL | Tenant relacionado |
| `user_id` | BIGINT UNSIGNED | YES | NULL | Usuario que realizó la acción |
| `action` | VARCHAR(100) | NO | - | Acción realizada |
| `entity_type` | VARCHAR(100) | NO | - | Tipo de entidad afectada |
| `entity_id` | BIGINT UNSIGNED | YES | NULL | ID de la entidad |
| `old_values` | JSON | YES | NULL | Valores anteriores |
| `new_values` | JSON | YES | NULL | Valores nuevos |
| `ip_address` | VARCHAR(45) | YES | NULL | IP del origen |
| `user_agent` | VARCHAR(500) | YES | NULL | User agent |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha del log |

**Índices:**
- PRIMARY KEY (`id`)
- INDEX `idx_audit_logs_tenant_id` (`tenant_id`)
- INDEX `idx_audit_logs_user_id` (`user_id`)
- INDEX `idx_audit_logs_action` (`action`)
- INDEX `idx_audit_logs_entity_type` (`entity_type`)
- INDEX `idx_audit_logs_created_at` (`created_at`)
- FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
- FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL

---

### 15. **tenant_usage_reports**
Reportes mensuales de uso por tenant para billing y analytics.

| Campo | Tipo | Null | Default | Descripción |
|-------|------|------|---------|-------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | ID único |
| `tenant_id` | BIGINT UNSIGNED | NO | - | Tenant al que pertenece |
| `period` | VARCHAR(7) | NO | - | Período (YYYY-MM) |
| `conversations_used` | INT | NO | 0 | Conversaciones consumidas |
| `messages_sent` | INT | NO | 0 | Mensajes enviados |
| `messages_received` | INT | NO | 0 | Mensajes recibidos |
| `tokens_used` | BIGINT | NO | 0 | Tokens de IA consumidos |
| `bots_active` | INT | NO | 0 | Bots activos en el período |
| `users_active` | INT | NO | 0 | Usuarios activos |
| `storage_mb_used` | DECIMAL(10,2) | NO | 0 | Storage usado en MB |
| `whatsapp_cost_usd` | DECIMAL(10,4) | NO | 0 | Costo WhatsApp API |
| `openai_cost_usd` | DECIMAL(10,4) | NO | 0 | Costo OpenAI API |
| `total_cost_usd` | DECIMAL(10,4) | NO | 0 | Costo total del período |
| `billing_status` | ENUM | NO | 'pending' | Estado de facturación |
| `billed_at` | TIMESTAMP | YES | NULL | Fecha de facturación |
| `metadata` | JSON | YES | NULL | Metadata adicional |
| `created_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | NO | CURRENT_TIMESTAMP | Última actualización |

**Índices:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `idx_tenant_usage_period` (`tenant_id`, `period`)
- INDEX `idx_tenant_usage_tenant_id` (`tenant_id`)
- INDEX `idx_tenant_usage_period` (`period`)
- INDEX `idx_tenant_usage_billing_status` (`billing_status`)
- FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE

**Valores de ENUM:**
- `billing_status`: 'pending', 'calculated', 'billed', 'paid', 'overdue'

---

## 🔍 ÍNDICES Y CONSTRAINTS

### Índices Compuestos Adicionales

```sql
-- Para búsquedas de conversaciones activas por bot
CREATE INDEX idx_conversations_bot_status ON conversations(bot_id, status);

-- Para búsquedas de mensajes recientes en conversación
CREATE INDEX idx_messages_conversation_created ON messages(conversation_id, created_at);

-- Para analytics por tenant y fecha
CREATE INDEX idx_analytics_tenant_date ON analytics_events(tenant_id, created_at);

-- Para búsquedas de documentos procesados
CREATE INDEX idx_knowledge_docs_kb_status ON knowledge_documents(knowledge_base_id, embedding_status);
```

### Foreign Keys (Resumen)

| Tabla Hija | Columna | Tabla Padre | Columna | On Delete |
|------------|---------|-------------|---------|-----------|
| users | tenant_id | tenants | id | CASCADE |
| bots | tenant_id | tenants | id | CASCADE |
| conversations | bot_id | bots | id | CASCADE |
| conversations | assigned_user_id | users | id | SET NULL |
| messages | conversation_id | conversations | id | CASCADE |
| messages | sender_id | users | id | SET NULL |
| knowledge_bases | bot_id | bots | id | CASCADE |
| knowledge_documents | knowledge_base_id | knowledge_bases | id | CASCADE |
| document_chunks | knowledge_document_id | knowledge_documents | id | CASCADE |
| webhooks | bot_id | bots | id | CASCADE |
| analytics_events | tenant_id | tenants | id | CASCADE |
| analytics_events | bot_id | bots | id | CASCADE |
| analytics_events | conversation_id | conversations | id | CASCADE |
| bot_user | bot_id | bots | id | CASCADE |
| bot_user | user_id | users | id | CASCADE |
| api_keys | tenant_id | tenants | id | CASCADE |
| notifications | user_id | users | id | CASCADE |
| audit_logs | tenant_id | tenants | id | CASCADE |
| audit_logs | user_id | users | id | SET NULL |

---

## 📖 DICCIONARIO DE DATOS

### Campos Especiales

#### JSON Fields

**tenants.settings**
```json
{
  "timezone": "America/Argentina/Buenos_Aires",
  "date_format": "d/m/Y",
  "currency": "USD",
  "features": {
    "white_label": true,
    "api_access": false,
    "custom_domain": false
  }
}
```

**bots.business_days**
```json
["monday", "tuesday", "wednesday", "thursday", "friday"]
```

**bots.metadata**
```json
{
  "industry": "ecommerce",
  "use_case": "customer_support",
  "custom_fields": {}
}
```

**conversations.tags**
```json
["urgent", "complaint", "sales", "support"]
```

**messages.metadata**
```json
{
  "context_window_size": 10,
  "knowledge_base_results": 3,
  "confidence_score": 0.85
}
```

**knowledge_bases.settings**
```json
{
  "chunk_size": 500,
  "chunk_overlap": 50,
  "max_results": 5,
  "similarity_threshold": 0.7
}
```

**document_chunks.embedding**
```json
[0.123, -0.456, 0.789, ...] // Vector de 1536 dimensiones para text-embedding-ada-002
```

**webhooks.headers**
```json
{
  "Authorization": "Bearer token123",
  "Content-Type": "application/json"
}
```

**analytics_events.event_data**
```json
{
  "response_time_ms": 1250,
  "tokens_used": 350,
  "success": true
}
```

---

## 🎯 CONSIDERACIONES TÉCNICAS

### Performance

1. **Particionamiento**: Considerar particionar `analytics_events` y `audit_logs` por fecha para mejor performance.

2. **Archivado**: Mover conversaciones cerradas >90 días a tabla de archivo.

3. **Cache**: Cachear queries frecuentes:
   - Configuración de bots
   - Límites de tenants
   - Knowledge base activa

### Backup

- **Backup completo:** Diario (3 AM)
- **Backup incremental:** Cada 6 horas
- **Retención:** 30 días
- **Punto de recuperación objetivo (RPO):** 6 horas
- **Tiempo de recuperación objetivo (RTO):** 2 horas

### Seguridad

1. **Encriptación en reposo** para campos sensibles:
   - `api_keys.key`
   - `webhooks.headers`
   - `users.password` (hasheado con bcrypt)

2. **Row-level security**: Siempre filtrar por `tenant_id` en queries.

3. **Soft deletes**: Usar `deleted_at` en tablas críticas para recuperación.

---

## 📝 NOTAS DE MIGRACIÓN

### Orden de Creación de Tablas

1. `tenants`
2. `users`
3. `bots`
4. `conversations`
5. `messages`
6. `knowledge_bases`
7. `knowledge_documents`
8. `document_chunks`
9. `webhooks`
10. `analytics_events`
11. `bot_user` (pivot)
12. `api_keys`
13. `notifications`
14. `audit_logs`
15. `tenant_usage_reports`

### Seeders Requeridos

- `TenantSeeder`: Crear tenant de prueba
- `UserSeeder`: Crear super admin y usuarios demo
- `RoleSeeder`: Crear roles con Spatie Permission
- `BotSeeder`: Crear bot demo

---

**Fin del schema v1.0.0**

*Este schema debe actualizarse con cada cambio en la estructura de BD.*
