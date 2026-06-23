<?php
namespace App\Http\Controllers;
use App\Models\Ticket; use App\Models\Usuario; use App\Models\Categoria;

class DashboardController extends Controller {
    public function index() {
        $user  = auth()->user();
        $query = Ticket::query();

        if ($user->esSolicitante())
            $query->where('solicitante_id', $user->id);
        elseif ($user->esTecnico())
            $query->where(fn($q) => $q->where('tecnico_id', $user->id)->orWhereNull('tecnico_id'));

        $stats = [
            'nuevo'       => (clone $query)->where('estado','nuevo')->count(),
            'en_proceso'  => (clone $query)->where('estado','en_proceso')->count(),
            'pendientes'  => (clone $query)->where('estado','pendiente')->count(),
            'resueltos'   => (clone $query)->where('estado','resuelto')->count(),
            'vencidos'    => (clone $query)->whereNotIn('estado',['resuelto','cerrado'])->where('fecha_limite','<',now())->count(),
            'total'       => (clone $query)->count(),
        ];

        $recientes = (clone $query)
            ->with(['categoria','solicitante','tecnico'])
            ->whereNotIn('estado',['cerrado'])
            ->orderByDesc('created_at')
            ->limit(8)->get();

        $criticos = (clone $query)
            ->with(['categoria','solicitante'])
            ->where('prioridad','critica')
            ->whereNotIn('estado',['resuelto','cerrado'])
            ->orderByDesc('created_at')
            ->limit(5)->get();

        // Datos para gráficas (solo admin/tecnico)
        $graficas = [];
        if ($user->puedeGestionar()) {
            // Tickets por estado
            $graficas['por_estado'] = Ticket::selectRaw('estado, count(*) as total')
                ->whereNotIn('estado',['cerrado'])
                ->groupBy('estado')->pluck('total','estado');

            // Tickets por categoría (top 6)
            $graficas['por_categoria'] = Ticket::selectRaw('categoria_id, count(*) as total')
                ->with('categoria')
                ->whereMonth('created_at', now()->month)
                ->groupBy('categoria_id')
                ->orderByDesc('total')
                ->limit(6)->get()
                ->mapWithKeys(fn($r) => [$r->categoria->nombre => $r->total]);

            // Tendencia últimos 7 días
            $graficas['tendencia'] = collect(range(6,0))->mapWithKeys(function($dias) {
                $fecha = now()->subDays($dias)->format('Y-m-d');
                $label = now()->subDays($dias)->format('d/m');
                return [$label => Ticket::whereDate('created_at', $fecha)->count()];
            });

            // Tickets por técnico
            $graficas['por_tecnico'] = Ticket::selectRaw('tecnico_id, count(*) as total')
                ->whereNotNull('tecnico_id')
                ->whereNotIn('estado',['cerrado'])
                ->groupBy('tecnico_id')
                ->with('tecnico')
                ->orderByDesc('total')
                ->limit(5)->get()
                ->mapWithKeys(fn($r) => [$r->tecnico->nombre => $r->total]);
        }

        return view('dashboard.index', compact('stats','recientes','criticos','user','graficas'));
    }
}
