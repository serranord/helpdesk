<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class McpOAuthMetadataController extends Controller
{
    public function authorizationServer(): JsonResponse
    {
        $base = rtrim(config('app.url'), '/');

        return response()->json([
            'issuer' => $base,
            'authorization_endpoint' => $base.'/oauth/authorize',
            'token_endpoint' => $base.'/oauth/token',
            'registration_endpoint' => $base.'/oauth/register',
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'code_challenge_methods_supported' => ['S256'],
            'token_endpoint_auth_methods_supported' => ['none', 'client_secret_basic', 'client_secret_post'],
            'scopes_supported' => ['mcp:use', 'offline_access'],
        ]);
    }

    public function protectedResource(): JsonResponse
    {
        $base = rtrim(config('app.url'), '/');

        return response()->json([
            'resource' => $base.'/mcp/helpdesk',
            'authorization_servers' => [$base],
            'scopes_supported' => ['mcp:use'],
            'bearer_methods_supported' => ['header'],
        ]);
    }
}
