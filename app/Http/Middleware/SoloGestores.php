<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;

class SoloGestores {
    public function handle(Request $request, Closure $next) {
        if (!auth()->check() || !auth()->user()->puedeGestionar()) {
            abort(403, 'Acceso restringido. Requiere rol de Técnico o Administrador.');
        }
        return $next($request);
    }
}
