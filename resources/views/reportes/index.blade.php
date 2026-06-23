@extends('layouts.app')
@section('title','Reportes')
@section('page-title','Reportes y Métricas')
@section('topbar-actions')
<a href="{{ route('reportes.exportar') }}?desde={{ $desde }}&hasta={{ $hasta }}&tecnico_id={{ $tecnico_id }}&categoria_id={{ $categoria_id }}" class="btn btn-success btn-sm">
    ⬇️ Exportar CSV
</a>
@endsection
@section('content')

{{-- Filtros --}}
<div class="card" style="margin-bottom:20px">
    <form method="GET" class="flex gap-2" style="flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Desde</label>
            <input type="date" name="desde" class="form-control" value="{{ $desde }}">
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Hasta</label>
            <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Técnico</label>
            <select name="tecnico_id" class="form-control">
                <option value="">Todos</option>
                @foreach($tecnicos as $t)
                <option value="{{ $t->id }}" @selected($tecnico_id==$t->id)>{{ $t->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0">
            <label class="form-label">Categoría</label>
            <select name="categoria_id" class="form-control">
                <option value="">Todas</option>
                @foreach($categorias as $c)
                <option value="{{ $c->id }}" @selected($categoria_id==$c->id)>{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
    </form>
</div>

{{-- KPIs --}}
<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card blue">
        <div class="stat-label">Total Tickets</div>
        <div class="stat-value">{{ $total }}</div>
        <div class="stat-sub">En el período seleccionado</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Resueltos</div>
        <div class="stat-value">{{ $resueltos }}</div>
        <div class="stat-sub">{{ $total > 0 ? round($resueltos/$total*100) : 0 }}% del total</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Vencidos</div>
        <div class="stat-value">{{ $vencidos }}</div>
        <div class="stat-sub">SLA excedido</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-label">Tiempo Promedio</div>
        <div class="stat-value">{{ $promedio_resolucion ? round($promedio_resolucion).'h' : '—' }}</div>
        <div class="stat-sub">Horas de resolución</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Calificación</div>
        <div class="stat-value">{{ $cal_promedio ? number_format($cal_promedio,1) : '—' }}</div>
        <div class="stat-sub">Promedio sobre 5 ⭐</div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px">
    {{-- Por categoría --}}
    <div class="card">
        <div class="card-title" style="margin-bottom:16px">Tickets por Categoría</div>
        @forelse($por_categoria as $nombre => $cantidad)
        @php $pct = $total > 0 ? round($cantidad/$total*100) : 0; @endphp
        <div style="margin-bottom:12px">
            <div class="flex items-center gap-2" style="margin-bottom:4px;justify-content:space-between">
                <span style="font-size:13px;font-weight:500">{{ $nombre }}</span>
                <span style="font-size:12px;color:var(--text-muted)">{{ $cantidad }} ({{ $pct }}%)</span>
            </div>
            <div style="height:6px;background:var(--surface-2);border-radius:4px">
                <div style="height:6px;background:var(--blue);border-radius:4px;width:{{ $pct }}%"></div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:20px"><p>Sin datos</p></div>
        @endforelse
    </div>

    {{-- Por técnico --}}
    <div class="card">
        <div class="card-title" style="margin-bottom:16px">Tickets por Técnico</div>
        @forelse($por_tecnico as $nombre => $cantidad)
        @php $pct = $total > 0 ? round($cantidad/$total*100) : 0; @endphp
        <div style="margin-bottom:12px">
            <div class="flex items-center gap-2" style="margin-bottom:4px;justify-content:space-between">
                <span style="font-size:13px;font-weight:500">{{ $nombre }}</span>
                <span style="font-size:12px;color:var(--text-muted)">{{ $cantidad }} tickets</span>
            </div>
            <div style="height:6px;background:var(--surface-2);border-radius:4px">
                <div style="height:6px;background:var(--purple);border-radius:4px;width:{{ $pct }}%"></div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:20px"><p>Sin tickets asignados</p></div>
        @endforelse
    </div>
</div>

{{-- Tabla de tickets --}}
<div class="card" style="padding:0">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
        <div class="card-title">Detalle de Tickets ({{ $total }})</div>
    </div>
    @if($tickets->isEmpty())
    <div class="empty-state"><p>No hay tickets en el período seleccionado</p></div>
    @else
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Número</th><th>Título</th><th>Categoría</th><th>Prioridad</th><th>Estado</th><th>Técnico</th><th>Creado</th><th>Resolución</th><th>Hrs.</th><th>⭐</th></tr>
            </thead>
            <tbody>
                @foreach($tickets as $t)
                <tr>
                    <td><a href="{{ route('tickets.show',$t) }}" class="link mono">{{ $t->numero }}</a></td>
                    <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->titulo }}</td>
                    <td style="font-size:12.5px">{{ $t->categoria->icono }} {{ $t->categoria->nombre }}</td>
                    <td><span style="display:inline-flex;align-items:center;gap:4px"><span class="prio-dot prio-{{ $t->prioridad }}"></span>{{ ucfirst($t->prioridad) }}</span></td>
                    <td><span class="badge badge-{{ $t->estado_color }}">{{ $t->estado_label }}</span></td>
                    <td style="font-size:12.5px">{{ $t->tecnico?->nombre ?? '—' }}</td>
                    <td class="mono text-muted">{{ $t->created_at->format('d/m/y') }}</td>
                    <td class="mono text-muted">{{ $t->fecha_resolucion?->format('d/m/y') ?? '—' }}</td>
                    <td class="text-muted">{{ $t->fecha_resolucion ? $t->created_at->diffInHours($t->fecha_resolucion).'h' : '—' }}</td>
                    <td>{{ $t->calificacion ? str_repeat('⭐',$t->calificacion->estrellas) : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
