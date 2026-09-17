<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateMcpOAuthRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('resource') && $request->input('resource') !== rtrim(config('app.url'), '/').'/mcp/helpdesk') {
            return response()->json(['error' => 'invalid_target'], 400);
        }

        if ($request->is('oauth/register')) {
            $uris = $request->input('redirect_uris');
            if (! is_array($uris) || count($uris) < 1 || count($uris) > 10) {
                return response()->json(['error' => 'invalid_redirect_uri'], 400);
            }
            foreach ($uris as $uri) {
                $parts = is_string($uri) && strlen($uri) <= 2048 ? parse_url($uri) : false;
                if (! $parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])
                    || isset($parts['fragment']) || isset($parts['user']) || isset($parts['pass'])) {
                    return response()->json(['error' => 'invalid_redirect_uri', 'error_description' => 'Usa una URL HTTPS sin fragmentos ni credenciales.'], 400);
                }
            }
        }

        return $next($request);
    }
}
