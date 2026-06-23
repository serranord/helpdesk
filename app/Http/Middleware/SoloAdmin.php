<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;

class SoloAdmin {
    public function handle(Request $request, Closure $next) {
        if (!auth()->check() || !auth()->user()->esAdministrador()) {
            abort(403, 'Acceso restringido. Requiere rol de Administrador.');
        }
        return $next($request);
    }
}
