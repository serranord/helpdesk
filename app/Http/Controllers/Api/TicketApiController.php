<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketApiController extends Controller
{
    // Consultar ticket por número — para Power Automate / Copilot
    public function porNumero(string $numero): JsonResponse
    {
        $ticket = Ticket::with(['categoria','solicitante','tecnico','comentarios' => function($q) {
            $q->where('es_interno', false)->orderByDesc('created_at')->limit(3);
        }])
        ->where('numero', strtoupper($numero))
        ->first();

        if (!$ticket) {
            return response()->json([
                'encontrado' => false,
                'mensaje'    => "No encontré ningún ticket con el número {$numero}. Verifica que el número sea correcto.",
            ], 404);
        }

        return response()->json([
            'encontrado'    => true,
            'numero'        => $ticket->numero,
            'titulo'        => $ticket->titulo,
            'estado'        => $ticket->estado_label,
            'prioridad'     => ucfirst($ticket->prioridad),
            'categoria'     => $ticket->categoria->nombre,
            'tecnico'       => $ticket->tecnico?->nombre ?? 'Sin asignar',
            'solicitante'   => $ticket->solicitante->nombre,
            'creado'        => $ticket->created_at->format('d/m/Y H:i'),
            'estimado_en'   => $ticket->estimado_en?->format('d/m/Y H:i'),
            'sla_vencido'   => $ticket->estaVencido(),
            'sla_limite'    => $ticket->fecha_limite?->format('d/m/Y H:i'),
            'resuelto_en'   => $ticket->fecha_resolucion?->format('d/m/Y H:i'),
            'ultima_actualizacion' => $ticket->comentarios->first()?->contenido ?? 'Sin actualizaciones aún',
            'url'           => config('app.url') . '/tickets/' . $ticket->id,
            'resumen'       => $this->generarResumen($ticket),
        ]);
    }

    // Consultar tickets de un usuario por correo
    public function porUsuario(string $correo): JsonResponse
    {
        $usuario = Usuario::where('correo', strtolower($correo))->first();

        if (!$usuario) {
            return response()->json([
                'encontrado' => false,
                'mensaje'    => "No encontré ningún usuario con el correo {$correo}.",
            ], 404);
        }

        $tickets = Ticket::with(['categoria','tecnico'])
            ->where('solicitante_id', $usuario->id)
            ->whereNotIn('estado', ['cerrado'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'encontrado' => true,
            'usuario'    => $usuario->nombre,
            'total'      => $tickets->count(),
            'tickets'    => $tickets->map(fn($t) => [
                'numero'   => $t->numero,
                'titulo'   => $t->titulo,
                'estado'   => $t->estado_label,
                'prioridad'=> ucfirst($t->prioridad),
                'tecnico'  => $t->tecnico?->nombre ?? 'Sin asignar',
                'creado'   => $t->created_at->diffForHumans(),
                'url'      => config('app.url') . '/tickets/' . $t->id,
            ]),
        ]);
    }

    // Estadísticas generales del equipo
    public function estadisticas(): JsonResponse
    {
        return response()->json([
            'nuevos'      => Ticket::where('estado','nuevo')->count(),
            'en_proceso'  => Ticket::whereIn('estado',['en_proceso','asignado'])->count(),
            'pendientes'  => Ticket::where('estado','pendiente')->count(),
            'criticos'    => Ticket::where('prioridad','critica')->whereNotIn('estado',['resuelto','cerrado'])->count(),
            'resueltos_hoy' => Ticket::where('estado','resuelto')->whereDate('fecha_resolucion', today())->count(),
            'sla_vencidos'  => Ticket::whereNotIn('estado',['resuelto','cerrado'])->where('fecha_limite','<',now())->count(),
        ]);
    }

    private function generarResumen(Ticket $ticket): string
    {
        $resumen = "Tu ticket **{$ticket->numero}** — _{$ticket->titulo}_ ";

        $resumen .= match($ticket->estado) {
            'nuevo'      => "está en cola, aún no ha sido revisado por el equipo de TI.",
            'abierto'    => "fue revisado y está pendiente de asignación.",
            'asignado'   => "fue asignado a **{$ticket->tecnico?->nombre}** y será atendido pronto.",
            'en_proceso' => "está siendo trabajado activamente por **{$ticket->tecnico?->nombre}**.",
            'pendiente'  => "está en espera de información o recursos adicionales.",
            'resuelto'   => "fue resuelto el {$ticket->fecha_resolucion?->format('d/m/Y')}.",
            'cerrado'    => "está cerrado.",
            default      => "está en estado {$ticket->estado_label}.",
        };

        if ($ticket->estimado_en && !in_array($ticket->estado, ['resuelto','cerrado'])) {
            $resumen .= " Se estima atención para el **{$ticket->estimado_en->format('d/m/Y H:i')}**.";
        }

        if ($ticket->estaVencido()) {
            $resumen .= " ⚠️ El SLA de este ticket está vencido.";
        }

        return $resumen;
    }
}
