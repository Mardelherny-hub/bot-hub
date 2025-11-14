# BOTHUB - MASTER DOCUMENTATION

**Versión:** 1.0.0  
**Última actualización:** 13 de Noviembre, 2025  
**Estado del proyecto:** En desarrollo - Fase de diseño

---

## 📋 TABLA DE CONTENIDOS

1. [Información General](#información-general)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Arquitectura del Sistema](#arquitectura-del-sistema)
4. [Convenciones del Proyecto](#convenciones-del-proyecto)
5. [Modelo de Negocio](#modelo-de-negocio)
6. [Roadmap de Desarrollo](#roadmap-de-desarrollo)

---

## 🎯 INFORMACIÓN GENERAL

### Descripción del Proyecto
**BotHub** es una plataforma SaaS multi-tenant que permite a agencias y empresas crear, gestionar y desplegar chatbots inteligentes para atención al cliente en múltiples canales, con especial énfasis en WhatsApp.

### Objetivo Principal
Crear un producto escalable que pueda venderse como servicio recurrente (SaaS), permitiendo a las agencias ofrecer soluciones de IA a sus clientes sin necesidad de desarrollo técnico.

### Propuesta de Valor
- **Para Agencias:** Ofrecer servicios de IA sin equipo técnico propio
- **Para Empresas:** Automatizar atención al cliente sin desarrollo interno
- **Para Usuarios Finales:** Atención rápida y eficiente 24/7

### Alcance del MVP (Fase 1)
- Sistema multi-tenant con roles y permisos
- Integración con WhatsApp Business API
- Motor de IA con OpenAI GPT-4
- Knowledge base con sistema RAG
- Dashboard con analytics básicos
- Sistema de handoff a agentes humanos

---

## 🛠️ STACK TECNOLÓGICO

### Backend
- **Framework:** Laravel 12
- **PHP:** 8.3
- **Base de datos:** MySQL 8.0
- **Cache/Queue:** Redis
- **Autenticación API:** Laravel Sanctum
- **Permisos:** Spatie Laravel Permission

### Frontend
- **Template Engine:** Laravel Blade
- **Interactividad:** Livewire 3
- **JavaScript:** Alpine.js
- **CSS Framework:** Tailwind CSS
- **Charts:** Chart.js

### APIs y Servicios Externos
- **IA:** OpenAI API (GPT-4)
- **Messaging:** WhatsApp Business API (Meta Cloud API)
- **SMS/WhatsApp Backup:** Twilio
- **Real-time:** Pusher o Laravel Reverb (WebSockets)

### DevOps
- **Control de versiones:** Git/GitHub
- **CI/CD:** GitHub Actions
- **Hosting:** HostGator (inicial)
- **Deploy:** SSH + GitHub Actions
- **Error Monitoring:** Sentry
- **Queue Monitoring:** Laravel Horizon
- **Debug:** Laravel Telescope (desarrollo)
- **Logs:** CloudWatch o Papertrail (producción)

---

## 🏗️ ARQUITECTURA DEL SISTEMA

### Modelo Multi-Tenant

```
SUPER ADMIN (BotHub Platform)
  │
  ├── AGENCIA 1
  │    ├── Usuario Admin Agencia
  │    ├── Cliente 1 → Bot WhatsApp
  │    ├── Cliente 2 → Bot WhatsApp + Web
  │    └── Cliente 3 → Bot Telegram
  │
  ├── AGENCIA 2
  │    ├── Usuario Admin Agencia
  │    └── Cliente 4 → Bot WhatsApp
  │
  └── EMPRESA DIRECTA (sin agencia)
       ├── Usuario Admin Empresa
       └── Bot WhatsApp propio
```

### Aislamiento Multi-Tenant (CRÍTICO)

**Estrategia de doble capa:**

1. **Global Scope (TenantScope)**
   - Aplicado automáticamente a todos los modelos
   - Filtra TODAS las queries por `tenant_id`
   - Imposible acceder a datos de otro tenant por error de código
   - Implementado con trait `BelongsToTenant`

2. **Middleware (TenantResolver)**
   - Identifica el tenant del usuario autenticado
   - Setea tenant en contexto global
   - Valida permisos de acceso
   - Registra auditoría de accesos

**Flujo de seguridad:**
```
Request → Middleware identifica tenant → Global Scope filtra queries → Response
```

**Preparación para Multi-DB:**
- Arquitectura permite migrar a multi-DB por tenant en futuro
- Single-DB para MVP
- Multi-DB opcional para clientes Enterprise (Fase 3)

### Sistema de Permisos Granular

**Niveles de permiso:**

1. **Super Admin (Platform)**
   - Acceso total a todos los tenants
   - Gestión de plataforma
   - Configuración global

2. **Admin (Tenant)**
   - Gestión completa de su tenant
   - Crear/editar bots
   - Gestionar usuarios
   - Ver facturación

3. **Supervisor (Tenant)**
   - Ver todos los bots del tenant
   - Ver todas las conversaciones
   - Analytics completo
   - No puede modificar configuración

4. **Agent (Por Bot)**
   - Solo bots asignados
   - Chat en vivo
   - Ver conversaciones asignadas
   - Analytics limitado

5. **Viewer (Por Bot)**
   - Solo lectura
   - Ver conversaciones
   - Ver analytics
   - No puede chatear ni modificar

**Permisos específicos por Bot (tabla `bot_user`):**
- `can_manage`: Configurar el bot
- `can_view_analytics`: Ver métricas
- `can_chat`: Usar chat en vivo
- `can_train_kb`: Subir documentos y entrenar
- `can_delete_data`: Borrar conversaciones/documentos

**Implementación:**
- Spatie Permission para roles base
- Pivot table `bot_user` para permisos por bot
- Policies de Laravel para autorización
- Gates personalizados para lógica compleja

### Flujo Principal de Conversación

```
Usuario Final (WhatsApp)
    ↓
[1] Envía mensaje → WhatsApp Business API
    ↓
[2] Webhook recibe mensaje → BotHub
    ↓
[3] Sistema crea Job en Queue (Redis)
    ↓
[4] Job procesa mensaje:
    - Identifica bot receptor
    - Obtiene contexto conversación
    - Consulta Knowledge Base (RAG)
    ↓
[5] Envía a OpenAI API con:
    - Prompt del bot (personalidad)
    - Contexto conversación
    - Información de Knowledge Base
    ↓
[6] OpenAI genera respuesta
    ↓
[7] Sistema evalúa:
    - ¿Necesita handoff humano? → Notifica agente
    - ¿Respuesta válida? → Envía por WhatsApp
    ↓
[8] Guarda mensaje en BD
    ↓
[9] Actualiza analytics en tiempo real
    ↓
Usuario Final recibe respuesta
```

### Arquitectura de Carpetas (Laravel)

```
app/
├── Models/
│   ├── Tenant.php              # Agencias o empresas directas
│   ├── User.php                # Usuarios del sistema
│   ├── Bot.php                 # Configuración de bots
│   ├── Conversation.php        # Hilos de conversación
│   ├── Message.php             # Mensajes individuales
│   ├── KnowledgeBase.php       # Base de conocimiento
│   ├── KnowledgeDocument.php   # Documentos subidos
│   ├── Webhook.php             # Configuración webhooks
│   └── AnalyticsEvent.php      # Eventos para analytics
│
├── Services/
│   ├── AI/
│   │   ├── OpenAIService.php           # Integración OpenAI
│   │   ├── PromptBuilder.php           # Constructor de prompts
│   │   └── RAGService.php              # Retrieval Augmented Generation
│   ├── Messaging/
│   │   ├── WhatsAppService.php         # Integración WhatsApp
│   │   ├── WebhookHandler.php          # Manejo de webhooks
│   │   └── MessageProcessor.php        # Procesamiento de mensajes
│   ├── Analytics/
│   │   ├── AnalyticsService.php        # Métricas y analytics
│   │   └── ReportGenerator.php         # Generación de reportes
│   └── Tenant/
│       └── TenantService.php           # Gestión multi-tenant
│
├── Repositories/
│   ├── ConversationRepository.php
│   ├── MessageRepository.php
│   ├── BotRepository.php
│   └── KnowledgeBaseRepository.php
│
├── Jobs/
│   ├── ProcessIncomingMessage.php      # Procesar mensaje recibido
│   ├── SendWhatsAppMessage.php         # Enviar mensaje WhatsApp
│   ├── TrainKnowledgeBase.php          # Entrenar KB con nuevo doc
│   ├── GenerateEmbeddings.php          # Crear embeddings para RAG
│   └── CalculateDailyAnalytics.php     # Calcular métricas diarias
│
├── Http/
│   ├── Controllers/
│   │   ├── Dashboard/
│   │   │   └── DashboardController.php
│   │   ├── Bot/
│   │   │   ├── BotController.php
│   │   │   └── BotConfigurationController.php
│   │   ├── Conversation/
│   │   │   ├── ConversationController.php
│   │   │   └── LiveChatController.php
│   │   ├── KnowledgeBase/
│   │   │   └── KnowledgeBaseController.php
│   │   ├── Webhook/
│   │   │   └── WhatsAppWebhookController.php
│   │   └── Admin/
│   │       ├── TenantController.php
│   │       └── UserController.php
│   │
│   └── Middleware/
│       ├── TenantMiddleware.php        # Filtrar por tenant
│       ├── CheckBotOwnership.php       # Verificar ownership bot
│       └── ValidateWebhookSignature.php # Validar firma webhook
│
├── Livewire/
│   ├── Chat/
│   │   ├── ChatInterface.php           # Interface chat en vivo
│   │   └── ConversationList.php        # Lista conversaciones
│   ├── Bot/
│   │   ├── BotConfiguration.php        # Configuración bot
│   │   └── BotList.php                 # Lista de bots
│   ├── Dashboard/
│   │   ├── AnalyticsDashboard.php      # Dashboard analytics
│   │   └── ActivityFeed.php            # Feed de actividad
│   └── KnowledgeBase/
│       ├── DocumentUploader.php        # Upload documentos
│       └── FAQManager.php              # Gestión FAQs
│
└── Events/
    ├── MessageReceived.php
    ├── MessageSent.php
    ├── HandoffRequested.php
    └── ConversationClosed.php
```

---

## 📏 CONVENCIONES DEL PROYECTO

### Nomenclatura de Base de Datos

#### Tablas
- Plural, snake_case: `tenants`, `bots`, `conversations`
- Tablas pivot: formato singular_singular: `bot_user`, `tenant_subscription`
- Prefijo para tablas multi-tenant: ninguno (se maneja por relaciones)

#### Columnas
- snake_case: `created_at`, `phone_number`, `knowledge_base_id`
- IDs foráneas: `{tabla_singular}_id` (ej: `tenant_id`, `bot_id`)
- Timestamps: usar `$timestamps = true` en modelos
- Soft deletes: `deleted_at` cuando aplique

#### Índices
- Formato: `idx_{tabla}_{columna(s)}`
- Ejemplo: `idx_messages_conversation_id`, `idx_bots_tenant_id`

### Nomenclatura de Código PHP

#### Modelos
- Singular, PascalCase: `Tenant`, `Bot`, `Conversation`
- Ubicación: `app/Models/`

#### Controladores
- Sufijo Controller, PascalCase: `BotController`, `DashboardController`
- Métodos CRUD estándar: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- Ubicación: `app/Http/Controllers/`

#### Services
- Sufijo Service, PascalCase: `OpenAIService`, `WhatsAppService`
- Métodos: verbos descriptivos (`sendMessage`, `processWebhook`, `generateResponse`)
- Ubicación: `app/Services/{Categoría}/`

#### Jobs
- Verbo + Sustantivo, PascalCase: `ProcessIncomingMessage`, `SendWhatsAppMessage`
- Ubicación: `app/Jobs/`

#### Livewire Components
- Sustantivo descriptivo: `ChatInterface`, `AnalyticsDashboard`
- Ubicación: `app/Livewire/{Categoría}/`

### Nomenclatura de Rutas

#### Web Routes
```php
// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Resource routes (CRUD completo)
Route::resource('bots', BotController::class);

// Rutas personalizadas con prefijos
Route::prefix('bots')->group(function () {
    Route::post('{bot}/activate', [BotController::class, 'activate'])->name('bots.activate');
    Route::post('{bot}/deactivate', [BotController::class, 'deactivate'])->name('bots.deactivate');
});
```

#### API Routes
```php
// Prefijo: /api/v1/
Route::prefix('v1')->group(function () {
    Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle']);
    Route::apiResource('bots', BotApiController::class);
});
```

### Convenciones de Git

#### Branches
- `main` - Producción
- `develop` - Desarrollo
- `feature/{nombre}` - Nueva funcionalidad
- `fix/{nombre}` - Corrección de bugs
- `hotfix/{nombre}` - Corrección urgente en producción

#### Commits
Formato: `tipo(scope): mensaje`

Tipos:
- `feat`: Nueva funcionalidad
- `fix`: Corrección de bug
- `docs`: Documentación
- `refactor`: Refactorización
- `test`: Tests
- `chore`: Tareas de mantenimiento

Ejemplos:
```
feat(bot): agregar configuración de personalidad
fix(whatsapp): corregir validación de webhook
docs(readme): actualizar instrucciones de instalación
refactor(services): simplificar OpenAIService
```

### Convenciones de Código

#### PHP
- PSR-12 coding standard
- Type hints obligatorios en parámetros y return types
- DocBlocks para métodos públicos
- Constantes en UPPER_SNAKE_CASE

```php
/**
 * Procesa un mensaje entrante de WhatsApp
 *
 * @param string $phoneNumber
 * @param string $messageText
 * @param Bot $bot
 * @return Message
 */
public function processIncomingMessage(
    string $phoneNumber,
    string $messageText,
    Bot $bot
): Message {
    // código...
}
```

#### Blade Templates
- Kebab-case para nombres de archivo: `bot-configuration.blade.php`
- Componentes: `<x-bot-card :bot="$bot" />`
- Usar @props en componentes

#### Livewire
- Propiedades públicas para datos
- Métodos públicos para acciones
- Usar validation rules en array

```php
public string $botName = '';
public array $rules = ['botName' => 'required|min:3'];

public function save(): void
{
    $this->validate();
    // código...
}
```

---

## 💰 MODELO DE NEGOCIO

### Planes de Suscripción

#### Plan Starter
- **Precio:** $99/mes
- **Bots:** 3
- **Conversaciones:** 1,000/mes
- **Usuarios:** 1
- **Soporte:** Email (48h)
- **Target:** Freelancers, pequeñas agencias

#### Plan Professional
- **Precio:** $299/mes
- **Bots:** 10
- **Conversaciones:** 5,000/mes
- **Usuarios:** 5
- **White-label:** ✅
- **Soporte:** Email + Chat (24h)
- **Target:** Agencias medianas

#### Plan Enterprise
- **Precio:** $799/mes
- **Bots:** Ilimitados
- **Conversaciones:** 20,000/mes
- **Usuarios:** Ilimitados
- **White-label:** ✅
- **API Access:** ✅
- **Soporte:** Prioritario (4h) + Account Manager
- **Target:** Agencias grandes, empresas

### Add-ons y Servicios Adicionales

- **Conversaciones extra:** $0.10/conversación adicional
- **Setup/Implementación:** $500-$2,000 por bot (según complejidad)
- **Personalización:** $150/hora
- **Training personalizado:** $500 (sesión de 2 horas)
- **Integraciones custom:** Cotización por proyecto

### Proyección de Ingresos (12 meses)

**Escenario Conservador:**
- Mes 1-3: 5 clientes Starter = $495/mes
- Mes 4-6: +10 clientes (8 Starter, 2 Professional) = $1,390/mes
- Mes 7-9: +15 clientes (10 Starter, 4 Professional, 1 Enterprise) = $3,185/mes
- Mes 10-12: +20 clientes total = $5,000-7,000/mes

**Año 1 total:** ~$40,000-50,000 (con crecimiento gradual)

---

## 🗓️ ROADMAP DE DESARROLLO

### FASE 1: MVP (10-12 semanas)

#### Sprint 0: Setup y Planificación (2-3 días) ✅
**Objetivo:** Documentación y lineamientos

- [x] Crear repositorio GitHub
- [x] Definir lineamientos del proyecto
- [x] Crear documentación base
- [x] Preparar ambiente de desarrollo

#### Sprint 0.5: Multi-tenant Enforcement (3-4 días) 🆕
**Objetivo:** Asegurar aislamiento perfecto de datos

- [ ] Crear TenantScope global
- [ ] Implementar trait BelongsToTenant
- [ ] Crear middleware TenantResolver
- [ ] Implementar tests de aislamiento
- [ ] Documentar flujo de seguridad

**Entregables:**
- Sistema multi-tenant con doble capa de seguridad
- Imposible mezclar datos entre tenants
- Tests que validan aislamiento

#### Sprint 0.9: Sistema de Permisos Granular (3-4 días) 🆕
**Objetivo:** Permisos finos por bot y usuario

- [ ] Definir roles y permisos con Spatie
- [ ] Implementar permisos por bot (bot_user pivot)
- [ ] Crear Policies para cada modelo
- [ ] Implementar Gates personalizados
- [ ] UI para asignación de permisos
- [ ] Tests de autorización

**Entregables:**
- Sistema de permisos completo
- Usuarios con acceso granular por bot
- Panel de gestión de permisos

#### Sprint 1: Fundación (Semana 1-2)
**Objetivo:** Setup completo y estructura base

- [x] Crear repositorio GitHub
- [ ] Setup proyecto Laravel 12
- [ ] Configurar base de datos multi-tenant
- [ ] Sistema de autenticación (Breeze/Jetstream)
- [ ] Implementar roles con Spatie Permission
- [ ] CRUD básico de Tenants
- [ ] CRUD básico de Bots
- [ ] Diseño de interfaz base con Tailwind
- [ ] Documentar estructura en `DATABASE_SCHEMA.md`

**Entregables:**
- Proyecto Laravel funcionando
- Login y registro
- Panel básico con sidebar
- Modelos Tenant, User, Bot creados

#### Sprint 2: Integración WhatsApp + IA (Semana 3-4)
**Objetivo:** Conectar WhatsApp y OpenAI

- [ ] Registrar cuenta WhatsApp Business API
- [ ] Configurar webhook de WhatsApp
- [ ] Crear `WhatsAppService` para envío de mensajes
- [ ] Crear `WebhookHandler` para recepción
- [ ] Integrar OpenAI API
- [ ] Crear `OpenAIService` con generación de respuestas
- [ ] Implementar sistema de Queue con Redis
- [ ] Crear Job `ProcessIncomingMessage`
- [ ] Crear Job `SendWhatsAppMessage`
- [ ] Modelos: Conversation, Message
- [ ] Probar flujo completo: recibir → procesar → responder

**Entregables:**
- Bot responde mensajes de WhatsApp
- Conversaciones guardadas en BD
- Queue funcionando correctamente
- Logs de todas las interacciones

#### Sprint 3: Knowledge Base + RAG (Semana 5-6)
**Objetivo:** Sistema de conocimiento y RAG

- [ ] Modelo KnowledgeBase y KnowledgeDocument
- [ ] Upload de documentos (PDF, TXT, DOCX)
- [ ] Procesamiento de documentos (extracción de texto)
- [ ] Sistema de embeddings con OpenAI
- [ ] Implementar búsqueda semántica (RAG)
- [ ] Job `TrainKnowledgeBase`
- [ ] Job `GenerateEmbeddings`
- [ ] Panel Livewire para gestionar documentos
- [ ] Integrar RAG en generación de respuestas
- [ ] FAQs manuales (pregunta-respuesta)

**Entregables:**
- Upload y procesamiento de documentos funcional
- RAG respondiendo con información específica
- Panel de gestión de knowledge base
- Base vectorial funcionando

#### Sprint 4: Dashboard + Handoff (Semana 7-8)
**Objetivo:** Analytics y handoff a humanos

- [ ] Analytics básico (modelo AnalyticsEvent)
- [ ] Dashboard con métricas:
  - Conversaciones totales
  - Mensajes por día/semana
  - Tasa de resolución
  - Tiempo promedio de respuesta
  - Temas más consultados
- [ ] Gráficos con Chart.js
- [ ] Sistema de handoff a humanos:
  - Detección de casos complejos
  - Notificación a agentes
  - Panel de chat en vivo (Livewire)
  - Transferencia bot → humano
- [ ] WebSockets para notificaciones real-time
- [ ] Sistema de "typing indicator"

**Entregables:**
- Dashboard funcional con gráficos
- Chat en vivo para agentes
- Handoff funcionando correctamente
- Notificaciones en tiempo real

#### Sprint 5: Testing + Deploy (Semana 9-10)
**Objetivo:** Pulir y lanzar MVP

- [ ] Testing funcional completo (manual)
- [ ] Escribir tests automatizados críticos
- [ ] Optimización de performance:
  - Índices de BD
  - Cache de consultas frecuentes
  - Eager loading
- [ ] Security audit básico
- [ ] Documentación de API
- [ ] Setup CI/CD con GitHub Actions
- [ ] Deploy a producción (HostGator)
- [ ] Configurar backups automáticos
- [ ] Documentación de usuario final
- [ ] Videos tutoriales básicos

**Entregables:**
- MVP funcional en producción
- Documentación completa
- Primer cliente piloto operando
- Landing page básica

---

### FASE 2: Expansión (Post-MVP)

#### Funcionalidades Planificadas

**Corto Plazo (1-3 meses):**
- Builder visual de flujos (drag & drop)
- Más canales: Telegram, Instagram DM
- Web widget embebible
- Plantillas de bots por industria
- Mejoras en analytics (exportar reportes)
- Sistema de billing automatizado
- Onboarding mejorado

**Medio Plazo (3-6 meses):**
- Voicebot con Twilio (llamadas)
- CRM básico integrado
- Appointment scheduling
- Integración con Zapier/Make
- A/B testing de respuestas
- Multilanguage automático
- Marketplace de integraciones

**Largo Plazo (6-12 meses):**
- Analytics predictivo con ML
- Sentiment analysis avanzado
- Auto-training de modelos
- Mobile app (iOS/Android)
- Integraciones nativas: Shopify, WooCommerce
- API pública para developers
- Sistema de afiliados

---

## 📊 MÉTRICAS DE ÉXITO

### KPIs del Producto
- **Uptime:** >99.5%
- **Tiempo respuesta promedio:** <2 segundos
- **Tasa de resolución automática:** >70%
- **Satisfacción de usuario:** >4.5/5
- **Retención de clientes:** >85% mensual

### KPIs de Negocio
- **MRR (Monthly Recurring Revenue):** Objetivo $10k en mes 12
- **Churn rate:** <10% mensual
- **CAC (Customer Acquisition Cost):** <$200
- **LTV (Lifetime Value):** >$2,400 (promedio 12 meses)
- **LTV/CAC ratio:** >3:1

---

## 🔐 CONSIDERACIONES DE SEGURIDAD

### Datos Sensibles
- API keys encriptadas en BD
- Variables de entorno (`.env`) nunca en repo
- Certificados SSL/TLS obligatorios
- Tokens con expiración y rotación

### Validación de Webhooks
- Verificar firma de Meta/WhatsApp
- Rate limiting en endpoints públicos
- Logs de intentos fallidos

### Multi-tenancy
- Middleware para aislar datos por tenant
- Queries siempre filtradas por tenant_id
- Tests de aislamiento de datos

### Compliance
- GDPR-ready (derecho al olvido)
- Almacenamiento de datos en región apropiada
- Política de privacidad clara
- Terms of Service

---

## 🚀 COMANDOS ÚTILES

### Desarrollo Local
```bash
# Iniciar servidor
php artisan serve

# Procesar queues
php artisan queue:work

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Crear componentes
php artisan make:model NombreModelo -mcr
php artisan make:livewire NombreComponente
php artisan make:job NombreJob
```

### Testing
```bash
# Ejecutar tests
php artisan test

# Con coverage
php artisan test --coverage
```

### Deploy
```bash
# Via GitHub Actions (automático en push a main)
git push origin main

# Manual (si es necesario)
ssh user@host
cd /path/to/project
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📞 CONTACTOS Y RECURSOS

### APIs y Servicios
- **WhatsApp Business API:** https://developers.facebook.com/docs/whatsapp
- **OpenAI API:** https://platform.openai.com/docs
- **Twilio:** https://www.twilio.com/docs
- **Laravel Docs:** https://laravel.com/docs/12.x

### Soporte Técnico
- **GitHub Issues:** [Repo BotHub]
- **Email:** victor@bothub.com (ejemplo)
- **Documentación:** /docs (cuando esté disponible)

---

## 📝 NOTAS FINALES

### Principios del Proyecto
1. **Simplicidad primero:** Código legible y mantenible
2. **Documentación continua:** Si no está documentado, no existe
3. **Testing esencial:** Funcionalidades críticas siempre testeadas
4. **Performance matters:** Optimizar desde el inicio
5. **Security by default:** Nunca comprometer seguridad por rapidez

### Para Desarrolladores
- Leer este documento completo antes de empezar
- Seguir convenciones al pie de la letra
- Documentar cambios significativos
- Hacer commits atómicos y descriptivos
- Pedir ayuda cuando sea necesario

---

**Fin del documento maestro v1.0.0**

*Este documento es vivo y debe actualizarse conforme el proyecto evoluciona.*
