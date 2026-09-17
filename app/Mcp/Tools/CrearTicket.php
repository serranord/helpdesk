<?php

namespace App\Mcp\Tools;

use App\Mail\TicketCreado;
use App\Models\ActividadLog;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\HistorialTicket;
use App\Models\Ticket;
use App\Models\Usuario;
use App\Services\SlaCalculator;
use App\Services\TeamsService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive(false)]
class CrearTicket extends Tool
{
    protected string $name = 'crear_ticket';

    protected string $description = 'Crea un ticket para un solicitante activo ya registrado y envía notificaciones por correo y Teams. Requiere categoría activa. No crea ni reactiva cuentas de usuarios.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'titulo' => $schema->string()->max(255)->required(),
            'descripcion' => $schema->string()->max(20000)->required(),
            'solicitante_email' => $schema->string()->description('Correo de un usuario activo registrado.')->required(),
            'categoria_id' => $schema->integer()->description('Identificador obtenido con listar_categorias.')->required(),
            'prioridad' => $schema->string()->enum(['baja', 'media', 'alta', 'critica'])->description('Predeterminada: media.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:20000',
            'solicitante_email' => 'required|email|max:255',
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->where('activa', true)],
            'prioridad' => 'sometimes|in:baja,media,alta,critica',
        ]);

        $usuario = Usuario::where('correo', strtolower($data['solicitante_email']))->where('estado', 'activo')->first();
        if (! $usuario) {
            return Response::error('El solicitante debe estar registrado y activo en el HelpDesk.');
        }

        $actorId = $request->user()?->id ?? $usuario->id;
        $ticket = DB::transaction(function () use ($data, $usuario, $actorId) {
            // Serialize MCP ticket numbering through a stable row, including when no tickets exist.
            Categoria::orderBy('id')->lockForUpdate()->firstOrFail();
            $prioridad = $data['prioridad'] ?? 'media';
            $ticket = Ticket::create([
                'numero' => Ticket::generarNumero(),
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'],
                'categoria_id' => $data['categoria_id'],
                'solicitante_id' => $usuario->id,
                'creado_por' => $actorId,
                'prioridad' => $prioridad,
                'estado' => 'nuevo',
                // Keep the existing enum: this is a request made on behalf of a user.
                'origen' => 'usuario',
                'fecha_limite' => SlaCalculator::agregarHorasLaborables(now(), Configuracion::slaHorasPara($prioridad)),
            ]);
            HistorialTicket::registrar($ticket->id, 'creado', 'Ticket creado mediante MCP en nombre del solicitante.');
            ActividadLog::registrar('creó', 'tickets', "MCP creó ticket {$ticket->numero}", $ticket->numero);

            return $ticket;
        });

        $ticket->load(['categoria', 'solicitante']);
        $avisos = [];
        try {
            Mail::to($usuario->correo)->send(new TicketCreado($ticket));
        } catch (\Throwable $exception) {
            report($exception);
            $avisos[] = 'El ticket se creó, pero no se pudo enviar el correo.';
        }
        try {
            (new TeamsService)->notificarTicketNuevo($ticket);
        } catch (\Throwable $exception) {
            report($exception);
            $avisos[] = 'El ticket se creó, pero no se pudo notificar a Teams.';
        }

        return Response::json([
            'success' => true,
            'numero' => $ticket->numero,
            'ticket_id' => $ticket->id,
            'url' => config('app.url').'/tickets/'.$ticket->id,
            'avisos' => $avisos,
        ]);
    }
}
