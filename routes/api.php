<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TicketApiController;

// Rutas protegidas por API token
//Route::middleware('api.token')->group(function () {
Route::middleware(\App\Http\Middleware\ApiTokenMiddleware::class)->group(function () {

    // Consultar ticket por número
    Route::get('/ticket/{numero}', [TicketApiController::class, 'porNumero']);

    // Consultar tickets de un usuario por correo
    Route::get('/tickets/usuario/{correo}', [TicketApiController::class, 'porUsuario']);

    // Estadísticas generales
    Route::get('/estadisticas', [TicketApiController::class, 'estadisticas']);
});
