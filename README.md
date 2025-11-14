<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>
# 🤖 BotHub - Plataforma SaaS Multi-tenant para Chatbots Inteligentes

**Estado:** En desarrollo - Sprint 0.5 (Multi-tenant Enforcement) ✅  
**Versión:** 0.1.0-alpha  
**Stack:** Laravel 12 + PHP 8.3 + MySQL 8 + Redis + Livewire 3

---

## 📋 Descripción

BotHub es una plataforma SaaS que permite a agencias y empresas crear, gestionar y desplegar chatbots inteligentes para atención al cliente en múltiples canales, con especial énfasis en WhatsApp.

---

## 🛠️ Stack Tecnológico

**Backend:**
- Laravel 12
- PHP 8.3
- MySQL 8.0
- Redis (Cache + Queues)
- Spatie Laravel Permission

**Frontend:**
- Livewire 3
- Tailwind CSS
- Alpine.js

**APIs Externas:**
- WhatsApp Business API (Meta)
- OpenAI GPT-4
- Twilio (backup)

---

## 🔒 Seguridad Multi-tenant

Sistema de doble capa implementado:

1. **TenantScope (Global Scope)**: Filtra automáticamente todas las queries por `tenant_id`
2. **TenantResolver (Middleware)**: Valida y setea el tenant en cada request

✅ Imposible mezclar datos entre tenants por error de código  
✅ Super admins pueden acceder a todos los tenants cuando necesario  
✅ Logs exhaustivos de todos los accesos

---

## 🚀 Instalación Local
```bash
# Clonar repositorio
git clone https://github.com/tu-usuario/bothub.git
cd bothub

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
DB_DATABASE=bothub
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Ejecutar migraciones
php artisan migrate

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
```

---

## 📊 Estado del Desarrollo

### ✅ Completado:

**Sprint 0: Documentación (100%)**
- [x] BOTHUB_MASTER_DOC.md
- [x] DATABASE_SCHEMA.md
- [x] API_INTEGRATIONS.md
- [x] DEVELOPMENT_ROADMAP.md

**Sprint 0.5: Multi-tenant Enforcement (90%)**
- [x] TenantScope global creado
- [x] BelongsToTenant trait implementado
- [x] Modelo Tenant completo
- [x] Modelo User actualizado
- [x] Migraciones ejecutadas
- [x] Middleware TenantResolver creado
- [ ] Middleware registrado en Kernel
- [ ] Tests de aislamiento

### ⏳ Próximos Sprints:

- Sprint 0.9: Sistema de Permisos Granular (3-4 días)
- Sprint 1: Fundación - CRUD básico (2 semanas)
- Sprint 2: WhatsApp + IA (2 semanas)
- Sprint 3: Knowledge Base + RAG (2 semanas)

---

## 📚 Documentación

Ver carpeta `/docs` para documentación completa:
- Arquitectura del sistema
- Schema de base de datos
- Roadmap de desarrollo
- Integraciones de APIs

---

## 👥 Equipo

- **Lead Developer:** Víctor
- **AI Assistant:** Claude (Anthropic)

---

## 📄 Licencia

Propietario - Todos los derechos reservados

---

**Última actualización:** 14 de Noviembre, 2025
## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
