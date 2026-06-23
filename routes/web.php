<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\TecnicoController;
use App\Http\Controllers\KbController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\AdjuntoController;

Route::get ('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',    [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');
Route::get ('/registro', [AuthController::class, 'showRegister'])->name('register');
Route::post('/registro', [AuthController::class, 'register'])->name('register.submit');

Route::middleware(['auth'])->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil
    Route::get('/perfil',          [PerfilController::class, 'show'])->name('perfil.show');
    Route::put('/perfil',          [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/password', [PerfilController::class, 'cambiarPassword'])->name('perfil.password');

    // Tickets
    Route::get ('/tickets',                       [TicketController::class, 'index'])->name('tickets.index');
    Route::get ('/tickets/nuevo',                 [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets',                       [TicketController::class, 'store'])->name('tickets.store');
    Route::get ('/tickets/{ticket}',              [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/comentar',     [TicketController::class, 'comentar'])->name('tickets.comentar');
    Route::post('/tickets/{ticket}/calificar',    [CalificacionController::class, 'store'])->name('tickets.calificar');

    // Adjuntos
    Route::post  ('/tickets/{ticket}/adjuntos',   [AdjuntoController::class, 'store'])->name('adjuntos.store');
    Route::get   ('/adjuntos/{adjunto}/descargar',[AdjuntoController::class, 'download'])->name('adjuntos.download');
    Route::delete('/adjuntos/{adjunto}',          [AdjuntoController::class, 'destroy'])->name('adjuntos.destroy');

    // Base de conocimientos
    Route::get('/kb',                  [KbController::class, 'index'])->name('kb.index');
    Route::get('/kb/{kbArticulo}',     [KbController::class, 'show'])->name('kb.show');

    // Notificaciones
    Route::get ('/notificaciones',              [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::get ('/notificaciones/no-leidas',    [NotificacionController::class, 'noLeidas'])->name('notificaciones.no-leidas');
    Route::post('/notificaciones/leer-todas',   [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.leer-todas');
    Route::post('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leer');

    // Solo técnicos y administradores
    Route::middleware('gestores')->group(function () {
        Route::get ('/mi-panel',                    [TecnicoController::class, 'panel'])->name('tecnico.panel');
        Route::post('/tickets/{ticket}/estado',     [TicketController::class, 'cambiarEstado'])->name('tickets.estado');
        Route::post('/tickets/{ticket}/asignar',    [TicketController::class, 'asignar'])->name('tickets.asignar');
        Route::post('/tickets/{ticket}/prioridad',  [TicketController::class, 'cambiarPrioridad'])->name('tickets.prioridad');
        Route::get ('/plantillas',                  [PlantillaController::class, 'index'])->name('plantillas.index');
        Route::post('/plantillas',                  [PlantillaController::class, 'store'])->name('plantillas.store');
        Route::put ('/plantillas/{plantilla}',      [PlantillaController::class, 'update'])->name('plantillas.update');
        Route::delete('/plantillas/{plantilla}',    [PlantillaController::class, 'destroy'])->name('plantillas.destroy');
        Route::get ('/plantillas/{plantilla}/datos',[PlantillaController::class, 'datos'])->name('plantillas.datos');
        Route::get ('/kb/admin/lista',              [KbController::class, 'admin'])->name('kb.admin');
        Route::post('/kb',                          [KbController::class, 'store'])->name('kb.store');
        Route::get ('/kb/{kbArticulo}/editar',      [KbController::class, 'edit'])->name('kb.edit');
        Route::put ('/kb/{kbArticulo}',             [KbController::class, 'update'])->name('kb.update');
        Route::delete('/kb/{kbArticulo}',           [KbController::class, 'destroy'])->name('kb.destroy');
    });

    // Solo administradores
    Route::middleware('solo.admin')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
        Route::get ('/categorias',             [CategoriaController::class, 'index'])->name('categorias.index');
        Route::post('/categorias',             [CategoriaController::class, 'store'])->name('categorias.store');
        Route::put ('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::get ('/reportes',               [ReporteController::class, 'index'])->name('reportes.index');
        Route::get ('/reportes/exportar',      [ReporteController::class, 'exportarExcel'])->name('reportes.exportar');
    });
});
