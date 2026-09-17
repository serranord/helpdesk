<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMcp
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        if ($origin !== null && ! in_array($origin, config('helpdesk_mcp.allowed_origins', []), true)) {
            return response()->json(['error' => 'Origen no permitido.'], 403);
        }

        $expected = config('helpdesk_mcp.token');
        $token = $request->bearerToken();
        if (is_string($expected) && $expected !== '' && is_string($token) && hash_equals($expected, $token)) {
            return $next($request);
        }

        if (! $token) {
            return $this->unauthorized();
        }

        $user = Auth::guard('api')->user();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user->estado !== 'activo' || ! $user->puedeGestionar() || ! $user->tokenCan('mcp:use')) {
            return response()->json(['error' => 'Esta cuenta no tiene permiso para usar MCP.'], 403);
        }
        Auth::shouldUse('api');
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    private function unauthorized(): Response
    {
        return response()->json(['error' => 'Autenticación MCP requerida.'], 401)
            ->header('WWW-Authenticate', 'Bearer resource_metadata="'.rtrim(config('app.url'), '/').'/.well-known/oauth-protected-resource/mcp/helpdesk"');
    }
}
