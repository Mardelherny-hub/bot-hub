<?php

use Illuminate\Support\Facades\Route;

/**
 * Rutas Públicas de BotHub
 * 
 * Solo landing page y rutas sin autenticación.
 * Las rutas autenticadas están separadas en:
 * - routes/admin.php (super_admin)
 * - routes/tenant.php (admin de tenant)
 * - routes/agent.php (agentes y usuarios del tenant)
 */

// Landing page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rutas de autenticación (Breeze)
require __DIR__.'/auth.php';
