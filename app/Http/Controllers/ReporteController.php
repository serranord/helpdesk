<?php
namespace App\Http\Controllers;
use App\Models\Ticket; use App\Models\Usuario; use App\Models\Categoria;
use App\Models\Calificacion;
use Illuminate\Http\Request;

class ReporteController extends Controller {

    public function index(Request $request) {
        $desde  = $request->filled('desde')  ? $request->desde  : now()->startOfMonth()->format('Y-m-d');
        $hasta  = $request->filled('hasta')  ? $request->hasta  : now()->format('Y-m-d');
        $tecnico_id  = $request->tecnico_id;
        $categoria_id = $request->categoria_id;

        $query = Ticket::whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);
        if ($tecnico_id)   $query->where('tecnico_id',   $tecnico_id);
        if ($categoria_id) $query->where('categoria_id', $categoria_id);

        $tickets = (clone $query)->with(['categoria','solicitante','tecnico','calificacion'])->orderByDesc('created_at')->get();

        // Métricas
        $total     = $tickets->count();
        $resueltos = $tickets->whereIn('estado',['resuelto','cerrado'])->count();
        $vencidos  = $tickets->filter(fn($t) => $t->estaVencido())->count();
        $promedio_resolucion = $tickets->whereNotNull('fecha_resolucion')
            ->avg(fn($t) => $t->created_at->diffInHours($t->fecha_resolucion));

        // Por categoría
        $por_categoria = $tickets->groupBy('categoria.nombre')->map->count()->sortDesc();

        // Por técnico
        $por_tecnico = $tickets->whereNotNull('tecnico_id')
            ->groupBy('tecnico.nombre')->map->count()->sortDesc();

        // Por estado
        $por_estado = $tickets->groupBy('estado')->map->count();

        // Calificación promedio
        $cal_promedio = $tickets->whereNotNull('calificacion')->avg('calificacion.estrellas');

        $tecnicos   = Usuario::where('estado','activo')->whereIn('rol',['tecnico','administrador'])->orderBy('nombre')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('reportes.index', compact(
            'tickets','desde','hasta','total','resueltos','vencidos',
            'promedio_resolucion','por_categoria','por_tecnico','por_estado',
            'cal_promedio','tecnicos','categorias','tecnico_id','categoria_id'
        ));
    }

    public function exportarExcel(Request $request) {
        $desde = $request->filled('desde') ? $request->desde : now()->startOfMonth()->format('Y-m-d');
        $hasta = $request->filled('hasta') ? $request->hasta : now()->format('Y-m-d');

        $tickets = Ticket::with(['categoria','solicitante','tecnico','calificacion'])
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->when($request->tecnico_id,   fn($q) => $q->where('tecnico_id',   $request->tecnico_id))
            ->when($request->categoria_id, fn($q) => $q->where('categoria_id', $request->categoria_id))
            ->orderByDesc('created_at')->get();

        $filename = 'tickets_' . $desde . '_al_' . $hasta . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($tickets) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel
            fputcsv($f, ['Número','Título','Categoría','Prioridad','Estado','Solicitante','Técnico','Fecha Creación','Fecha Resolución','Calificación','Hrs. Resolución']);
            foreach ($tickets as $t) {
                fputcsv($f, [
                    $t->numero,
                    $t->titulo,
                    $t->categoria->nombre,
                    ucfirst($t->prioridad),
                    $t->estado_label,
                    $t->solicitante->nombre,
                    $t->tecnico?->nombre ?? 'Sin asignar',
                    $t->created_at->format('d/m/Y H:i'),
                    $t->fecha_resolucion?->format('d/m/Y H:i') ?? '—',
                    $t->calificacion ? $t->calificacion->estrellas . '/5' : '—',
                    $t->fecha_resolucion ? $t->created_at->diffInHours($t->fecha_resolucion) . 'h' : '—',
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
