<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MicrosoftAuthService
{
    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->tenantId     = config('services.microsoft.tenant_id');
        $this->clientId     = config('services.microsoft.client_id');
        $this->clientSecret = config('services.microsoft.client_secret');
        $this->redirectUri  = url(config('services.microsoft.redirect'));
    }

    // Generar URL de autorización de Microsoft
    public function getAuthUrl(): string
    {
        $params = http_build_query([
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => $this->redirectUri,
            'scope'         => 'openid profile email User.Read',
            'response_mode' => 'query',
            'state'         => csrf_token(),
        ]);

        return "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/authorize?{$params}";
    }

    // Intercambiar código por token de acceso
    public function getAccessToken(string $code): ?string
    {
        try {
            $response = Http::asForm()->post(
                "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token",
                [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code'          => $code,
                    'redirect_uri'  => $this->redirectUri,
                    'grant_type'    => 'authorization_code',
                ]
            );

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Microsoft token error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Microsoft token exception: ' . $e->getMessage());
            return null;
        }
    }

    // Obtener datos del usuario desde Microsoft Graph
    public function getUserData(string $accessToken): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/me', [
                    '$select' => 'id,displayName,mail,userPrincipalName,department,jobTitle',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'microsoft_id' => $data['id'],
                    'nombre'       => $data['displayName'],
                    'correo'       => $data['mail'] ?? $data['userPrincipalName'],
                    'departamento' => $data['department'] ?? null,
                    'cargo'        => $data['jobTitle'] ?? null,
                ];
            }

            Log::error('Microsoft Graph error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Microsoft Graph exception: ' . $e->getMessage());
            return null;
        }
    }
}
