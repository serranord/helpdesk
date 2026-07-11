<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActividadLog;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\HistorialTicket;
use App\Models\Ticket;
use App\Models\Usuario;
use App\Mail\TicketCreado;
use App\Services\TeamsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TicketApiController extends Controller
{
    // Listar categorías activas — para que el agente elija la correcta antes de crear el ticket
    public function categorias(): JsonResponse
    {
        return response()->json(
            Categoria::where('activa', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'descripcion', 'sla_horas'])
        );
    }

    // Crear ticket — usado por el agente de Copilot tras la aprobación en Teams
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo'             => 'required|string|max:255',
            'descripcion'        => 'required|string',
            'solicitante_email'  => 'required|email',
            'solicitante_nombre' => 'nullable|string|max:255',
            'categoria_id'       => 'nullable|integer|exists:categorias,id',
            'prioridad'          => 'nullable|in:baja,media,alta,critica',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Solicitante: buscar por correo (incluyendo borrados). Si no existe, crear un usuario real automáticamente
        // (asi el ticket queda bien atribuido y se acumula historial si esa persona vuelve a escribir).
        $correo = strtolower(trim($data['solicitante_email']));
        $solicitante = Usuario::withTrashed()->where('correo', $correo)->first();

        if (!$solicitante) {
            $solicitante = Usuario::create([
                'correo'   => $correo,
                'nombre'   => $data['solicitante_nombre'] ?? Str::before($correo, '@'),
                'password' => Hash::make(Str::random(32)),
                'rol'      => 'solicitante',
                'estado'   => 'activo',
            ]);
        } elseif ($solicitante->trashed()) {
            $solicitante->restore();
        }

        // Categoría: la indicada, o "Interno TI" como respaldo si el agente no pudo clasificarla
        $categoria = isset($data['categoria_id'])
            ? Categoria::find($data['categoria_id'])
            : Categoria::where('nombre', 'Interno TI')->first() ?? Categoria::first();

        if (!$categoria) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No hay categorías configuradas en el sistema.',
            ], 422);
        }

        $prioridad = $data['prioridad'] ?? 'media';

        $ticket = Ticket::create([
            'numero'         => Ticket::generarNumero(),
            'titulo'         => $data['titulo'],
            'descripcion'    => $data['descripcion'],
            'prioridad'      => $prioridad,
            'categoria_id'   => $categoria->id,
            'solicitante_id' => $solicitante->id,
            'tecnico_id'     => null,
            'creado_por'     => $solicitante->id,
            'origen'         => 'copilot',
            'estado'         => 'nuevo',
            'fecha_limite'   => now()->addHours(Configuracion::slaHorasPara($prioridad)),
        ]);

        ActividadLog::registrar('creó', 'tickets', "Copilot creó ticket {$ticket->numero} desde correo entrante", $ticket->numero);
        HistorialTicket::registrar($ticket->id, 'creado', 'Ticket creado automáticamente por el agente de Copilot desde un correo, previa aprobación en Teams');

        $ticket->load(['categoria', 'solicitante']);

        try {
            Mail::to($ticket->solicitante->correo)->send(new TicketCreado($ticket));
        } catch (\Exception $e) {}

        try {
            (new TeamsService())->notificarTicketNuevo($ticket);
        } catch (\Exception $e) {}

        return response()->json([
            'success'   => true,
            'ticket_id' => $ticket->id,
            'numero'    => $ticket->numero,
            'url'       => config('app.url') . '/tickets/' . $ticket->id,
        ], 201);
    }

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
