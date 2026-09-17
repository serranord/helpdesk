<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Tests\Support\ConfiguresPassportKeys;
use Tests\TestCase;

class McpOAuthTest extends TestCase
{
    use ConfiguresPassportKeys;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'app.url' => 'http://localhost', 'helpdesk_mcp.token' => '',
            'helpdesk_mcp.allowed_origins' => [], 'mcp.authorization_server' => 'http://localhost',
            'services.microsoft.tenant_id' => 'tenant', 'services.microsoft.client_id' => 'client',
            'services.microsoft.client_secret' => 'secret', 'services.microsoft.redirect' => '/auth/microsoft/callback',
        ]);
        DB::purge('sqlite');
        $this->configurePassportKeys();
        foreach (['000001_create_usuarios_table', '000005_create_actividad_log_table', '000013_add_microsoft_id_to_usuarios'] as $name) {
            (require database_path('migrations/2024_01_01_'.$name.'.php'))->up();
        }
        foreach (glob(database_path('migrations/*create_oauth*')) as $migration) {
            (require $migration)->up();
        }
        Http::preventStrayRequests();
    }

    private function user(string $role = 'administrador'): Usuario
    {
        return Usuario::create(['nombre' => 'Admin', 'correo' => 'admin@amcham.org.do', 'password' => 'test', 'rol' => $role, 'estado' => 'activo']);
    }

    private function authorization(): array
    {
        $registration = $this->postJson('/oauth/register', [
            'client_name' => 'Cliente de prueba', 'redirect_uris' => ['https://client.example/callback'],
        ])->assertCreated()->assertJsonPath('token_endpoint_auth_method', 'none');
        $verifier = str_repeat('a', 64);
        $params = [
            'response_type' => 'code', 'client_id' => $registration->json('client_id'),
            'redirect_uri' => 'https://client.example/callback', 'scope' => 'mcp:use offline_access',
            'state' => 'client-state', 'code_challenge_method' => 'S256',
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'resource' => 'http://localhost/mcp/helpdesk',
        ];

        return [$params, $verifier];
    }

    private function authorizeCode(Usuario $user, array $params): string
    {
        $this->actingAs($user, 'web')->withSession(['mcp.microsoft_user_id' => $user->id]);
        $page = $this->get('/oauth/authorize?'.http_build_query($params))->assertOk()->assertSee('Autorizar conexión');
        $authToken = $page->viewData('authToken');
        $approved = $this->post('/oauth/authorize', ['auth_token' => $authToken])->assertRedirect();
        parse_str(parse_url($approved->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('client-state', $query['state']);

        return $query['code'];
    }

    public function test_discovery_and_unauthorized_challenge_are_public(): void
    {
        $this->getJson('/.well-known/oauth-protected-resource/mcp/helpdesk')->assertOk()
            ->assertJsonPath('resource', 'http://localhost/mcp/helpdesk');
        $this->getJson('/.well-known/oauth-authorization-server')->assertOk()
            ->assertJsonPath('authorization_endpoint', 'http://localhost/oauth/authorize')
            ->assertJsonPath('code_challenge_methods_supported.0', 'S256');
        $this->getJson('/.well-known/oauth-protected-resource')->assertJsonPath('resource', 'http://localhost/mcp/helpdesk');
        $this->postJson('/mcp/helpdesk', [])->assertUnauthorized()->assertHeader('WWW-Authenticate');
        $this->withToken('invalid')->postJson('/mcp/helpdesk', [])->assertUnauthorized();
    }

    public function test_registration_rejects_unsafe_redirects_and_wrong_resource(): void
    {
        foreach (['http://evil.example/cb', 'https://a.example/cb#fragment', 'https://user:pass@a.example/cb'] as $uri) {
            $this->postJson('/oauth/register', ['redirect_uris' => [$uri]])->assertStatus(400);
        }
        $this->postJson('/oauth/token', ['resource' => 'https://another.example'])->assertStatus(400)->assertJsonPath('error', 'invalid_target');
    }

    public function test_authorization_requires_pkce_microsoft_login_and_manager_role(): void
    {
        [$params] = $this->authorization();
        $this->get('/oauth/authorize?'.http_build_query([...$params, 'code_challenge_method' => 'plain']))->assertStatus(400);
        $this->get('/oauth/authorize?'.http_build_query($params))->assertRedirect(route('microsoft.redirect'));
        $this->assertStringContainsString('/oauth/authorize?', session('url.intended'));
        $user = $this->user('solicitante');
        $this->actingAs($user)->withSession(['mcp.microsoft_user_id' => $user->id]);
        $this->get('/oauth/authorize?'.http_build_query($params))->assertForbidden();
    }

    public function test_full_pkce_flow_refresh_and_revocation(): void
    {
        [$params, $verifier] = $this->authorization();
        $user = $this->user();
        $code = $this->authorizeCode($user, $params);
        $exchange = ['grant_type' => 'authorization_code', 'client_id' => $params['client_id'],
            'redirect_uri' => $params['redirect_uri'], 'code' => $code, 'code_verifier' => $verifier,
            'resource' => $params['resource']];
        $token = $this->postJson('/oauth/token', $exchange)->assertOk()->assertJsonStructure(['access_token', 'refresh_token']);
        Auth::forgetGuards();
        $this->withToken($token->json('access_token'))->postJson('/mcp/helpdesk', [
            'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list',
        ])->assertOk()->assertJsonCount(6, 'result.tools');
        $this->postJson('/oauth/token', $exchange)->assertStatus(400);
        $refreshed = $this->postJson('/oauth/token', [
            'grant_type' => 'refresh_token', 'client_id' => $params['client_id'], 'refresh_token' => $token->json('refresh_token'),
        ])->assertOk();
        Auth::forgetGuards();
        $this->actingAs($user, 'web')->get('/mcp/conexiones')->assertOk()->assertSee('Cliente de prueba');
        $this->delete('/mcp/conexiones/'.$params['client_id'])->assertRedirect(route('mcp.connections'));
        Auth::forgetGuards();
        $this->withToken($refreshed->json('access_token'))->postJson('/mcp/helpdesk', [])->assertUnauthorized();
        $this->postJson('/oauth/token', [
            'grant_type' => 'refresh_token', 'client_id' => $params['client_id'], 'refresh_token' => $refreshed->json('refresh_token'),
        ])->assertStatus(400);
    }

    public function test_wrong_pkce_verifier_cannot_exchange_code(): void
    {
        [$params] = $this->authorization();
        $code = $this->authorizeCode($this->user(), $params);
        $this->postJson('/oauth/token', ['grant_type' => 'authorization_code', 'client_id' => $params['client_id'],
            'redirect_uri' => $params['redirect_uri'], 'code' => $code, 'code_verifier' => str_repeat('b', 64),
        ])->assertStatus(400);
    }

    public function test_oauth_checks_scope_and_current_role_on_each_request(): void
    {
        $user = $this->user();
        Passport::actingAs($user, ['offline_access'], 'api');
        $this->withToken('test')->postJson('/mcp/helpdesk', [])->assertForbidden();
        Passport::actingAs($user, ['mcp:use'], 'api');
        $user->estado = 'inactivo';
        $this->postJson('/mcp/helpdesk', [])->assertForbidden();
        $user->estado = 'activo';
        $user->rol = 'solicitante';
        $this->postJson('/mcp/helpdesk', [])->assertForbidden();
    }

    public function test_microsoft_callback_rejects_invalid_state_before_network(): void
    {
        $this->withSession(['microsoft_oauth_state' => 'expected'])->get('/auth/microsoft/callback?state=wrong&code=test')
            ->assertRedirect(route('login'))->assertSessionHasErrors('correo');
        Http::assertNothingSent();
    }

    public function test_microsoft_login_returns_to_oauth_and_does_not_reactivate_accounts(): void
    {
        $user = $this->user();
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'microsoft-token']),
            'graph.microsoft.com/*' => Http::response(['id' => 'ms-id', 'displayName' => 'Admin Microsoft', 'mail' => $user->correo]),
        ]);
        $this->withSession(['microsoft_oauth_state' => 'valid', 'url.intended' => 'http://localhost/oauth/authorize?client_id=test'])
            ->get('/auth/microsoft/callback?state=valid&code=test')->assertRedirect('http://localhost/oauth/authorize?client_id=test')
            ->assertSessionHas('mcp.microsoft_user_id', $user->id);
        $user->update(['estado' => 'inactivo']);
        Auth::guard('web')->logout();
        $this->withSession(['microsoft_oauth_state' => 'valid2'])->get('/auth/microsoft/callback?state=valid2&code=test')
            ->assertSessionHasErrors('correo');
        $this->assertSame('inactivo', $user->fresh()->estado);
    }
}
