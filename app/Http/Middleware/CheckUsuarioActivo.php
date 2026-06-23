<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth;

class CheckUsuarioActivo {
    public function handle(Request $request, Closure $next) {
        if (Auth::check() && Auth::user()->estado !== 'activo') {
            Auth::logout();
            return redirect()->route('login')->withErrors(['correo' => 'Tu cuenta está inactiva. Contacta al administrador.']);
        }
        return $next($request);
    }
}
