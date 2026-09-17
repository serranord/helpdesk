<?php

namespace Tests\Feature;

use App\Mail\TicketCreado;
use App\Models\Categoria;
use App\Models\Comentario;
use App\Models\Ticket;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class McpTest extends TestCase
{
    use \Tests\Support\ConfiguresPassportKeys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurePassportKeys();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'helpdesk_mcp.token' => 'test-mcp-token',
            'helpdesk_mcp.allowed_origins' => [],
            'services.teams.webhook_url' => '',
        ]);
        DB::purge('sqlite');
        // Use real base migrations; the repository also contains MySQL-only legacy ALTER statements.
        foreach ([
            '000001_create_usuarios_table', '000002_create_categorias_table',
            '000003_create_tickets_table', '000004_create_comentarios_table',
            '000005_create_actividad_log_table', '000007_create_historial_ticket_table',
            '000015_create_configuracion_table',
        ] as $migration) {
            (require database_path('migrations/2024_01_01_'.$migration.'.php'))->up();
        }
        Mail::fake();
        Http::preventStrayRequests();
    }

    private function rpc(string $method, array $params = []): TestResponse
    {
        return $this->withToken('test-mcp-token')->postJson('/mcp/helpdesk', [
            'jsonrpc' => '2.0', 'id' => 1, 'method' => $method, 'params' => $params ?: (object) [],
        ]);
    }

    private function callTool(string $name, array $arguments = []): TestResponse
    {
        return $this->rpc('tools/call', ['name' => $name, 'arguments' => $arguments ?: (object) []]);
    }

    private function fixture(): array
    {
        $user = Usuario::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.com', 'password' => 'test', 'rol' => 'solicitante', 'estado' => 'activo']);
        $category = Categoria::create(['nombre' => 'Soporte', 'activa' => true, 'sla_horas' => 24]);

        return ['titulo' => 'No puedo imprimir', 'descripcion' => 'Impresora sin conexión', 'solicitante_email' => $user->correo, 'categoria_id' => $category->id];
    }

    public function test_web_requires_a_separate_bearer_token(): void
    {
        $this->postJson('/mcp/helpdesk', [])->assertUnauthorized();
        $this->withToken('incorrecto')->postJson('/mcp/helpdesk', [])->assertUnauthorized();
        $this->withHeaders(['X-API-Token' => 'test-mcp-token'])->postJson('/mcp/helpdesk?api_token=test-mcp-token', [])->assertUnauthorized();
        config(['helpdesk_mcp.token' => '']);
        $this->rpc('tools/list')->assertUnauthorized();
    }

    public function test_origin_is_rejected_unless_explicitly_allowed(): void
    {
        $this->withHeader('Origin', 'https://untrusted.example');
        $this->rpc('tools/list')->assertForbidden();
        config(['helpdesk_mcp.allowed_origins' => ['https://untrusted.example']]);
        $this->rpc('tools/list')->assertOk();
    }

    public function test_initialization_and_tool_discovery(): void
    {
        $this->rpc('initialize', ['protocolVersion' => '2025-03-26', 'capabilities' => (object) [], 'clientInfo' => ['name' => 'test', 'version' => '1.0']])
            ->assertOk()->assertJsonPath('result.serverInfo.name', 'HelpDesk DR');
        $response = $this->rpc('tools/list')->assertOk()->assertJsonCount(6, 'result.tools');
        $this->assertEqualsCanonicalizing(
            ['consultar_ticket', 'buscar_tickets', 'tickets_por_usuario', 'estadisticas', 'listar_categorias', 'crear_ticket'],
            array_column($response->json('result.tools'), 'name'),
        );
    }

    public function test_creates_ticket_with_sla_audit_and_notification(): void
    {
        $response = $this->callTool('crear_ticket', $this->fixture())->assertOk()->assertJsonPath('result.isError', false);
        $data = json_decode($response->json('result.content.0.text'), true);
        $ticket = Ticket::firstOrFail();
        $this->assertSame($ticket->numero, $data['numero']);
        $this->assertSame('nuevo', $ticket->estado);
        $this->assertSame('media', $ticket->prioridad);
        $this->assertNotNull($ticket->fecha_limite);
        $this->assertDatabaseHas('historial_ticket', ['ticket_id' => $ticket->id, 'descripcion' => 'Ticket creado mediante MCP en nombre del solicitante.']);
        $this->assertDatabaseHas('actividad_log', ['referencia' => $ticket->numero, 'descripcion' => "MCP creó ticket {$ticket->numero}"]);
        Mail::assertSent(TicketCreado::class);
    }

    public function test_invalid_creation_does_not_write(): void
    {
        $args = $this->fixture();
        $this->callTool('crear_ticket', [...$args, 'prioridad' => 'urgente'])->assertOk()->assertJsonPath('result.isError', true);
        Categoria::query()->update(['activa' => false]);
        $this->callTool('crear_ticket', $args)->assertOk()->assertJsonPath('result.isError', true);
        $this->assertDatabaseCount('tickets', 0);
        Mail::assertNothingSent();
    }

    public function test_inactive_deleted_or_unknown_requesters_cannot_create_tickets(): void
    {
        $args = $this->fixture();
        $this->callTool('crear_ticket', [...$args, 'solicitante_email' => 'unknown@example.com'])->assertJsonPath('result.isError', true);
        Usuario::query()->update(['estado' => 'inactivo']);
        $this->callTool('crear_ticket', $args)->assertJsonPath('result.isError', true);
        Usuario::query()->update(['estado' => 'activo']);
        Usuario::first()->delete();
        $this->callTool('crear_ticket', $args)->assertJsonPath('result.isError', true);
        $this->assertDatabaseCount('tickets', 0);
        $this->assertDatabaseCount('usuarios', 1);
    }

    public function test_reads_only_latest_public_comment_and_handles_deleted_requester(): void
    {
        $this->callTool('crear_ticket', $this->fixture())->assertJsonPath('result.isError', false);
        $ticket = Ticket::firstOrFail();
        foreach (['Primera', 'Última pública', 'Secreto interno'] as $index => $text) {
            Comentario::create(['ticket_id' => $ticket->id, 'usuario_id' => $ticket->solicitante_id, 'contenido' => $text, 'es_interno' => $index === 2]);
        }
        $response = $this->callTool('consultar_ticket', ['numero' => strtolower($ticket->numero)])->assertJsonPath('result.isError', false);
        $this->assertSame('Última pública', json_decode($response->json('result.content.0.text'), true)['ultima_actualizacion']);
        $this->assertStringNotContainsString('Secreto interno', $response->json('result.content.0.text'));
        Usuario::first()->delete();
        $this->callTool('consultar_ticket', ['numero' => $ticket->numero])->assertJsonPath('result.isError', false);
        $this->callTool('consultar_ticket', ['numero' => 'TKT-99999'])->assertJsonPath('result.isError', true);
    }

    public function test_search_filters_paginates_and_bounds_results(): void
    {
        $args = $this->fixture();
        $this->callTool('crear_ticket', $args);
        $this->callTool('crear_ticket', [...$args, 'titulo' => 'Red caída', 'prioridad' => 'critica']);
        $response = $this->callTool('buscar_tickets', ['prioridad' => 'critica', 'estado' => 'nuevo', 'texto' => 'Red', 'por_pagina' => 1]);
        $data = json_decode($response->json('result.content.0.text'), true);
        $this->assertSame(1, $data['total']);
        $this->assertSame('Red caída', $data['tickets'][0]['titulo']);
        $page = $this->callTool('buscar_tickets', ['por_pagina' => 1, 'pagina' => 2]);
        $this->assertSame(2, json_decode($page->json('result.content.0.text'), true)['pagina']);
        $this->callTool('buscar_tickets', ['por_pagina' => 51])->assertJsonPath('result.isError', true);
    }

    public function test_statistics_categories_and_user_tickets(): void
    {
        $this->callTool('crear_ticket', $this->fixture());
        $stats = $this->callTool('estadisticas')->assertJsonPath('result.isError', false);
        $this->assertSame(1, json_decode($stats->json('result.content.0.text'), true)['nuevos']);
        $categories = $this->callTool('listar_categorias')->assertJsonPath('result.isError', false);
        $this->assertCount(1, json_decode($categories->json('result.content.0.text'), true));
        $tickets = $this->callTool('tickets_por_usuario', ['correo' => 'prueba@example.com'])->assertJsonPath('result.isError', false);
        $this->assertSame(1, json_decode($tickets->json('result.content.0.text'), true)['total']);
        $this->callTool('tickets_por_usuario', ['correo' => 'bad'])->assertJsonPath('result.isError', true);
    }
}
