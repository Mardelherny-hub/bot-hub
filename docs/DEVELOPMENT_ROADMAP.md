# BOTHUB - DEVELOPMENT ROADMAP

**Versión:** 1.0.0  
**Última actualización:** 13 de Noviembre, 2025  
**Duración estimada MVP:** 10-12 semanas  
**Estado actual:** Sprint 0 - Planificación (Completado)

---

## 📋 TABLA DE CONTENIDOS

1. [Visión General](#visión-general)
2. [Sprint 0: Setup y Planificación](#sprint-0-setup-y-planificación)
3. [Sprint 1: Fundación](#sprint-1-fundación)
4. [Sprint 2: WhatsApp + IA](#sprint-2-whatsapp--ia)
5. [Sprint 3: Knowledge Base + RAG](#sprint-3-knowledge-base--rag)
6. [Sprint 4: Dashboard + Handoff](#sprint-4-dashboard--handoff)
7. [Sprint 5: Testing + Deploy](#sprint-5-testing--deploy)
8. [Post-MVP: Roadmap Futuro](#post-mvp-roadmap-futuro)
9. [Tracking de Progreso](#tracking-de-progreso)

---

## 🎯 VISIÓN GENERAL

### Objetivo del MVP
Crear una plataforma funcional que permita a una agencia o empresa:
1. Crear y configurar bots de WhatsApp con IA
2. Entrenar el bot con su propia información
3. Recibir y responder mensajes automáticamente
4. Transferir conversaciones a humanos cuando sea necesario
5. Ver métricas básicas de rendimiento

### Criterios de Éxito
- ✅ Bot responde correctamente 70%+ de las consultas
- ✅ Tiempo de respuesta < 3 segundos
- ✅ Sistema procesa 100+ mensajes/minuto sin degradación
- ✅ Primera venta (cliente piloto) cerrada
- ✅ Feedback positivo de usuarios beta

### Stack Tecnológico Confirmado
- Laravel 12 + PHP 8.3
- MySQL 8.0 + Redis
- Livewire 3 + Tailwind CSS
- WhatsApp Business API + OpenAI GPT-4
- GitHub + GitHub Actions

---

## 🚀 SPRINT 0: SETUP Y PLANIFICACIÓN

**Duración:** 2-3 días  
**Estado:** ✅ Completado  
**Fecha:** 13-15 Noviembre 2025

### Objetivos
- [x] Definir lineamientos del proyecto
- [x] Crear documentación base
- [x] Configurar repositorio
- [x] Preparar ambiente de desarrollo

### Tareas Completadas

#### Documentación
- [x] BOTHUB_MASTER_DOC.md
- [x] DATABASE_SCHEMA.md
- [x] API_INTEGRATIONS.md
- [x] DEVELOPMENT_ROADMAP.md

#### Próximos Pasos
- [ ] Crear proyecto en Claude (este paso)
- [ ] Crear repositorio GitHub
- [ ] Setup inicial de Laravel 12

---

## 🔒 SPRINT 0.5: MULTI-TENANT ENFORCEMENT

**Duración:** 3-4 días  
**Estado:** ⏳ Pendiente  
**Fecha estimada:** 16-19 Noviembre 2025

### Objetivos Principales
1. Implementar TenantScope global
2. Crear trait BelongsToTenant
3. Implementar middleware TenantResolver
4. Tests exhaustivos de aislamiento
5. Garantizar seguridad multi-tenant perfecta

### ¿Por Qué Este Sprint Es Crítico?

El middleware solo NO es suficiente para proteger datos en multi-tenancy. Necesitamos **doble capa de seguridad**:

1. **Global Scope:** Filtra automáticamente TODAS las queries
2. **Middleware:** Identifica y valida el tenant activo

Esto previene:
- ❌ Fugas de datos por error humano
- ❌ Queries sin filtro de tenant_id
- ❌ Acceso accidental a datos de otro tenant

### Tareas Detalladas

#### Día 1: TenantScope Global

**Checklist:**
- [ ] Crear Scope global
  ```bash
  php artisan make:scope TenantScope
  ```

- [ ] Implementar TenantScope
  ```php
  // app/Models/Scopes/TenantScope.php
  namespace App\Models\Scopes;
  
  use Illuminate\Database\Eloquent\Builder;
  use Illuminate\Database\Eloquent\Model;
  use Illuminate\Database\Eloquent\Scope;
  
  class TenantScope implements Scope
  {
      public function apply(Builder $builder, Model $model)
      {
          if (auth()->check() && auth()->user()->tenant_id) {
              $builder->where('tenant_id', auth()->user()->tenant_id);
          }
      }
  }
  ```

- [ ] Crear trait BelongsToTenant
  ```bash
  php artisan make:trait BelongsToTenant
  ```

- [ ] Implementar trait
  ```php
  // app/Models/Concerns/BelongsToTenant.php
  namespace App\Models\Concerns;
  
  use App\Models\Scopes\TenantScope;
  use App\Models\Tenant;
  use Illuminate\Database\Eloquent\Relations\BelongsTo;
  
  trait BelongsToTenant
  {
      protected static function bootBelongsToTenant()
      {
          static::addGlobalScope(new TenantScope);
          
          static::creating(function ($model) {
              if (auth()->check() && !$model->tenant_id) {
                  $model->tenant_id = auth()->user()->tenant_id;
              }
          });
      }
      
      public function tenant(): BelongsTo
      {
          return $this->belongsTo(Tenant::class);
      }
  }
  ```

- [ ] Aplicar trait a modelos
  ```php
  // En Bot, Conversation, KnowledgeBase, etc.
  use BelongsToTenant;
  ```

- [ ] Probar con Tinker
  ```bash
  php artisan tinker
  >>> Bot::count(); // Solo del tenant actual
  ```

#### Día 2: Middleware TenantResolver

**Checklist:**
- [ ] Crear middleware
  ```bash
  php artisan make:middleware TenantResolver
  ```

- [ ] Implementar middleware
  ```php
  namespace App\Http\Middleware;
  
  use Closure;
  use Illuminate\Http\Request;
  
  class TenantResolver
  {
      public function handle(Request $request, Closure $next)
      {
          if (!auth()->check()) {
              return $next($request);
          }
          
          $user = auth()->user();
          
          // Verificar que el usuario tiene tenant
          if (!$user->tenant_id) {
              abort(403, 'Usuario sin tenant asignado');
          }
          
          // Setear tenant en contexto
          app()->instance('tenant', $user->tenant);
          
          // Log de acceso para auditoría
          \Log::info('Tenant access', [
              'user_id' => $user->id,
              'tenant_id' => $user->tenant_id,
              'ip' => $request->ip(),
              'url' => $request->url(),
          ]);
          
          return $next($request);
      }
  }
  ```

- [ ] Registrar middleware
  ```php
  // app/Http/Kernel.php
  protected $middlewareGroups = [
      'web' => [
          // ...
          \App\Http\Middleware\TenantResolver::class,
      ],
  ];
  ```

- [ ] Probar acceso

#### Día 3: Tests de Aislamiento

**Checklist:**
- [ ] Crear test suite
  ```bash
  php artisan make:test TenantIsolationTest
  ```

- [ ] Implementar tests críticos
  ```php
  public function test_users_can_only_see_their_tenant_bots()
  {
      $tenant1 = Tenant::factory()->create();
      $tenant2 = Tenant::factory()->create();
      
      $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);
      $user2 = User::factory()->create(['tenant_id' => $tenant2->id]);
      
      $bot1 = Bot::factory()->create(['tenant_id' => $tenant1->id]);
      $bot2 = Bot::factory()->create(['tenant_id' => $tenant2->id]);
      
      $this->actingAs($user1);
      
      // User1 solo ve bot1
      $this->assertEquals(1, Bot::count());
      $this->assertEquals($bot1->id, Bot::first()->id);
      
      // User1 NO puede acceder a bot2
      $this->assertNull(Bot::find($bot2->id));
  }
  
  public function test_tenant_scope_prevents_cross_tenant_queries()
  {
      $tenant1 = Tenant::factory()->create();
      $tenant2 = Tenant::factory()->create();
      
      $user = User::factory()->create(['tenant_id' => $tenant1->id]);
      
      Bot::factory()->count(5)->create(['tenant_id' => $tenant1->id]);
      Bot::factory()->count(3)->create(['tenant_id' => $tenant2->id]);
      
      $this->actingAs($user);
      
      // Solo ve los 5 bots de su tenant
      $this->assertEquals(5, Bot::count());
  }
  
  public function test_creating_model_auto_assigns_tenant()
  {
      $tenant = Tenant::factory()->create();
      $user = User::factory()->create(['tenant_id' => $tenant->id]);
      
      $this->actingAs($user);
      
      $bot = Bot::create([
          'name' => 'Test Bot',
          'phone_number' => '+5492231234567',
      ]);
      
      // Tenant se asigna automáticamente
      $this->assertEquals($tenant->id, $bot->tenant_id);
  }
  ```

- [ ] Ejecutar tests
  ```bash
  php artisan test --filter TenantIsolation
  ```

#### Día 4: Documentación y Validación Final

**Checklist:**
- [ ] Documentar flujo de seguridad
  - Diagrama de flujo
  - Casos edge
  - Cómo bypassear scope (cuando sea necesario)

- [ ] Crear helper para bypass
  ```php
  // Cuando super_admin necesita ver todos
  Bot::withoutGlobalScope(TenantScope::class)->get();
  ```

- [ ] Actualizar BOTHUB_MASTER_DOC.md
  - Sección de seguridad multi-tenant
  - Código de ejemplo

- [ ] Code review completo

- [ ] Deploy a develop branch

### Entregables Sprint 0.5
- ✅ TenantScope implementado en todos los modelos
- ✅ Middleware TenantResolver funcionando
- ✅ Suite de tests pasando (100% aislamiento)
- ✅ Documentación actualizada
- ✅ Sistema multi-tenant robusto y seguro

### Criterios de Aceptación
- [ ] Es imposible ver datos de otro tenant (tests lo validan)
- [ ] Modelos se crean con tenant_id automático
- [ ] Queries están filtradas por tenant sin código extra
- [ ] Super admin puede ver todos los tenants cuando necesario
- [ ] Middleware registra accesos para auditoría

---

## 🔑 SPRINT 0.9: SISTEMA DE PERMISOS GRANULAR

**Duración:** 3-4 días  
**Estado:** ⏳ Pendiente  
**Fecha estimada:** 20-23 Noviembre 2025

### Objetivos Principales
1. Implementar roles con Spatie Permission
2. Crear permisos por bot (bot_user pivot)
3. Implementar Policies para autorización
4. UI para gestión de permisos
5. Tests de autorización completos

### ¿Por Qué Este Sprint Es Crítico?

Un bot puede tener múltiples usuarios con diferentes niveles de acceso:
- Supervisor: Ve todo pero no modifica
- Agente: Solo chatea en conversaciones asignadas
- Viewer: Solo ve métricas
- Admin: Control total

Esto NO se puede hacer solo con roles globales - necesitamos permisos **por bot**.

### Tareas Detalladas

#### Día 1: Roles Base con Spatie

**Checklist:**
- [ ] Ya está instalado Spatie Permission (Sprint 1)

- [ ] Crear Seeder de roles
  ```bash
  php artisan make:seeder RolePermissionSeeder
  ```

- [ ] Definir roles y permisos
  ```php
  // database/seeders/RolePermissionSeeder.php
  
  // Roles globales
  Role::create(['name' => 'super_admin']);
  Role::create(['name' => 'admin']);
  Role::create(['name' => 'supervisor']);
  Role::create(['name' => 'agent']);
  Role::create(['name' => 'viewer']);
  
  // Permisos generales
  Permission::create(['name' => 'manage tenants']);
  Permission::create(['name' => 'manage users']);
  Permission::create(['name' => 'view analytics']);
  Permission::create(['name' => 'manage billing']);
  
  // Asignar permisos a roles
  $superAdmin = Role::findByName('super_admin');
  $superAdmin->givePermissionTo(Permission::all());
  
  $admin = Role::findByName('admin');
  $admin->givePermissionTo([
      'manage users',
      'view analytics',
      'manage billing'
  ]);
  ```

- [ ] Ejecutar seeder
  ```bash
  php artisan db:seed --class=RolePermissionSeeder
  ```

#### Día 2: Permisos Por Bot (Pivot Table)

**Checklist:**
- [ ] La tabla `bot_user` ya existe con campos de permisos

- [ ] Crear modelo BotUser
  ```bash
  php artisan make:model BotUser
  ```

- [ ] Implementar modelo
  ```php
  namespace App\Models;
  
  class BotUser extends Model
  {
      protected $table = 'bot_user';
      
      protected $fillable = [
          'bot_id',
          'user_id',
          'can_manage',
          'can_view_analytics',
          'can_chat',
          'can_train_kb',
          'can_delete_data',
      ];
      
      protected $casts = [
          'can_manage' => 'boolean',
          'can_view_analytics' => 'boolean',
          'can_chat' => 'boolean',
          'can_train_kb' => 'boolean',
          'can_delete_data' => 'boolean',
      ];
      
      public function bot()
      {
          return $this->belongsTo(Bot::class);
      }
      
      public function user()
      {
          return $this->belongsTo(User::class);
      }
  }
  ```

- [ ] Actualizar relaciones en modelos
  ```php
  // En Bot.php
  public function users()
  {
      return $this->belongsToMany(User::class, 'bot_user')
          ->withPivot([
              'can_manage',
              'can_view_analytics',
              'can_chat',
              'can_train_kb',
              'can_delete_data'
          ])
          ->withTimestamps();
  }
  
  // En User.php
  public function bots()
  {
      return $this->belongsToMany(Bot::class, 'bot_user')
          ->withPivot([
              'can_manage',
              'can_view_analytics',
              'can_chat',
              'can_train_kb',
              'can_delete_data'
          ])
          ->withTimestamps();
  }
  
  public function canManageBot(Bot $bot): bool
  {
      // Super admin puede todo
      if ($this->hasRole('super_admin')) {
          return true;
      }
      
      // Admin del tenant puede todo en su tenant
      if ($this->hasRole('admin') && $this->tenant_id === $bot->tenant_id) {
          return true;
      }
      
      // Revisar permisos específicos del bot
      $pivot = $this->bots()->where('bot_id', $bot->id)->first()?->pivot;
      return $pivot?->can_manage ?? false;
  }
  
  public function canChatInBot(Bot $bot): bool
  {
      if ($this->hasRole('super_admin')) return true;
      if ($this->hasRole('admin') && $this->tenant_id === $bot->tenant_id) return true;
      
      $pivot = $this->bots()->where('bot_id', $bot->id)->first()?->pivot;
      return $pivot?->can_chat ?? false;
  }
  
  // Similar para otros permisos...
  ```

#### Día 3: Policies de Autorización

**Checklist:**
- [ ] Crear Policy para Bot
  ```bash
  php artisan make:policy BotPolicy --model=Bot
  ```

- [ ] Implementar Policy
  ```php
  namespace App\Policies;
  
  class BotPolicy
  {
      public function viewAny(User $user): bool
      {
          return $user->hasAnyRole(['super_admin', 'admin', 'supervisor', 'agent', 'viewer']);
      }
      
      public function view(User $user, Bot $bot): bool
      {
          if ($user->hasRole('super_admin')) return true;
          if ($user->tenant_id !== $bot->tenant_id) return false;
          
          // Si está asignado al bot, puede verlo
          return $user->bots->contains($bot->id);
      }
      
      public function create(User $user): bool
      {
          return $user->hasAnyRole(['super_admin', 'admin']);
      }
      
      public function update(User $user, Bot $bot): bool
      {
          return $user->canManageBot($bot);
      }
      
      public function delete(User $user, Bot $bot): bool
      {
          return $user->canManageBot($bot);
      }
      
      public function chat(User $user, Bot $bot): bool
      {
          return $user->canChatInBot($bot);
      }
      
      public function viewAnalytics(User $user, Bot $bot): bool
      {
          if ($user->hasRole('super_admin')) return true;
          if ($user->hasAnyRole(['admin', 'supervisor']) && $user->tenant_id === $bot->tenant_id) return true;
          
          $pivot = $user->bots()->where('bot_id', $bot->id)->first()?->pivot;
          return $pivot?->can_view_analytics ?? false;
      }
      
      public function trainKnowledgeBase(User $user, Bot $bot): bool
      {
          if ($user->hasRole('super_admin')) return true;
          if ($user->hasRole('admin') && $user->tenant_id === $bot->tenant_id) return true;
          
          $pivot = $user->bots()->where('bot_id', $bot->id)->first()?->pivot;
          return $pivot?->can_train_kb ?? false;
      }
  }
  ```

- [ ] Registrar Policy
  ```php
  // app/Providers/AuthServiceProvider.php
  protected $policies = [
      Bot::class => BotPolicy::class,
  ];
  ```

- [ ] Usar en controladores
  ```php
  public function update(Request $request, Bot $bot)
  {
      $this->authorize('update', $bot);
      // ... código
  }
  
  public function chat(Bot $bot)
  {
      $this->authorize('chat', $bot);
      // ... código
  }
  ```

- [ ] Crear Policies para otros modelos
  ```bash
  php artisan make:policy ConversationPolicy --model=Conversation
  php artisan make:policy KnowledgeBasePolicy --model=KnowledgeBase
  ```

#### Día 4: UI de Gestión de Permisos

**Checklist:**
- [ ] Crear componente Livewire
  ```bash
  php artisan make:livewire Bot/ManageBotUsers
  ```

- [ ] Implementar componente
  ```php
  namespace App\Livewire\Bot;
  
  use Livewire\Component;
  use App\Models\Bot;
  use App\Models\User;
  
  class ManageBotUsers extends Component
  {
      public Bot $bot;
      public $selectedUserId;
      public $permissions = [
          'can_manage' => false,
          'can_view_analytics' => false,
          'can_chat' => false,
          'can_train_kb' => false,
          'can_delete_data' => false,
      ];
      
      public function mount(Bot $bot)
      {
          $this->authorize('update', $bot);
          $this->bot = $bot;
      }
      
      public function assignUser()
      {
          $this->validate([
              'selectedUserId' => 'required|exists:users,id',
          ]);
          
          $user = User::find($this->selectedUserId);
          
          // Verificar que el usuario es del mismo tenant
          if ($user->tenant_id !== $this->bot->tenant_id) {
              session()->flash('error', 'Usuario no pertenece al mismo tenant');
              return;
          }
          
          $this->bot->users()->attach($user->id, $this->permissions);
          
          session()->flash('success', 'Usuario asignado correctamente');
          $this->reset(['selectedUserId', 'permissions']);
      }
      
      public function updatePermissions($userId)
      {
          $this->bot->users()->updateExistingPivot($userId, $this->permissions);
          session()->flash('success', 'Permisos actualizados');
      }
      
      public function removeUser($userId)
      {
          $this->bot->users()->detach($userId);
          session()->flash('success', 'Usuario removido');
      }
      
      public function render()
      {
          $assignedUsers = $this->bot->users;
          $availableUsers = User::where('tenant_id', $this->bot->tenant_id)
              ->whereNotIn('id', $assignedUsers->pluck('id'))
              ->get();
          
          return view('livewire.bot.manage-bot-users', [
              'assignedUsers' => $assignedUsers,
              'availableUsers' => $availableUsers,
          ]);
      }
  }
  ```

- [ ] Crear vista
  ```blade
  {{-- resources/views/livewire/bot/manage-bot-users.blade.php --}}
  <div>
      <h3>Usuarios asignados al bot</h3>
      
      @foreach($assignedUsers as $user)
          <div class="user-card">
              <span>{{ $user->name }}</span>
              <div class="permissions">
                  @if($user->pivot->can_manage) <span>Gestionar</span> @endif
                  @if($user->pivot->can_chat) <span>Chatear</span> @endif
                  @if($user->pivot->can_view_analytics) <span>Analytics</span> @endif
              </div>
              <button wire:click="removeUser({{ $user->id }})">Remover</button>
          </div>
      @endforeach
      
      <h4>Asignar nuevo usuario</h4>
      <select wire:model="selectedUserId">
          <option value="">Seleccionar usuario</option>
          @foreach($availableUsers as $user)
              <option value="{{ $user->id }}">{{ $user->name }}</option>
          @endforeach
      </select>
      
      <div class="permissions-checkboxes">
          <label><input type="checkbox" wire:model="permissions.can_manage"> Gestionar</label>
          <label><input type="checkbox" wire:model="permissions.can_chat"> Chatear</label>
          <label><input type="checkbox" wire:model="permissions.can_view_analytics"> Ver analytics</label>
          <label><input type="checkbox" wire:model="permissions.can_train_kb"> Entrenar KB</label>
          <label><input type="checkbox" wire:model="permissions.can_delete_data"> Eliminar datos</label>
      </div>
      
      <button wire:click="assignUser">Asignar</button>
  </div>
  ```

- [ ] Integrar en vista de configuración del bot

### Entregables Sprint 0.9
- ✅ Roles base implementados con Spatie
- ✅ Permisos por bot funcionando (pivot table)
- ✅ Policies implementadas para todos los modelos críticos
- ✅ UI para asignar usuarios a bots con permisos
- ✅ Tests de autorización pasando

### Criterios de Aceptación
- [ ] Usuarios solo ven bots a los que están asignados
- [ ] Permisos se respetan en toda la aplicación
- [ ] Admin puede asignar usuarios a bots con permisos específicos
- [ ] Super admin puede hacer todo
- [ ] Tests validan todos los casos de autorización

---

## 📦 SPRINT 1: FUNDACIÓN

**Duración:** 2 semanas (Semana 1-2)  
**Estado:** ⏳ Pendiente  
**Fecha estimada:** 16-29 Noviembre 2025

### Objetivos Principales
1. Setup completo del proyecto Laravel
2. Base de datos con multi-tenancy
3. Sistema de autenticación y roles
4. CRUD básico funcional
5. Interfaz base con Tailwind

### Tareas Detalladas

#### Día 1-2: Setup Inicial del Proyecto

**Checklist:**
- [ ] Crear repositorio en GitHub
  - Nombre: `bothub`
  - Visibilidad: Private
  - README.md inicial
  - .gitignore para Laravel
  
- [ ] Instalar Laravel 12
  ```bash
  composer create-project laravel/laravel bothub
  cd bothub
  php artisan --version # Verificar versión
  ```

- [ ] Configurar base de datos
  ```bash
  # Crear BD en MySQL
  CREATE DATABASE bothub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'bothub_user'@'localhost' IDENTIFIED BY 'password';
  GRANT ALL PRIVILEGES ON bothub.* TO 'bothub_user'@'localhost';
  ```

- [ ] Configurar .env
  ```
  APP_NAME=BotHub
  DB_DATABASE=bothub
  DB_USERNAME=bothub_user
  DB_PASSWORD=password
  ```

- [ ] Instalar Redis
  ```bash
  sudo apt-get install redis-server
  redis-server --version
  ```

- [ ] Configurar Queue
  ```
  QUEUE_CONNECTION=redis
  ```

- [ ] Primer commit
  ```bash
  git add .
  git commit -m "chore: initial Laravel 12 setup"
  git push origin main
  ```

#### Día 3-4: Autenticación y Estructura Base

**Checklist:**
- [ ] Instalar Laravel Breeze
  ```bash
  composer require laravel/breeze --dev
  php artisan breeze:install blade
  npm install && npm run build
  php artisan migrate
  ```

- [ ] Instalar Livewire 3
  ```bash
  composer require livewire/livewire
  ```

- [ ] Instalar Spatie Permission
  ```bash
  composer require spatie/laravel-permission
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  php artisan migrate
  ```

- [ ] Crear roles básicos (seeder)
  ```php
  // database/seeders/RoleSeeder.php
  Role::create(['name' => 'super_admin']);
  Role::create(['name' => 'admin']);
  Role::create(['name' => 'agent']);
  Role::create(['name' => 'viewer']);
  ```

- [ ] Actualizar modelo User
  ```php
  use HasRoles; // Trait de Spatie
  protected $fillable = ['tenant_id', 'name', 'email', 'password', 'role'];
  ```

#### Día 5-7: Migrations de Base de Datos

**Checklist:**
- [ ] Crear migration: tenants
  ```bash
  php artisan make:migration create_tenants_table
  ```

- [ ] Crear migration: bots
  ```bash
  php artisan make:migration create_bots_table
  ```

- [ ] Crear migration: conversations
  ```bash
  php artisan make:migration create_conversations_table
  ```

- [ ] Crear migration: messages
  ```bash
  php artisan make:migration create_messages_table
  ```

- [ ] Crear migration: knowledge_bases
  ```bash
  php artisan make:migration create_knowledge_bases_table
  ```

- [ ] Crear migration: knowledge_documents
  ```bash
  php artisan make:migration create_knowledge_documents_table
  ```

- [ ] Crear migration: document_chunks
  ```bash
  php artisan make:migration create_document_chunks_table
  ```

- [ ] Crear migration: webhooks
  ```bash
  php artisan make:migration create_webhooks_table
  ```

- [ ] Crear migration: analytics_events
  ```bash
  php artisan make:migration create_analytics_events_table
  ```

- [ ] Crear migration: bot_user (pivot)
  ```bash
  php artisan make:migration create_bot_user_table
  ```

- [ ] Crear migration: api_keys
  ```bash
  php artisan make:migration create_api_keys_table
  ```

- [ ] Crear migration: notifications
  ```bash
  php artisan make:migration create_notifications_table
  ```

- [ ] Crear migration: audit_logs
  ```bash
  php artisan make:migration create_audit_logs_table
  ```

- [ ] Crear migration: tenant_usage_reports
  ```bash
  php artisan make:migration create_tenant_usage_reports_table
  ```

- [ ] Ejecutar migraciones
  ```bash
  php artisan migrate
  ```

- [ ] Verificar estructura en BD
  ```bash
  php artisan db:show
  ```

**Nota:** Seguir exactamente el schema de DATABASE_SCHEMA.md

#### Día 8-10: Modelos y Relaciones

**Checklist:**
- [ ] Crear modelo: Tenant
  ```bash
  php artisan make:model Tenant
  ```
  - Definir fillable
  - Relación hasMany con User
  - Relación hasMany con Bot
  - Scopes para subscripción activa

- [ ] Actualizar modelo: User
  - Relación belongsTo con Tenant
  - Relación belongsToMany con Bot
  - Trait HasRoles

- [ ] Crear modelo: Bot
  ```bash
  php artisan make:model Bot
  ```
  - Relación belongsTo con Tenant
  - Relación hasMany con Conversation
  - Relación hasOne con KnowledgeBase
  - Relación belongsToMany con User

- [ ] Crear modelo: Conversation
  ```bash
  php artisan make:model Conversation
  ```
  - Relación belongsTo con Bot
  - Relación hasMany con Message
  - Relación belongsTo con User (assigned)

- [ ] Crear modelo: Message
  ```bash
  php artisan make:model Message
  ```
  - Relación belongsTo con Conversation
  - Relación belongsTo con User (sender)

- [ ] Crear modelo: KnowledgeBase
  ```bash
  php artisan make:model KnowledgeBase
  ```
  - Relación belongsTo con Bot
  - Relación hasMany con KnowledgeDocument

- [ ] Crear modelo: KnowledgeDocument
  ```bash
  php artisan make:model KnowledgeDocument
  ```
  - Relación belongsTo con KnowledgeBase
  - Relación hasMany con DocumentChunk

- [ ] Crear modelo: DocumentChunk
  ```bash
  php artisan make:model DocumentChunk
  ```
  - Relación belongsTo con KnowledgeDocument

- [ ] Verificar relaciones con Tinker
  ```bash
  php artisan tinker
  >>> $tenant = Tenant::first();
  >>> $tenant->users;
  >>> $tenant->bots;
  ```

#### Día 11-12: CRUD de Tenants

**Checklist:**
- [ ] Crear controlador
  ```bash
  php artisan make:controller TenantController --resource
  ```

- [ ] Crear rutas
  ```php
  Route::resource('tenants', TenantController::class)
      ->middleware(['auth', 'role:super_admin']);
  ```

- [ ] Crear vistas Blade
  - [ ] tenants/index.blade.php (listado)
  - [ ] tenants/create.blade.php (crear)
  - [ ] tenants/edit.blade.php (editar)
  - [ ] tenants/show.blade.php (ver detalle)

- [ ] Implementar métodos del controlador
  - [ ] index()
  - [ ] create()
  - [ ] store()
  - [ ] show()
  - [ ] edit()
  - [ ] update()
  - [ ] destroy()

- [ ] Validación de formularios
  ```php
  php artisan make:request StoreTenantRequest
  php artisan make:request UpdateTenantRequest
  ```

- [ ] Probar CRUD completo

#### Día 13-14: CRUD de Bots

**Checklist:**
- [ ] Crear controlador
  ```bash
  php artisan make:controller BotController --resource
  ```

- [ ] Middleware para verificar ownership
  ```bash
  php artisan make:middleware CheckBotOwnership
  ```

- [ ] Crear rutas
  ```php
  Route::resource('bots', BotController::class)
      ->middleware(['auth', 'check.bot.ownership']);
  ```

- [ ] Crear vistas Blade
  - [ ] bots/index.blade.php
  - [ ] bots/create.blade.php
  - [ ] bots/edit.blade.php
  - [ ] bots/show.blade.php

- [ ] Implementar métodos del controlador
  - [ ] index() - solo bots del tenant
  - [ ] create()
  - [ ] store()
  - [ ] show()
  - [ ] edit()
  - [ ] update()
  - [ ] destroy()

- [ ] Validación de formularios
  ```php
  php artisan make:request StoreBotRequest
  php artisan make:request UpdateBotRequest
  ```

- [ ] Probar CRUD completo

#### Día 15-16: Interfaz Base con Tailwind

**Checklist:**
- [ ] Configurar Tailwind (ya viene con Breeze)
  ```bash
  npm install -D @tailwindcss/forms
  ```

- [ ] Crear layout principal
  - [ ] resources/views/layouts/app.blade.php
  - Sidebar colapsable
  - Header con usuario
  - Notificaciones
  - Menú de navegación

- [ ] Crear componentes Blade reutilizables
  - [ ] components/sidebar.blade.php
  - [ ] components/header.blade.php
  - [ ] components/card.blade.php
  - [ ] components/button.blade.php
  - [ ] components/modal.blade.php

- [ ] Crear dashboard básico
  - [ ] DashboardController
  - [ ] views/dashboard.blade.php
  - Mostrar:
    - Total de bots
    - Conversaciones activas
    - Mensajes hoy
    - Gráfico placeholder

- [ ] Responsive design
  - [ ] Mobile-friendly
  - [ ] Tablet view
  - [ ] Desktop view

- [ ] Dark mode (opcional)

### Entregables Sprint 1
- ✅ Proyecto Laravel 12 configurado y funcional
- ✅ Base de datos con todas las tablas creadas
- ✅ Autenticación con roles implementada
- ✅ CRUD de Tenants funcional
- ✅ CRUD de Bots funcional
- ✅ Interfaz base con Tailwind responsive
- ✅ Código en GitHub con commits descriptivos

### Criterios de Aceptación
- [ ] Usuario puede registrarse y hacer login
- [ ] Super admin puede crear tenants
- [ ] Admin de tenant puede crear bots
- [ ] Interfaz es clara y navegable
- [ ] No hay errores en consola
- [ ] Código sigue convenciones de Laravel

---

## 🤝 SPRINT 2: WHATSAPP + IA

**Duración:** 2 semanas (Semana 3-4)  
**Estado:** ⏳ Pendiente  
**Fecha estimada:** 30 Nov - 13 Dic 2025

### Objetivos Principales
1. Integrar WhatsApp Business API
2. Configurar y recibir webhooks
3. Integrar OpenAI GPT-4
4. Implementar queue system
5. Flujo completo: recibir → procesar → responder

### Tareas Detalladas

#### Día 1-2: Configuración WhatsApp Business API

**Checklist:**
- [ ] Crear cuenta Meta Business
  - https://business.facebook.com
  
- [ ] Configurar WhatsApp Business Platform
  - Agregar app de WhatsApp
  - Verificar número de teléfono
  - Obtener credenciales

- [ ] Guardar en .env
  ```
  WHATSAPP_ACCESS_TOKEN=xxxxx
  WHATSAPP_PHONE_NUMBER_ID=xxxxx
  WHATSAPP_BUSINESS_ACCOUNT_ID=xxxxx
  WHATSAPP_WEBHOOK_VERIFY_TOKEN=mi_token_secreto_123
  ```

- [ ] Actualizar config/services.php
  ```php
  'whatsapp' => [
      'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
      'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
      'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
      'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
  ],
  ```

- [ ] Probar envío manual con Postman

#### Día 3-4: Crear WhatsAppService

**Checklist:**
- [ ] Crear servicio
  ```bash
  php artisan make:class Services/Messaging/WhatsAppService
  ```

- [ ] Implementar métodos:
  - [ ] sendTextMessage(string $to, string $message)
  - [ ] sendImage(string $to, string $imageUrl, ?string $caption)
  - [ ] sendDocument(string $to, string $docUrl, string $filename)
  - [ ] markAsRead(string $messageId)
  - [ ] downloadMedia(string $mediaId)

- [ ] Tests básicos
  ```bash
  php artisan make:test WhatsAppServiceTest
  ```

- [ ] Probar envío real a número de prueba

#### Día 5-6: Configurar Webhooks

**Checklist:**
- [ ] Crear controlador de webhook
  ```bash
  php artisan make:controller Webhook/WhatsAppWebhookController
  ```

- [ ] Implementar verificación (GET)
  ```php
  public function verify(Request $request)
  {
      // Verificar hub.verify_token
      // Retornar hub.challenge
  }
  ```

- [ ] Implementar recepción (POST)
  ```php
  public function handle(Request $request)
  {
      // Validar firma de Meta
      // Procesar payload
      // Retornar 200 rápido
  }
  ```

- [ ] Crear ruta pública
  ```php
  Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify']);
  Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle']);
  ```

- [ ] Exponer webhook con ngrok (desarrollo)
  ```bash
  ngrok http 8000
  # Copiar URL: https://xxxx.ngrok.io
  ```

- [ ] Configurar webhook en Meta
  - URL: https://xxxx.ngrok.io/api/webhooks/whatsapp
  - Verify token: mi_token_secreto_123
  - Eventos: messages, message_status

- [ ] Probar recepción enviando mensaje desde WhatsApp

#### Día 7-8: Crear WebhookHandler

**Checklist:**
- [ ] Crear handler
  ```bash
  php artisan make:class Services/Messaging/WebhookHandler
  ```

- [ ] Implementar métodos:
  - [ ] handle(array $payload): void
  - [ ] handleIncomingMessages(array $value): void
  - [ ] handleMessageStatuses(array $value): void

- [ ] Crear Job para procesamiento
  ```bash
  php artisan make:job ProcessIncomingMessage
  ```

- [ ] Implementar Job:
  - Recibir datos del mensaje
  - Encontrar bot por phone_number_id
  - Encontrar o crear conversación
  - Guardar mensaje en BD
  - Disparar procesamiento de IA

- [ ] Configurar Queue
  ```bash
  php artisan queue:table
  php artisan migrate
  ```

- [ ] Probar queue
  ```bash
  php artisan queue:work
  ```

#### Día 9-10: Integrar OpenAI

**Checklist:**
- [ ] Obtener API key de OpenAI
  - https://platform.openai.com/api-keys

- [ ] Configurar en .env
  ```
  OPENAI_API_KEY=sk-proj-xxxxx
  ```

- [ ] Crear OpenAIService
  ```bash
  php artisan make:class Services/AI/OpenAIService
  ```

- [ ] Implementar métodos:
  - [ ] generateResponse(array $messages, float $temperature, int $maxTokens)
  - [ ] createEmbedding(string $text)
  - [ ] createEmbeddings(array $texts)

- [ ] Probar con Tinker
  ```bash
  php artisan tinker
  >>> $service = app(OpenAIService::class);
  >>> $response = $service->generateResponse([
          ['role' => 'user', 'content' => 'Hola']
      ]);
  ```

#### Día 11-12: Crear PromptBuilder

**Checklist:**
- [ ] Crear service
  ```bash
  php artisan make:class Services/AI/PromptBuilder
  ```

- [ ] Implementar métodos:
  - [ ] buildSystemPrompt(Bot $bot): string
  - [ ] buildConversationContext(Conversation $conv, int $limit): array
  - [ ] addKnowledgeContext(string $message, array $results): string

- [ ] Probar construcción de prompts

#### Día 13-14: Flujo Completo de Procesamiento

**Checklist:**
- [ ] Crear MessageProcessor
  ```bash
  php artisan make:class Services/Messaging/MessageProcessor
  ```

- [ ] Implementar flujo:
  1. Recibir mensaje desde Job
  2. Obtener bot y configuración
  3. Crear/actualizar conversación
  4. Guardar mensaje del usuario
  5. Construir prompt con PromptBuilder
  6. Llamar a OpenAI
  7. Guardar respuesta en BD
  8. Enviar respuesta por WhatsApp
  9. Actualizar analytics

- [ ] Crear Job para envío
  ```bash
  php artisan make:job SendWhatsAppMessage
  ```

- [ ] Implementar Job de envío:
  - Obtener mensaje de BD
  - Llamar a WhatsAppService
  - Actualizar estado del mensaje
  - Manejar errores

- [ ] Probar flujo end-to-end:
  1. Enviar mensaje desde WhatsApp
  2. Verificar que llega al webhook
  3. Verificar que se procesa en queue
  4. Verificar que OpenAI responde
  5. Verificar que la respuesta llega a WhatsApp
  6. Verificar que todo está en BD

### Entregables Sprint 2
- ✅ WhatsApp Business API integrada
- ✅ Webhooks funcionando correctamente
- ✅ OpenAI GPT-4 integrado
- ✅ Queue system operativo
- ✅ Flujo completo funcional: mensaje → IA → respuesta
- ✅ Mensajes guardados en BD
- ✅ Logs de todas las operaciones

### Criterios de Aceptación
- [ ] Bot recibe mensajes de WhatsApp correctamente
- [ ] OpenAI genera respuestas coherentes
- [ ] Respuestas llegan a WhatsApp en <3 segundos
- [ ] Queue procesa sin errores
- [ ] Todas las conversaciones se guardan en BD
- [ ] Sistema maneja 10+ mensajes simultáneos

---

## 📚 SPRINT 3: KNOWLEDGE BASE + RAG

**Duración:** 2 semanas (Semana 5-6)  
**Estado:** ⏳ Pendiente  
**Fecha estimada:** 14-27 Dic 2025

### Objetivos Principales
1. Sistema de knowledge base
2. Upload y procesamiento de documentos
3. Sistema de embeddings
4. RAG (Retrieval-Augmented Generation)
5. Panel de gestión con Livewire

### Tareas Detalladas

#### Día 1-3: Upload de Documentos

**Checklist:**
- [ ] Crear componente Livewire
  ```bash
  php artisan make:livewire KnowledgeBase/DocumentUploader
  ```

- [ ] Implementar upload de archivos
  - Soporte para: PDF, TXT, DOCX
  - Validación de tamaño (max 10MB)
  - Validación de tipo MIME

- [ ] Configurar storage
  ```bash
  php artisan storage:link
  ```

- [ ] Crear Job de procesamiento
  ```bash
  php artisan make:job ProcessDocument
  ```

- [ ] Instalar librerías de procesamiento
  ```bash
  composer require smalot/pdfparser
  composer require phpoffice/phpword
  ```

- [ ] Implementar extracción de texto:
  - [ ] Para PDF: usar pdfparser
  - [ ] Para DOCX: usar phpword
  - [ ] Para TXT: file_get_contents

- [ ] Guardar documento en BD
  - knowledge_documents table
  - Guardar texto extraído

- [ ] Probar upload y extracción

#### Día 4-5: Sistema de Chunking

**Checklist:**
- [ ] Crear service
  ```bash
  php artisan make:class Services/AI/ChunkingService
  ```

- [ ] Implementar métodos:
  - [ ] chunkText(string $text, int $chunkSize, int $overlap): array
  - [ ] intelligentChunk(string $text): array (basado en párrafos)

- [ ] Configurar parámetros
  ```php
  // config/bothub.php
  'knowledge_base' => [
      'chunk_size' => 500,
      'chunk_overlap' => 50,
      'max_chunks_per_document' => 1000,
  ],
  ```

- [ ] Job para crear chunks
  ```bash
  php artisan make:job CreateDocumentChunks
  ```

- [ ] Implementar Job:
  - Obtener documento
  - Dividir en chunks
  - Guardar en document_chunks

- [ ] Probar chunking con documento real

#### Día 6-8: Sistema de Embeddings

**Checklist:**
- [ ] Crear Job
  ```bash
  php artisan make:job GenerateEmbeddings
  ```

- [ ] Implementar Job:
  - Obtener chunks sin embedding
  - Llamar a OpenAI embeddings API
  - Guardar embeddings en BD (JSON)
  - Batch processing (100 chunks a la vez)

- [ ] Optimizar storage de embeddings
  - Considerar columna dedicada
  - O usar servicio externo (Pinecone, Weaviate)

- [ ] Crear comando Artisan
  ```bash
  php artisan make:command GenerateAllEmbeddings
  ```

- [ ] Implementar comando:
  ```bash
  php artisan embeddings:generate {knowledge_base_id?}
  ```

- [ ] Probar generación de embeddings

#### Día 9-11: RAG Service

**Checklist:**
- [ ] Crear service
  ```bash
  php artisan make:class Services/AI/RAGService
  ```

- [ ] Implementar métodos:
  - [ ] search(string $query, int $limit): array
  - [ ] cosineSimilarity(array $vec1, array $vec2): float
  - [ ] findSimilarChunks(array $embedding, int $limit): array

- [ ] Algoritmo de búsqueda:
  1. Crear embedding del query
  2. Calcular similitud coseno con todos los chunks
  3. Retornar top N más similares
  4. Agregar contexto al prompt

- [ ] Optimización:
  - Cache de embeddings frecuentes
  - Índice en BD para búsquedas

- [ ] Integrar RAG en MessageProcessor
  - Antes de llamar a OpenAI
  - Agregar chunks relevantes al prompt

- [ ] Probar RAG:
  - Hacer pregunta específica
  - Verificar que usa info de KB
  - Comparar respuesta con/sin RAG

#### Día 12-14: Panel de Gestión

**Checklist:**
- [ ] Crear componente Livewire
  ```bash
  php artisan make:livewire KnowledgeBase/KnowledgeBaseManager
  ```

- [ ] Implementar funcionalidades:
  - [ ] Listar documentos de la KB
  - [ ] Upload nuevo documento
  - [ ] Ver contenido de documento
  - [ ] Eliminar documento
  - [ ] Ver chunks de un documento
  - [ ] Estado de procesamiento (pending/processing/completed)
  - [ ] Botón "Re-entrenar" (regenerar embeddings)

- [ ] Crear componente para FAQs manuales
  ```bash
  php artisan make:livewire KnowledgeBase/FAQManager
  ```

- [ ] Implementar CRUD de FAQs:
  - Agregar pregunta-respuesta manual
  - Editar FAQ
  - Eliminar FAQ
  - Auto-convertir FAQ a chunk con embedding

- [ ] Crear vista principal
  - [ ] knowledge-bases/show.blade.php
  - Tabs: Documentos | FAQs | Configuración
  - Estadísticas: total docs, chunks, tokens

- [ ] Web scraping (opcional)
  - [ ] Agregar opción para scraping de URL
  - [ ] Extraer contenido de sitio web del cliente
  - [ ] Procesar igual que documentos

### Entregables Sprint 3
- ✅ Upload de documentos funcional (PDF, TXT, DOCX)
- ✅ Sistema de chunking implementado
- ✅ Embeddings generándose correctamente
- ✅ RAG funcionando y mejorando respuestas
- ✅ Panel de gestión de KB con Livewire
- ✅ FAQs manuales
- ✅ Bot responde usando información específica

### Criterios de Aceptación
- [ ] Usuario puede subir documentos
- [ ] Documentos se procesan automáticamente
- [ ] Bot usa información de los documentos para responder
- [ ] Respuestas son más precisas con RAG
- [ ] Panel muestra estado de procesamiento
- [ ] Sistema maneja documentos de hasta 10MB

---

## 📊 SPRINT 4: DASHBOARD + HANDOFF

**Duración:** 2 semanas (Semana 7-8)  
**Estado:** ⏳ Pendiente  
**Fecha estimada:** 28 Dic - 10 Ene 2026

### Objetivos Principales
1. Dashboard con analytics
2. Gráficos y métricas
3. Sistema de handoff a humanos
4. Chat en vivo para agentes
5. Notificaciones real-time

### Tareas Detalladas

#### Día 1-3: Analytics Service

**Checklist:**
- [ ] Crear service
  ```bash
  php artisan make:class Services/Analytics/AnalyticsService
  ```

- [ ] Implementar métodos:
  - [ ] trackEvent(string $type, array $data): void
  - [ ] getConversationsCount(Carbon $start, Carbon $end): int
  - [ ] getMessagesCount(Carbon $start, Carbon $end): int
  - [ ] getAverageResponseTime(Carbon $start, Carbon $end): float
  - [ ] getResolutionRate(Carbon $start, Carbon $end): float
  - [ ] getTopTopics(int $limit): array
  - [ ] getSentimentDistribution(): array

- [ ] Crear Observer para eventos
  ```bash
  php artisan make:observer MessageObserver
  ```

- [ ] Registrar eventos automáticos:
  - message.received
  - message.sent
  - conversation.started
  - conversation.closed
  - handoff.requested

- [ ] Crear Job para cálculos diarios
  ```bash
  php artisan make:job CalculateDailyAnalytics
  ```

- [ ] Programar Job en schedule
  ```php
  // app/Console/Kernel.php
  $schedule->job(new CalculateDailyAnalytics)->daily();
  ```

#### Día 4-6: Dashboard con Gráficos

**Checklist:**
- [ ] Instalar Chart.js
  ```bash
  npm install chart.js
  ```

- [ ] Crear componente Livewire
  ```bash
  php artisan make:livewire Dashboard/AnalyticsDashboard
  ```

- [ ] Implementar métricas:
  - [ ] Cards con números grandes:
    - Total conversaciones (hoy, semana, mes)
    - Total mensajes
    - Tasa de resolución
    - Tiempo promedio respuesta
    - Conversaciones activas ahora

  - [ ] Gráfico: Mensajes por día (últimos 30 días)
  - [ ] Gráfico: Distribución por hora del día
  - [ ] Gráfico: Temas más consultados (top 10)
  - [ ] Gráfico: Sentimiento (positivo/neutral/negativo)

- [ ] Filtros:
  - Por bot
  - Por rango de fechas
  - Por estado de conversación

- [ ] Actualización en tiempo real
  - Polling cada 30 segundos con Livewire
  - O usar WebSockets

- [ ] Exportar reportes
  - Botón "Exportar CSV"
  - Botón "Exportar PDF" (opcional)

#### Día 7-9: Sistema de Handoff

**Checklist:**
- [ ] Crear lógica de detección
  ```bash
  php artisan make:class Services/AI/HandoffDetector
  ```

- [ ] Implementar reglas:
  - [ ] Usuario pide explícitamente hablar con humano
  - [ ] Bot no sabe responder (baja confianza)
  - [ ] Conversación excede N mensajes sin resolver
  - [ ] Keywords específicos (ej: "queja", "reclamar")
  - [ ] Sentimiento muy negativo

- [ ] Integrar en MessageProcessor
  - Después de cada respuesta del bot
  - Evaluar si requiere handoff
  - Cambiar estado de conversación

- [ ] Crear sistema de asignación
  - [ ] Automático: al agente con menos conversaciones
  - [ ] Round-robin
  - [ ] Por especialidad (tags)

- [ ] Notificaciones
  - [ ] Email al agente asignado
  - [ ] Notificación en panel
  - [ ] Push notification (opcional)

- [ ] Actualizar modelo Conversation
  - Campo: assigned_user_id
  - Campo: handoff_at
  - Campo: handoff_reason
  - Estado: waiting_human, with_human

#### Día 10-12: Chat en Vivo

**Checklist:**
- [ ] Crear componente Livewire
  ```bash
  php artisan make:livewire Chat/ChatInterface
  ```

- [ ] Implementar interfaz:
  - [ ] Lista de conversaciones asignadas
  - [ ] Chat window con historial
  - [ ] Input para escribir mensaje
  - [ ] Botón enviar
  - [ ] Indicador "escribiendo..."
  - [ ] Estados de mensaje (sent/delivered/read)

- [ ] Funcionalidades:
  - [ ] Ver historial completo
  - [ ] Enviar mensaje como humano
  - [ ] Transferir a otro agente
  - [ ] Marcar como resuelto
  - [ ] Cerrar conversación
  - [ ] Agregar notas internas (no visible para usuario)
  - [ ] Ver info del usuario (nombre, historial)

- [ ] Crear ruta
  ```php
  Route::get('/chat/{conversation}', ChatInterface::class)
      ->middleware(['auth', 'can:chat,conversation']);
  ```

#### Día 13-14: Real-time con Pusher/Reverb

**Checklist:**
- [ ] Configurar Pusher
  ```bash
  composer require pusher/pusher-php-server
  npm install --save-dev laravel-echo pusher-js
  ```

- [ ] Configurar .env
  ```
  BROADCAST_DRIVER=pusher
  PUSHER_APP_ID=xxxxx
  PUSHER_APP_KEY=xxxxx
  PUSHER_APP_SECRET=xxxxx
  PUSHER_APP_CLUSTER=us2
  ```

- [ ] Crear eventos de broadcast
  ```bash
  php artisan make:event MessageReceived
  php artisan make:event MessageSent
  php artisan make:event AgentTyping
  ```

- [ ] Implementar broadcasts:
  - Cuando llega mensaje nuevo
  - Cuando agente envía mensaje
  - Cuando alguien está escribiendo

- [ ] Frontend con Echo
  ```javascript
  Echo.private('conversations.' + conversationId)
      .listen('MessageReceived', (e) => {
          // Agregar mensaje al chat
      })
      .listen('AgentTyping', (e) => {
          // Mostrar "escribiendo..."
      });
  ```

- [ ] Probar real-time:
  - Abrir chat en dos navegadores
  - Enviar mensaje desde uno
  - Verificar que aparece en el otro

### Entregables Sprint 4
- ✅ Dashboard funcional con métricas
- ✅ Gráficos visuales con Chart.js
- ✅ Sistema de handoff implementado
- ✅ Chat en vivo para agentes
- ✅ Notificaciones real-time
- ✅ Asignación automática de conversaciones

### Criterios de Aceptación
- [ ] Dashboard muestra métricas en tiempo real
- [ ] Gráficos son legibles y útiles
- [ ] Handoff se activa correctamente
- [ ] Agentes reciben notificaciones de nuevas conversaciones
- [ ] Chat en vivo funciona sin lag
- [ ] Mensajes se actualizan en tiempo real

---

## 🧪 SPRINT 5: TESTING + DEPLOY

**Duración:** 2 semanas (Semana 9-10)  
**Estado:** ⏳ Pendiente  
**Fecha estimada:** 11-24 Ene 2026

### Objetivos Principales
1. Testing completo del sistema
2. Optimización de performance
3. Security audit
4. Setup CI/CD
5. Deploy a producción
6. Documentación de usuario

### Tareas Detalladas

#### Día 1-3: Testing Funcional

**Checklist:**
- [ ] Tests de modelos
  ```bash
  php artisan make:test Models/TenantTest
  php artisan make:test Models/BotTest
  php artisan make:test Models/ConversationTest
  ```

- [ ] Tests de services
  ```bash
  php artisan make:test Services/WhatsAppServiceTest
  php artisan make:test Services/OpenAIServiceTest
  php artisan make:test Services/RAGServiceTest
  ```

- [ ] Tests de flujo completo
  ```bash
  php artisan make:test Features/CompleteConversationFlowTest
  ```

- [ ] Tests de API
  ```bash
  php artisan make:test Api/WebhookTest
  ```

- [ ] Ejecutar suite completa
  ```bash
  php artisan test
  ```

- [ ] Cobertura de código
  ```bash
  php artisan test --coverage
  # Objetivo: >70%
  ```

#### Día 4-5: Optimización de Performance

**Checklist:**
- [ ] Análisis de queries lentas
  ```bash
  php artisan telescope:install
  ```

- [ ] Agregar índices faltantes
  - Revisar queries más frecuentes
  - Agregar índices compuestos

- [ ] Eager loading
  - Revisar N+1 queries
  - Agregar with() donde corresponda

- [ ] Cache de queries frecuentes
  ```php
  Cache::remember('bot_config_' . $botId, 3600, function() {
      return Bot::find($botId);
  });
  ```

- [ ] Optimizar storage de embeddings
  - Considerar mover a servicio externo
  - O usar columna binaria

- [ ] Queue optimization
  - Múltiples workers
  - Priorización de jobs

- [ ] Probar con carga
  ```bash
  # Simular 100 mensajes simultáneos
  ```

#### Día 6-7: Security Audit

**Checklist:**
- [ ] Instalar Sentry para error monitoring
  ```bash
  composer require sentry/sentry-laravel
  php artisan sentry:publish --dsn=https://xxxxx@sentry.io/xxxxx
  ```

- [ ] Configurar Sentry
  ```php
  // config/sentry.php
  'dsn' => env('SENTRY_LARAVEL_DSN'),
  'environment' => env('APP_ENV'),
  'release' => env('APP_VERSION'),
  'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.2),
  ```

- [ ] Agregar a .env
  ```
  SENTRY_LARAVEL_DSN=https://xxxxx@sentry.io/xxxxx
  SENTRY_TRACES_SAMPLE_RATE=0.2
  ```

- [ ] Configurar alertas en Sentry
  - Errores críticos → Email inmediato
  - Errores frecuentes → Slack notification
  - Performance issues → Weekly digest

- [ ] Validación de inputs
  - Todos los formularios
  - Todos los endpoints de API

- [ ] Protección CSRF
  - Verificar que está activo
  - Excepciones solo en webhooks

- [ ] Rate limiting
  ```php
  Route::middleware('throttle:60,1')->group(function () {
      // Rutas de API
  });
  ```

- [ ] Encriptación de datos sensibles
  - API keys en BD
  - Tokens de acceso

- [ ] Verificación de firma de webhooks
  - WhatsApp webhook signature

- [ ] SQL Injection prevention
  - Usar query builder o Eloquent
  - Nunca concatenar SQL

- [ ] XSS prevention
  - Escapar outputs con {{ }}
  - Validar HTML inputs

- [ ] Autenticación de 2FA (opcional)
  ```bash
  composer require pragmarx/google2fa-laravel
  ```

- [ ] Logs de auditoría
  - Todas las acciones críticas
  - Login attempts
  - Cambios en configuración

#### Día 8-9: CI/CD con GitHub Actions

**Checklist:**
- [ ] Crear workflow
  ```yaml
  # .github/workflows/tests.yml
  name: Tests
  on: [push, pull_request]
  jobs:
    tests:
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v2
        - name: Setup PHP
          uses: shivammathur/setup-php@v2
          with:
            php-version: 8.3
        - name: Install Dependencies
          run: composer install
        - name: Run Tests
          run: php artisan test
  ```

- [ ] Crear workflow de deploy
  ```yaml
  # .github/workflows/deploy.yml
  name: Deploy
  on:
    push:
      branches: [main]
  jobs:
    deploy:
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v2
        - name: Deploy to Production
          uses: appleboy/ssh-action@master
          with:
            host: ${{ secrets.HOST }}
            username: ${{ secrets.USERNAME }}
            key: ${{ secrets.SSH_KEY }}
            script: |
              cd /path/to/bothub
              git pull origin main
              composer install --no-dev
              php artisan migrate --force
              php artisan config:cache
              php artisan route:cache
              php artisan queue:restart
  ```

- [ ] Configurar secrets en GitHub
  - HOST
  - USERNAME
  - SSH_KEY

- [ ] Probar workflow

#### Día 10-12: Deploy a Producción

**Checklist:**
- [ ] Preparar servidor (HostGator o VPS)
  - Instalar PHP 8.3
  - Instalar Composer
  - Instalar Node.js
  - Instalar MySQL 8.0
  - Instalar Redis
  - Configurar SSL (Let's Encrypt)

- [ ] Configurar dominio
  - DNS apuntando al servidor
  - Certificado SSL activo

- [ ] Crear base de datos de producción
  ```sql
  CREATE DATABASE bothub_prod;
  ```

- [ ] Configurar .env de producción
  ```
  APP_ENV=production
  APP_DEBUG=false
  ```

- [ ] Subir código
  ```bash
  git clone git@github.com:usuario/bothub.git
  cd bothub
  composer install --optimize-autoloader --no-dev
  php artisan key:generate
  php artisan migrate --force
  php artisan db:seed --class=RoleSeeder
  npm install && npm run build
  ```

- [ ] Configurar queue worker
  ```bash
  # supervisor config
  sudo nano /etc/supervisor/conf.d/bothub-worker.conf
  
  [program:bothub-worker]
  process_name=%(program_name)s_%(process_num)02d
  command=php /path/to/bothub/artisan queue:work --sleep=3 --tries=3
  autostart=true
  autorestart=true
  user=www-data
  numprocs=4
  redirect_stderr=true
  stdout_logfile=/path/to/bothub/storage/logs/worker.log
  ```

- [ ] Configurar cron para schedule
  ```bash
  crontab -e
  * * * * * cd /path/to/bothub && php artisan schedule:run >> /dev/null 2>&1
  ```

- [ ] Configurar backups automáticos
  ```bash
  composer require spatie/laravel-backup
  php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
  ```

- [ ] Probar en producción
  - Crear tenant de prueba
  - Crear bot de prueba
  - Enviar mensaje real
  - Verificar respuesta

#### Día 13-14: Documentación

**Checklist:**
- [ ] README.md actualizado
  - Descripción del proyecto
  - Requisitos
  - Instalación
  - Configuración
  - Deploy

- [ ] Documentación de API
  ```bash
  composer require darkaonline/l5-swagger
  php artisan l5-swagger:generate
  ```

- [ ] Guía de usuario
  - Cómo crear un bot
  - Cómo configurar WhatsApp
  - Cómo subir documentos
  - Cómo usar el chat en vivo
  - FAQ

- [ ] Videos tutoriales
  - Screencast de configuración inicial
  - Screencast de uso del dashboard
  - Subir a YouTube o Loom

- [ ] Onboarding para nuevos usuarios
  - Tour guiado en la interfaz
  - Tooltips
  - Link a documentación

### Entregables Sprint 5
- ✅ Suite de tests completa
- ✅ Performance optimizado
- ✅ Security audit realizado
- ✅ CI/CD configurado
- ✅ Aplicación en producción
- ✅ Documentación completa
- ✅ Backups automáticos configurados

### Criterios de Aceptación
- [ ] Tests pasan con >70% cobertura
- [ ] Aplicación responde en <2s
- [ ] No hay vulnerabilidades críticas
- [ ] Deploy automático funciona
- [ ] Aplicación está en producción y accesible
- [ ] Documentación está completa y clara

---

## 🚀 POST-MVP: ROADMAP FUTURO

### Fase 2 (Meses 3-6)

#### Q1 2026 (Feb-Abr)
- [ ] **Módulo de Billing (CRÍTICO)**
  - Integración con Stripe
  - Integración con MercadoPago (LATAM)
  - Webhooks de pagos
  - Manejo de suscripciones
  - Upgrade/downgrade automático
  - Pruebas gratis (trial)
  - Facturación automática usando tenant_usage_reports
- [ ] Builder visual de flujos (drag & drop)
- [ ] Integración con Telegram
- [ ] Integración con Instagram DM
- [ ] Web widget embebible
- [ ] Plantillas de bots por industria
- [ ] Mejorar onboarding

#### Q2 2026 (May-Jul)
- [ ] Voicebot con Twilio (llamadas)
- [ ] CRM básico integrado
- [ ] Appointment scheduling
- [ ] Integración con Zapier/Make
- [ ] A/B testing de respuestas
- [ ] Multilanguage automático
- [ ] Analytics predictivo

### Fase 3 (Meses 7-12)

#### Q3 2026 (Ago-Oct)
- [ ] Mobile app (React Native)
- [ ] Sentiment analysis avanzado
- [ ] **Event Sourcing para Conversations**
  - Historial completo inmutable
  - Auditoría perfecta de IA
  - Reconstrucción de estados
  - Analytics profundo
- [ ] Auto-training de modelos
- [ ] Integraciones: Shopify, WooCommerce
- [ ] API pública para developers
- [ ] Marketplace de integraciones

#### Q4 2026 (Nov-Dic)
- [ ] Expansión a más idiomas
- [ ] White-label completo
- [ ] Sistema de afiliados
- [ ] Advanced reporting
- [ ] **Enterprise features:**
  - Multi-DB por tenant (opcional)
  - Aislamiento físico de datos
  - Custom SLA
  - Dedicated support

---

## 📈 TRACKING DE PROGRESO

### Dashboard de Estado

**Sprint Actual:** Sprint 0 - Setup y Planificación  
**Progreso General MVP:** 5% (Documentación completada)

### Métricas de Progreso

| Sprint | Estado | Progreso | Inicio | Fin |
|--------|--------|----------|--------|-----|
| Sprint 0 | ✅ Completado | 100% | 13 Nov | 15 Nov |
| Sprint 0.5 | ⏳ Pendiente | 0% | 16 Nov | 19 Nov |
| Sprint 0.9 | ⏳ Pendiente | 0% | 20 Nov | 23 Nov |
| Sprint 1 | ⏳ Pendiente | 0% | 24 Nov | 7 Dic |
| Sprint 2 | ⏳ Pendiente | 0% | 8 Dic | 21 Dic |
| Sprint 3 | ⏳ Pendiente | 0% | 22 Dic | 4 Ene |
| Sprint 4 | ⏳ Pendiente | 0% | 5 Ene | 18 Ene |
| Sprint 5 | ⏳ Pendiente | 0% | 19 Ene | 1 Feb |

### Próximos Hitos

- **16 Nov 2025:** Inicio Sprint 0.5 (Multi-tenant Enforcement)
- **20 Nov 2025:** Inicio Sprint 0.9 (Permisos Granulares)
- **24 Nov 2025:** Inicio Sprint 1 (Fundación)
- **25 Dic 2025:** MVP funcionando (80% completo)
- **1 Feb 2026:** Lanzamiento MVP a producción

### Actualización Semanal

**Última actualización:** 13 Nov 2025  
**Completado esta semana:**
- Documentación completa del proyecto
- Lineamientos técnicos definidos
- Schema de base de datos diseñado
- Roadmap detallado creado

**Próxima semana:**
- Crear repositorio GitHub
- Setup inicial Laravel 12
- Primeras migrations
- CRUD de Tenants

---

## 📝 NOTAS FINALES

### Principios de Desarrollo
1. **Commits frecuentes:** Mínimo 2-3 commits por día
2. **Tests desde el inicio:** Escribir tests para funcionalidad crítica
3. **Documentación continua:** Actualizar docs con cada cambio significativo
4. **Code review:** Todo código debe ser revisado antes de merge
5. **Performance first:** Optimizar desde el principio

### Herramientas de Tracking
- **GitHub Projects:** Para tracking de issues y PRs
- **Linear/Trello:** Para sprint planning (opcional)
- **Slack/Discord:** Para comunicación del equipo
- **Daily standups:** 15 min diarios (si hay equipo)

### Contacto y Soporte
- **Lead Developer:** Víctor
- **Repository:** github.com/usuario/bothub (a crear)
- **Documentación:** Ver BOTHUB_MASTER_DOC.md

---

**Fin del roadmap v1.0.0**

*Este roadmap es un documento vivo y debe actualizarse conforme avanza el proyecto.*
