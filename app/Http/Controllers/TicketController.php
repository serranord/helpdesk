<?php
namespace App\Http\Controllers;
use App\Models\Ticket; use App\Models\Categoria; use App\Models\Usuario;
use App\Models\Comentario; use App\Models\ActividadLog; use App\Models\HistorialTicket;
use App\Models\Notificacion; use App\Models\TicketVinculado;
use App\Mail\TicketCreado; use App\Mail\TicketActualizado; use App\Mail\TicketResuelto;
use App\Services\TeamsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller {

    public function index(Request $request) {
        $user  = auth()->user();
        $query = Ticket::with(['categoria','solicitante','tecnico']);

        if ($user->esSolicitante())
            $query->where('solicitante_id', $user->id);
        elseif ($user->esTecnico())
            $query->where(fn($q) => $q->where('tecnico_id', $user->id)->orWhereNull('tecnico_id'));

        if ($request->filled('estado'))    $query->where('estado', $request->estado);
        if ($request->filled('prioridad')) $query->where('prioridad', $request->prioridad);
        if ($request->filled('categoria')) $query->where('categoria_id', $request->categoria);
        if ($request->filled('buscar'))    $query->where(fn($q) =>
            $q->where('numero','like','%'.$request->buscar.'%')
              ->orWhere('titulo','like','%'.$request->buscar.'%')
              ->orWhereHas('solicitante', fn($q2) => $q2->where('nombre','like','%'.$request->buscar.'%')));

        $tickets    = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $categorias = Categoria::where('activa',true)->get();
        $estados    = Ticket::estados();
        return view('tickets.index', compact('tickets','categorias','estados'));
    }

    public function create() {
        $categorias   = Categoria::where('activa',true)->get();
        $solicitantes = Usuario::where('estado','activo')->orderBy('nombre')->get();
        $tecnicos     = Usuario::where('estado','activo')->whereIn('rol',['tecnico','administrador'])->orderBy('nombre')->get();
        $plantillas   = \App\Models\Plantilla::where('activa',true)->with('categoria')->orderBy('nombre')->get();
        return view('tickets.create', compact('categorias','solicitantes','tecnicos','plantillas'));
    }

    public function store(Request $request) {
        $user = auth()->user();
        $data = $request->validate([
            'titulo'        => 'required|string|max:255',
            'descripcion'   => 'required|string',
            'categoria_id'  => 'required|exists:categorias,id',
            'solicitante_id'=> 'nullable|exists:usuarios,id',
            'tecnico_id'    => 'nullable|exists:usuarios,id',
            'prioridad'     => 'nullable|in:baja,media,alta,critica',
        ]);

        $prioridad   = ($user->puedeGestionar() && $request->filled('prioridad')) ? $data['prioridad'] : 'media';
        $categoria   = Categoria::find($data['categoria_id']);
        $solicitante = $user->puedeGestionar() && $request->filled('solicitante_id') ? $data['solicitante_id'] : $user->id;
        $estadoInicial = ($user->puedeGestionar() && $request->filled('tecnico_id')) ? 'asignado' : 'nuevo';

        $ticket = Ticket::create([
            'numero'         => Ticket::generarNumero(),
            'titulo'         => $data['titulo'],
            'descripcion'    => $data['descripcion'],
            'prioridad'      => $prioridad,
            'categoria_id'   => $data['categoria_id'],
            'solicitante_id' => $solicitante,
            'tecnico_id'     => $user->puedeGestionar() ? ($data['tecnico_id'] ?? null) : null,
            'creado_por'     => $user->id,
            'origen'         => $user->esSolicitante() ? 'usuario' : 'tecnico',
            'estado'         => $estadoInicial,
            'fecha_limite'   => now()->addHours($categoria->sla_horas),
        ]);

        ActividadLog::registrar('creó', 'tickets', "Creó ticket {$ticket->numero}", $ticket->numero);
        HistorialTicket::registrar($ticket->id, 'creado', 'Ticket creado');

        // Correo de confirmación
        try {
            $ticket->load(['categoria','solicitante']);
            Mail::to($ticket->solicitante->correo)->send(new TicketCreado($ticket));
        } catch (\Exception $e) {}

        // Teams — notifica al canal con mención al solicitante
        try {
            (new TeamsService())->notificarTicketNuevo($ticket);
        } catch (\Exception $e) {}

        return redirect()->route('tickets.show', $ticket)->with('success', "Ticket {$ticket->numero} creado correctamente.");
    }

    public function show(Ticket $ticket) {
        $user = auth()->user();
        if ($user->esSolicitante() && $ticket->solicitante_id !== $user->id) abort(403);

        $ticket->load(['categoria','solicitante','tecnico','creadoPor','comentarios.usuario','calificacion','adjuntos.usuario','historial.usuario','hijosVinculados.hijo.solicitante','padreVinculado.padre']);
        $tecnicos        = Usuario::where('estado','activo')->whereIn('rol',['tecnico','administrador'])->orderBy('nombre')->get();
        $estados         = Ticket::estados();
        $ticketsVinculables = $user->puedeGestionar()
            ? Ticket::where('id','!=',$ticket->id)->whereNotIn('estado',['cerrado'])->whereDoesntHave('padreVinculado')->orderByDesc('created_at')->limit(50)->get()
            : collect();

        return view('tickets.show', compact('ticket','tecnicos','user','estados','ticketsVinculables'));
    }

    public function cambiarEstado(Request $request, Ticket $ticket) {
        $request->validate(['estado' => 'required|in:nuevo,abierto,asignado,en_proceso,pendiente,resuelto,cerrado']);
        $viejo = $ticket->estado_label;
        $ticket->estado = $request->estado;
        if (in_array($request->estado, ['resuelto','cerrado'])) {
            $ticket->fecha_resolucion = now();
            $ticket->nota_cierre = $request->nota_cierre;
        }
        $ticket->save();
        $ticket->load(['solicitante','tecnico','categoria']);

        // Correo si fue resuelto
        if ($request->estado === 'resuelto') {
            try { Mail::to($ticket->solicitante->correo)->send(new TicketResuelto($ticket)); } catch (\Exception $e) {}
            try { (new TeamsService())->notificarResuelto($ticket); } catch (\Exception $e) {}
        } else {
            // Teams — notifica cambio de estado con mención al solicitante
            try { (new TeamsService())->notificarCambioEstado($ticket, $viejo); } catch (\Exception $e) {}
        }

        // Notificación interna
        if ($ticket->solicitante_id !== auth()->id()) {
            Notificacion::create(['usuario_id'=>$ticket->solicitante_id,'tipo'=>'estado_cambiado','titulo'=>'Estado actualizado','mensaje'=>"Tu solicitud {$ticket->numero} cambió a: {$ticket->estado_label}",'url'=>route('tickets.show',$ticket),'referencia'=>$ticket->numero]);
        }

        HistorialTicket::registrar($ticket->id, 'estado', "Estado cambiado de '{$viejo}' a '{$ticket->estado_label}'", 'estado', $viejo, $ticket->estado_label);
        ActividadLog::registrar('actualizó', 'tickets', "Cambió estado {$ticket->numero}", $ticket->numero);
        return back()->with('success', "Estado actualizado a «{$ticket->estado_label}».");
    }

    public function asignar(Request $request, Ticket $ticket) {
        $request->validate(['tecnico_id' => 'nullable|exists:usuarios,id']);
        $ticket->tecnico_id = $request->tecnico_id;
        if ($request->tecnico_id && in_array($ticket->estado, ['nuevo','abierto'])) $ticket->estado = 'asignado';
        $ticket->save();
        $ticket->load(['solicitante','tecnico','categoria']);

        $nombre = $ticket->tecnico?->nombre ?? 'sin técnico';

        // Notificación interna al técnico
        if ($ticket->tecnico_id && $ticket->tecnico_id !== auth()->id()) {
            Notificacion::create(['usuario_id'=>$ticket->tecnico_id,'tipo'=>'ticket_asignado','titulo'=>'Ticket asignado','mensaje'=>"Se te asignó el ticket {$ticket->numero}: {$ticket->titulo}",'url'=>route('tickets.show',$ticket),'referencia'=>$ticket->numero]);
        }

        // Teams — notifica al solicitante que fue asignado
        if ($ticket->tecnico_id) {
            try { (new TeamsService())->notificarTecnicoAsignado($ticket); } catch (\Exception $e) {}
        }

        HistorialTicket::registrar($ticket->id, 'asignacion', "Asignado a {$nombre}");
        ActividadLog::registrar('asignó', 'tickets', "Asignó {$ticket->numero} a {$nombre}", $ticket->numero);
        return back()->with('success', 'Técnico asignado correctamente.');
    }

    public function cambiarPrioridad(Request $request, Ticket $ticket) {
        $request->validate(['prioridad' => 'required|in:baja,media,alta,critica']);
        $viejo = $ticket->prioridad;
        $ticket->prioridad = $request->prioridad;
        $ticket->save();
        HistorialTicket::registrar($ticket->id, 'prioridad', "Prioridad cambiada de {$viejo} a {$ticket->prioridad}", 'prioridad', $viejo, $ticket->prioridad);
        ActividadLog::registrar('actualizó', 'tickets', "Cambió prioridad {$ticket->numero}", $ticket->numero);
        return back()->with('success', 'Prioridad actualizada.');
    }

    public function estimarAtencion(Request $request, Ticket $ticket) {
        $request->validate(['estimado_en' => 'required|date|after:now']);
        $ticket->estimado_en = $request->estimado_en;
        $ticket->save();

        Notificacion::create(['usuario_id'=>$ticket->solicitante_id,'tipo'=>'estado_cambiado','titulo'=>'Tiempo estimado de atención','mensaje'=>"Tu solicitud {$ticket->numero} será atendida aproximadamente el ".date('d/m/Y H:i', strtotime($request->estimado_en)),'url'=>route('tickets.show',$ticket),'referencia'=>$ticket->numero]);
        HistorialTicket::registrar($ticket->id, 'estimacion', "Tiempo estimado: ".date('d/m/Y H:i', strtotime($request->estimado_en)));
        return back()->with('success', 'Tiempo estimado establecido. El solicitante fue notificado.');
    }

    public function reabrir(Request $request, Ticket $ticket) {
        $user = auth()->user();
        if ($ticket->solicitante_id !== $user->id) abort(403);
        if (!in_array($ticket->estado, ['resuelto','cerrado'])) return back()->with('error', 'Solo puedes reabrir tickets resueltos o cerrados.');

        $ticket->estado    = 'abierto';
        $ticket->reabierto = true;
        $ticket->fecha_resolucion = null;
        $ticket->save();

        Comentario::create(['ticket_id'=>$ticket->id,'usuario_id'=>$user->id,'contenido'=>"Ticket reabierto. Motivo: ".($request->motivo ?? 'El problema persiste.'),'es_interno'=>false]);
        HistorialTicket::registrar($ticket->id, 'reapertura', 'Ticket reabierto por el solicitante');

        if ($ticket->tecnico_id) {
            Notificacion::create(['usuario_id'=>$ticket->tecnico_id,'tipo'=>'ticket_asignado','titulo'=>'Ticket reabierto','mensaje'=>"El ticket {$ticket->numero} fue reabierto",'url'=>route('tickets.show',$ticket),'referencia'=>$ticket->numero]);
        }

        try { $ticket->load(['solicitante','tecnico','categoria']); (new TeamsService())->notificarReabierto($ticket); } catch (\Exception $e) {}
        return back()->with('success', 'Ticket reabierto correctamente.');
    }

    public function vincular(Request $request, Ticket $ticket) {
        $request->validate(['ticket_hijo_id' => 'required|exists:tickets,id']);
        $hijo = Ticket::find($request->ticket_hijo_id);
        if (TicketVinculado::where('ticket_hijo_id', $hijo->id)->exists())
            return back()->with('error', 'Ese ticket ya está vinculado a otro ticket padre.');
        TicketVinculado::create(['ticket_padre_id' => $ticket->id, 'ticket_hijo_id' => $hijo->id]);
        HistorialTicket::registrar($ticket->id, 'vinculacion', "Vinculado con {$hijo->numero}");
        return back()->with('success', "Ticket {$hijo->numero} vinculado correctamente.");
    }

    public function desvincular(Ticket $ticket, TicketVinculado $vinculo) {
        $vinculo->delete();
        return back()->with('success', 'Ticket desvinculado.');
    }

    public function comentar(Request $request, Ticket $ticket) {
        $user = auth()->user();
        if ($user->esSolicitante() && $ticket->solicitante_id !== $user->id) abort(403);
        $request->validate(['contenido' => 'required|string|max:2000']);

        $esInterno  = $user->puedeGestionar() && $request->boolean('es_interno');
        $comentario = Comentario::create(['ticket_id'=>$ticket->id,'usuario_id'=>$user->id,'contenido'=>$request->contenido,'es_interno'=>$esInterno]);

        if ($user->puedeGestionar() && in_array($ticket->estado, ['nuevo','abierto','asignado'])) {
            $ticket->estado = 'en_proceso'; $ticket->save();
        }

        $notificarA = $user->puedeGestionar() ? $ticket->solicitante_id : $ticket->tecnico_id;
        if ($notificarA && $notificarA !== $user->id && !$esInterno) {
            Notificacion::create(['usuario_id'=>$notificarA,'tipo'=>'comentario','titulo'=>'Nuevo comentario','mensaje'=>"Nuevo comentario en {$ticket->numero}",'url'=>route('tickets.show',$ticket),'referencia'=>$ticket->numero]);
        }

        // Teams — notifica al solicitante cuando el técnico comenta
        if ($user->puedeGestionar() && !$esInterno) {
            try {
                $ticket->load(['solicitante','tecnico','categoria']);
                Mail::to($ticket->solicitante->correo)->send(new TicketActualizado($ticket, $comentario));
                (new TeamsService())->notificarActualizacion($ticket, $request->contenido);
            } catch (\Exception $e) {}
        }

        ActividadLog::registrar('comentó', 'tickets', "Comentó en {$ticket->numero}", $ticket->numero);
        return back()->with('success', 'Actualización publicada.');
    }
}
