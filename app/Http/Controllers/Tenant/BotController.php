<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBotRequest;
use App\Http\Requests\UpdateBotRequest;
use App\Models\Bot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * BotController
 * 
 * Gestiona el CRUD de bots para el tenant.
 * Solo accesible para admin/supervisor del tenant.
 * 
 * CARACTERÍSTICAS:
 * - Todos los bots están automáticamente filtrados por tenant (TenantScope)
 * - El tenant_id se asigna automáticamente al crear (BelongsToTenant trait)
 * - Validación de límites del plan del tenant
 * 
 * @package App\Http\Controllers\Tenant
 */
class BotController extends Controller
{
    /**
     * Muestra listado de bots del tenant
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $tenant = app('tenant');
        
        // Query base (ya filtrado por tenant gracias a TenantScope)
        $query = Bot::query()->with(['users']);
        
        // Filtro por búsqueda (opcional)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filtro por estado (opcional)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        // Ordenamiento
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);
        
        // Paginación
        $bots = $query->paginate(12)->withQueryString();
        
        return view('tenant.bots.index', compact('bots', 'tenant'));
    }

    /**
     * Muestra formulario para crear nuevo bot
     * 
     * @return View|RedirectResponse
     */
    public function create(): View|RedirectResponse
    {
        $tenant = app('tenant');
        
        // Verificar límite de bots del plan
        $currentBotCount = Bot::count();
        if ($currentBotCount >= $tenant->monthly_bot_limit) {
            return redirect()
                ->route('tenant.bots.index')
                ->with('error', "Has alcanzado el límite de {$tenant->monthly_bot_limit} bots de tu plan. Actualiza tu plan para crear más bots.");
        }
        
        return view('tenant.bots.create', compact('tenant'));
    }

    /**
     * Almacena un nuevo bot
     * 
     * @param StoreBotRequest $request
     * @return RedirectResponse
     */
    public function store(StoreBotRequest $request): RedirectResponse
    {
        $tenant = app('tenant');
        
        // Verificar límite de bots del plan
        $currentBotCount = Bot::count();
        if ($currentBotCount >= $tenant->monthly_bot_limit) {
            return redirect()
                ->route('tenant.bots.index')
                ->with('error', "Has alcanzado el límite de {$tenant->monthly_bot_limit} bots de tu plan.");
        }
        
        // Obtener datos validados
        $validated = $request->validated();
        
        // El tenant_id se asigna automáticamente gracias al trait BelongsToTenant
        $bot = Bot::create($validated);
        
        // Asignar el usuario actual al bot con permisos completos
        $bot->users()->attach(auth()->id(), [
            'can_manage' => true,
            'can_view_analytics' => true,
            'can_chat' => true,
            'can_train_kb' => true,
            'can_delete_data' => true,
        ]);
        
        return redirect()
            ->route('tenant.bots.show', $bot)
            ->with('success', "Bot '{$bot->name}' creado exitosamente.");
    }

    /**
     * Muestra detalles de un bot específico
     * 
     * @param Bot $bot
     * @return View
     */
    public function show(Bot $bot): View
    {
        // Cargar relaciones necesarias
        $bot->load(['users', 'knowledgeBase']);
        $bot->loadCount(['conversations', 'users']);
        
        $tenant = app('tenant');
        
        return view('tenant.bots.show', compact('bot', 'tenant'));
    }

    /**
     * Muestra formulario para editar un bot
     * 
     * @param Bot $bot
     * @return View
     */
    public function edit(Bot $bot): View
    {
        $tenant = app('tenant');
        
        return view('tenant.bots.edit', compact('bot', 'tenant'));
    }

    /**
     * Actualiza un bot
     * 
     * @param UpdateBotRequest $request
     * @param Bot $bot
     * @return RedirectResponse
     */
    public function update(UpdateBotRequest $request, Bot $bot): RedirectResponse
    {
        // Obtener datos validados
        $validated = $request->validated();
        
        // Actualizar bot
        $bot->update($validated);
        
        return redirect()
            ->route('tenant.bots.show', $bot)
            ->with('success', "Bot '{$bot->name}' actualizado exitosamente.");
    }

    /**
     * Elimina (soft delete) un bot
     * 
     * IMPORTANTE: Esto también eliminará:
     * - Conversaciones asociadas
     * - Knowledge base
     * - Webhooks
     * - Analytics events
     * 
     * @param Bot $bot
     * @return RedirectResponse
     */
    public function destroy(Bot $bot): RedirectResponse
    {
        $botName = $bot->name;
        
        // Soft delete
        $bot->delete();
        
        return redirect()
            ->route('tenant.bots.index')
            ->with('success', "Bot '{$botName}' eliminado exitosamente.");
    }

    /**
     * Activa un bot
     * 
     * @param Bot $bot
     * @return RedirectResponse
     */
    public function activate(Bot $bot): RedirectResponse
    {
        $bot->update(['is_active' => true]);
        
        return redirect()
            ->route('tenant.bots.show', $bot)
            ->with('success', "Bot '{$bot->name}' activado.");
    }

    /**
     * Desactiva un bot
     * 
     * @param Bot $bot
     * @return RedirectResponse
     */
    public function deactivate(Bot $bot): RedirectResponse
    {
        $bot->update(['is_active' => false]);
        
        return redirect()
            ->route('tenant.bots.show', $bot)
            ->with('warning', "Bot '{$bot->name}' desactivado.");
    }
}