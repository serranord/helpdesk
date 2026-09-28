<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Ticket;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TicketDiferidoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'services.teams.webhook_url' => '']);
        DB::purge('sqlite');
        foreach (glob(database_path('migrations/2024_*.php')) as $migration) {
            (require $migration)->up();
        }
        foreach (['2026_07_11_000000_add_trabajando_hoy_to_tickets', '2026_09_16_000000_add_orden_trabajando_hoy_to_tickets', '2026_09_28_000000_add_diferido_to_tickets_estado'] as $migration) {
            (require database_path('migrations/'.$migration.'.php'))->up();
        }
        Mail::fake();
        Http::preventStrayRequests();
    }

    private function usuario(string $rol = 'solicitante'): Usuario
    {
        return Usuario::create(['nombre' => 'Prueba', 'correo' => uniqid().'@example.com', 'password' => 'test', 'rol' => $rol, 'estado' => 'activo']);
    }

    private function ticket(Usuario $solicitante, string $estado = 'diferido'): Ticket
    {
        $categoria = Categoria::create(['nombre' => 'Soporte', 'activa' => true, 'sla_horas' => 24]);
        return Ticket::create(['numero' => Ticket::generarNumero(), 'titulo' => 'Sin solución disponible', 'descripcion' => 'Caso de prueba', 'categoria_id' => $categoria->id, 'solicitante_id' => $solicitante->id, 'creado_por' => $solicitante->id, 'estado' => $estado]);
    }

    public function test_both_management_actions_accept_deferred_and_render_it(): void
    {
        $owner = $this->usuario();
        $ticket = $this->ticket($owner, 'nuevo');
        $this->actingAs($this->usuario('administrador'));
        $this->post(route('tickets.estado', $ticket), ['estado' => 'diferido'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('diferido', $ticket->fresh()->estado);
        $this->assertNull($ticket->fresh()->fecha_resolucion);
        $ticket->update(['estado' => 'pendiente']);
        $this->post(route('tickets.gestionar', $ticket), ['estado' => 'diferido', 'prioridad' => 'media', 'solicitante_id' => $owner->id])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('diferido', $ticket->fresh()->estado);
        $this->get(route('tickets.index', ['estado' => 'diferido']))->assertOk()->assertSee('ticket-diferido')->assertSee($ticket->titulo);
        $this->get(route('tickets.show', $ticket))->assertOk()->assertSee('Reabrir ticket')->assertSee('Caso diferido');
    }

    public function test_owner_and_managers_can_reopen_with_history_and_normal_style(): void
    {
        $owner = $this->usuario();
        foreach ([$owner, $this->usuario('tecnico'), $this->usuario('administrador')] as $actor) {
            $ticket = $this->ticket($owner);
            $this->actingAs($actor)->post(route('tickets.reabrir', $ticket), ['motivo' => 'Ya existe una alternativa'])->assertRedirect()->assertSessionHasNoErrors();
            $this->assertSame('abierto', $ticket->fresh()->estado);
            $this->assertTrue($ticket->fresh()->reabierto);
            $this->assertDatabaseHas('historial_ticket', ['ticket_id' => $ticket->id, 'accion' => 'reapertura']);
            $this->assertDatabaseHas('comentarios', ['ticket_id' => $ticket->id, 'usuario_id' => $actor->id, 'contenido' => 'Ticket reabierto. Motivo: Ya existe una alternativa']);
        }
        $this->get(route('tickets.index'))->assertOk()->assertDontSee('class="ticket-diferido"', false);
    }

    public function test_another_requester_cannot_reopen_and_active_tickets_are_rejected(): void
    {
        $owner = $this->usuario();
        $ticket = $this->ticket($owner);
        $this->actingAs($this->usuario())->post(route('tickets.reabrir', $ticket))->assertForbidden();
        $this->assertSame('diferido', $ticket->fresh()->estado);
        $ticket->update(['estado' => 'en_proceso']);
        $this->actingAs($owner)->post(route('tickets.reabrir', $ticket))->assertSessionHas('error');
        $this->assertSame('en_proceso', $ticket->fresh()->estado);
    }
}
