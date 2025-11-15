<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * TenantController
 * 
 * Gestiona el CRUD de tenants (agencias/empresas).
 * Solo accesible para usuarios con rol 'super_admin'.
 * 
 * @package App\Http\Controllers\Admin
 */
class TenantController extends Controller
{


    /**
     * Muestra listado de todos los tenants
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Query base
        $query = Tenant::query()
            ->withCount(['users', 'bots'])
            ->withoutGlobalScopes(); // Super admin puede ver todos los tenants

        // Filtro por búsqueda (opcional)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filtro por status de suscripción (opcional)
        if ($request->filled('subscription_status')) {
            $query->where('subscription_status', $request->input('subscription_status'));
        }

        // Filtro por plan (opcional)
        if ($request->filled('subscription_plan')) {
            $query->where('subscription_plan', $request->input('subscription_plan'));
        }

        // Ordenamiento (por defecto: más recientes primero)
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginación
        $tenants = $query->paginate(15)->withQueryString();

        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Muestra formulario para crear nuevo tenant
     * 
     * @return View
     */
    public function create(): View
    {
        return view('admin.tenants.create');
    }

    /**
     * Almacena un nuevo tenant en la base de datos
     * 
     * @param StoreTenantRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTenantRequest $request): RedirectResponse
    {
        // Obtener datos validados
        $validated = $request->validated();

        // Generar slug único si no se proporcionó
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Verificar unicidad y agregar sufijo si es necesario
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Tenant::withoutGlobalScopes()->where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Establecer fechas de suscripción si es trial
        if ($validated['subscription_status'] === 'trial') {
            $validated['subscription_started_at'] = now();
            $validated['subscription_ends_at'] = now()->addDays(14); // 14 días de trial
        } elseif ($validated['subscription_status'] === 'active') {
            $validated['subscription_started_at'] = now();
            $validated['subscription_ends_at'] = now()->addMonth(); // 1 mes
        }

        // Crear tenant
        $tenant = Tenant::create($validated);

        // Mensaje de éxito
        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', "Tenant '{$tenant->name}' creado exitosamente.");
    }

    /**
     * Muestra detalles de un tenant específico
     * 
     * @param Tenant $tenant
     * @return View
     */
    public function show(Tenant $tenant): View
    {
        // Cargar relaciones necesarias
        $tenant->loadCount(['users', 'bots', 'apiKeys']);
        
        // Cargar usuarios del tenant (limitado a 10 más recientes)
        $tenant->load(['users' => function ($query) {
            $query->latest()->limit(10);
        }]);

        // Cargar bots del tenant (limitado a 10 más recientes)
        $tenant->load(['bots' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('admin.tenants.show', compact('tenant'));
    }

    /**
     * Muestra formulario para editar un tenant
     * 
     * @param Tenant $tenant
     * @return View
     */
    public function edit(Tenant $tenant): View
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    /**
     * Actualiza un tenant en la base de datos
     * 
     * @param UpdateTenantRequest $request
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        // Obtener datos validados
        $validated = $request->validated();

        // Si el nombre cambió, regenerar slug (opcional)
        if (isset($validated['name']) && $validated['name'] !== $tenant->name) {
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
                
                // Verificar unicidad excluyendo el tenant actual
                $originalSlug = $validated['slug'];
                $counter = 1;
                while (Tenant::withoutGlobalScopes()
                    ->where('slug', $validated['slug'])
                    ->where('id', '!=', $tenant->id)
                    ->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        }

        // Actualizar tenant
        $tenant->update($validated);

        // Mensaje de éxito
        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', "Tenant '{$tenant->name}' actualizado exitosamente.");
    }

    /**
     * Elimina (soft delete) un tenant
     * 
     * IMPORTANTE: Esto eliminará en cascada todos los datos asociados:
     * - Usuarios del tenant
     * - Bots del tenant
     * - Conversaciones
     * - Mensajes
     * - Knowledge bases
     * - etc.
     * 
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        // Verificar que no sea el tenant por defecto o crítico (si aplica)
        // Esta lógica depende de tus reglas de negocio
        
        $tenantName = $tenant->name;

        // Soft delete
        $tenant->delete();

        // Mensaje de éxito
        return redirect()
            ->route('admin.tenants.index')
            ->with('success', "Tenant '{$tenantName}' eliminado exitosamente.");
    }

    /**
     * Reactiva un tenant eliminado (restaura soft delete)
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function restore(int $id): RedirectResponse
    {
        $tenant = Tenant::withoutGlobalScopes()->onlyTrashed()->findOrFail($id);
        $tenant->restore();

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', "Tenant '{$tenant->name}' restaurado exitosamente.");
    }

    /**
     * Suspende la suscripción de un tenant
     * 
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function suspend(Tenant $tenant): RedirectResponse
    {
        $tenant->update([
            'subscription_status' => 'suspended',
        ]);

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('warning', "Suscripción del tenant '{$tenant->name}' suspendida.");
    }

    /**
     * Reactiva la suscripción de un tenant suspendido
     * 
     * @param Tenant $tenant
     * @return RedirectResponse
     */
    public function activate(Tenant $tenant): RedirectResponse
    {
        $tenant->update([
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addMonth(), // Extender 1 mes
        ]);

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', "Tenant '{$tenant->name}' reactivado exitosamente.");
    }
}