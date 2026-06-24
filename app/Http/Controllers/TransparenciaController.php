<?php
namespace App\Http\Controllers;
use App\Models\Ticket; use App\Models\Usuario; use App\Models\AvisoTI;
use Illuminate\Http\Request;

class TransparenciaController extends Controller {

    public function index() {
        $user = auth()->user();

        // Carga actual por técnico
        $tecnicos = Usuario::where('estado','activo')
            ->whereIn('rol',['tecnico','administrador'])
            ->withCount([
                'ticketsAsignados as activos' => fn($q) => $q->whereNotIn('estado',['resuelto','cerrado']),
                'ticketsAsignados as criticos' => fn($q) => $q->where('prioridad','critica')->whereNotIn('estado',['resuelto','cerrado']),
                'ticketsAsignados as en_proceso' => fn($q) => $q->where('estado','en_proceso'),
            ])
            ->orderByDesc('activos')
            ->get();

        // Tickets en progreso — el solicitante NO ve quién lo pidió
        $enProceso = Ticket::with(['categoria','tecnico'])
            ->whereIn('estado',['en_proceso','asignado'])
            ->orderByRaw("CASE prioridad WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->orderBy('created_at')
            ->get();

        // Tickets nuevos sin asignar
        $sinAsignar = Ticket::where('estado','nuevo')
            ->orderByRaw("CASE prioridad WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->count();

        // Avisos activos del equipo TI
        $avisos = AvisoTI::vigentes()->get();

        // Estadísticas generales
        $stats = [
            'hoy_resueltos' => Ticket::where('estado','resuelto')->whereDate('fecha_resolucion', today())->count(),
            'semana'        => Ticket::whereBetween('created_at',[now()->startOfWeek(), now()])->count(),
            'en_proceso'    => Ticket::whereIn('estado',['en_proceso','asignado'])->count(),
            'criticos'      => Ticket::where('prioridad','critica')->whereNotIn('estado',['resuelto','cerrado'])->count(),
        ];

        return view('transparencia.index', compact('tecnicos','enProceso','sinAsignar','avisos','stats','user'));
    }
}
