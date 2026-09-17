<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeMcpConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            if ($request->input('code_challenge_method') !== 'S256'
                || ! is_string($request->input('code_challenge'))
                || ! preg_match('/^[A-Za-z0-9_-]{43}$/D', $request->input('code_challenge'))) {
                return response()->json(['error' => 'invalid_request', 'error_description' => 'Se requiere PKCE S256.'], 400);
            }
        }

        $user = $request->user('web');
        if (! $user || (string) $request->session()->get('mcp.microsoft_user_id') !== (string) $user->id) {
            if (! $request->isMethod('GET')) {
                abort(403, 'Inicia nuevamente la conexión desde tu aplicación de IA.');
            }

            return redirect()->guest(route('microsoft.redirect'));
        }
        abort_unless($user->estado === 'activo' && $user->puedeGestionar(), 403,
            'Solo administradores y técnicos activos pueden conectar una IA al HelpDesk.');

        if ($request->isMethod('GET')) {
            $request->session()->put('mcp.oauth_user_id', $user->id);
        } else {
            abort_unless((string) $request->session()->get('mcp.oauth_user_id') === (string) $user->id, 403);
        }

        return $next($request);
    }
}
