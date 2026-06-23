@extends('layouts.app')
@section('title','Tickets')
@section('page-title','Tickets de Soporte')
@section('topbar-actions')
    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Nueva Solicitud
    </a>
@endsection
@section('content')

<form method="GET" class="filtros">
    <input type="text" name="buscar" class="form-control" placeholder="Buscar número o título..." value="{{ request('buscar') }}" style="min-width:200px">
    <select name="estado" class="form-control">
        <option value="">Todos los estados</option>
        @foreach($estados as $key => $label)
        <option value="{{ $key }}" @selected(request('estado')===$key)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="prioridad" class="form-control">
        <option value="">Todas las prioridades</option>
        @foreach(['baja'=>'Baja','media'=>'Media','alta'=>'Alta','critica'=>'Crítica'] as $k=>$v)
        <option value="{{ $k }}" @selected(request('prioridad')===$k)>{{ $v }}</option>
        @endforeach
    </select>
    <select name="categoria" class="form-control">
        <option value="">Todas las categorías</option>
        @foreach($categorias as $c)
        <option value="{{ $c->id }}" @selected(request('categoria')==$c->id)>{{ $c->icono }} {{ $c->nombre }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filtrar</button>
    @if(request()->hasAny(['buscar','estado','prioridad','categoria']))
    <a href="{{ route('tickets.index') }}" class="btn btn-outline btn-sm">✕ Limpiar</a>
    @endif
</form>

<div class="card" style="padding:0">
    @if($tickets->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            <p>No se encontraron tickets</p>
        </div>
    @else
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Solicitante</th>
                    <th>Técnico</th>
                    <th>SLA</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $t)
                <tr>
                    <td><a href="{{ route('tickets.show',$t) }}" class="link mono">{{ $t->numero }}</a></td>
                    <td style="max-width:220px">
                        <a href="{{ route('tickets.show',$t) }}" class="link" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block">{{ $t->titulo }}</a>
                    </td>
                    <td style="font-size:13px">{{ $t->categoria->icono }} {{ $t->categoria->nombre }}</td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:5px">
                            <span class="prio-dot prio-{{ $t->prioridad }}"></span>
                            {{ ucfirst($t->prioridad) }}
                        </span>
                    </td>
                    <td><span class="badge badge-{{ $t->estado_color }}">{{ $t->estado_label }}</span></td>
                    <td style="font-size:13px">{{ $t->solicitante->nombre }}</td>
                    <td style="font-size:13px">{{ $t->tecnico?->nombre ?? '—' }}</td>
                    <td>
                        @if($t->fecha_limite)
                            @if($t->estaVencido())
                                <span class="badge badge-red">Vencido</span>
                            @else
                                <span style="font-size:12px;color:var(--text-muted)">{{ $t->fecha_limite->diffForHumans() }}</span>
                            @endif
                        @else
                            <span style="color:var(--text-muted)">—</span>
                        @endif
                    </td>
                    <td><span class="text-muted mono">{{ $t->created_at->format('d/m/y') }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:16px 20px">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
