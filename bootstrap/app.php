<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TenantResolver;

/**
 * Bootstrap de la aplicación BotHub
 * 
 * ESTRUCTURA DE RUTAS:
 * - web.php: Rutas públicas
 * - auth.php: Autenticación (Breeze)
 * - admin.php: Super admin (sin tenant)
 * - tenant.php: Admin de tenant
 * - agent.php: Agentes y usuarios
 * - api.php: Webhooks y API
 * 
 * MIDDLEWARES:
 * - tenant.resolver: Valida tenant y lo setea en contexto
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Cargar archivos de rutas adicionales
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            
            Route::middleware('web')
                ->group(base_path('routes/tenant.php'));
            
            Route::middleware('web')
                ->group(base_path('routes/agent.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar middleware de tenant con alias
        $middleware->alias([
            'tenant.resolver' => TenantResolver::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Aplicar tenant.resolver a todas las rutas web autenticadas
        // Esto asegura que TODAS las rutas protegidas tengan el tenant seteado
        $middleware->web(append: [
            // Aquí puedes agregar middlewares que se apliquen a TODAS las rutas web
        ]);

        // NOTA: No agregamos tenant.resolver aquí globalmente porque:
        // 1. Las rutas públicas (login, register) no necesitan tenant
        // 2. Lo aplicaremos específicamente en las rutas protegidas
        // 3. Damos flexibilidad para rutas sin autenticación
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();