<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-API-Token') ?? $request->query('api_token');
        $validToken = config('services.api.token');

        if (!$token || $token !== $validToken) {
            return response()->json(['error' => 'Token inválido o no proporcionado.'], 401);
        }

        return $next($request);
    }
}
