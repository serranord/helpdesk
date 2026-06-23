<?php
namespace App\Http\Controllers;
use App\Models\Ticket;

class TecnicoController extends Controller {
    public function panel() {
        $user = auth()->user();

        $misTickets = Ticket::with(['categoria','solicitante'])
            ->where('tecnico_id', $user->id)
            ->whereNotIn('estado',['cerrado'])
            ->orderByRaw("CASE prioridad WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->orderBy('fecha_limite')
            ->get();

        $sinAsignar = Ticket::with(['categoria','solicitante'])
            ->whereNull('tecnico_id')
            ->whereNotIn('estado',['cerrado'])
            ->orderByRaw("CASE prioridad WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->orderBy('created_at')
            ->get();

        $stats = [
            'mis_abiertos'   => $misTickets->whereNotIn('estado',['resuelto'])->count(),
            'mis_vencidos'   => $misTickets->filter(fn($t) => $t->estaVencido())->count(),
            'sin_asignar'    => $sinAsignar->count(),
            'resueltos_hoy'  => Ticket::where('tecnico_id',$user->id)
                ->where('estado','resuelto')
                ->whereDate('fecha_resolucion', today())->count(),
        ];

        return view('tecnico.panel', compact('misTickets','sinAsignar','stats','user'));
    }
}
