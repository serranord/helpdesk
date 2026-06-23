@extends('layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('topbar-actions')
    <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Nueva Solicitud
    </a>
@endsection
@section('content')

{{-- KPIs --}}
<div class="stats-grid">
    <a href="{{ route('tickets.index') }}?estado=nuevo" class="stat-card cyan">
        <div class="stat-label">Nuevos</div>
        <div class="stat-value">{{ $stats['nuevo'] }}</div>
        <div class="stat-sub">Sin revisar</div>
    </a>
    <a href="{{ route('tickets.index') }}?estado=en_proceso" class="stat-card purple">
        <div class="stat-label">En Proceso</div>
        <div class="stat-value">{{ $stats['en_proceso'] }}</div>
        <div class="stat-sub">Siendo atendidos</div>
    </a>
    <a href="{{ route('tickets.index') }}?estado=pendiente" class="stat-card amber">
        <div class="stat-label">Pendientes</div>
        <div class="stat-value">{{ $stats['pendientes'] }}</div>
        <div class="stat-sub">En espera</div>
    </a>
    <a href="{{ route('tickets.index') }}?estado=resuelto" class="stat-card green">
        <div class="stat-label">Resueltos</div>
        <div class="stat-value">{{ $stats['resueltos'] }}</div>
        <div class="stat-sub">Este período</div>
    </a>
    @if($stats['vencidos'] > 0)
    <a href="{{ route('tickets.index') }}" class="stat-card red">
        <div class="stat-label">⚠️ Vencidos</div>
        <div class="stat-value">{{ $stats['vencidos'] }}</div>
        <div class="stat-sub">SLA excedido</div>
    </a>
    @endif
</div>

{{-- GRÁFICAS (solo gestores) --}}
@if($user->puedeGestionar() && !empty($graficas))
<div class="grid-2" style="margin-bottom:24px">

    {{-- Tendencia 7 días --}}
    <div class="card">
        <div class="card-title" style="margin-bottom:16px">📈 Tickets últimos 7 días</div>
        @php
            $maxTendencia = max(array_values($graficas['tendencia']->toArray()) ?: [1]);
        @endphp
        <div style="display:flex;align-items:flex-end;gap:6px;height:100px">
            @foreach($graficas['tendencia'] as $dia => $total)
            @php $h = $maxTendencia > 0 ? round(($total/$maxTendencia)*100) : 0; @endphp
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px">
                <span style="font-size:11px;font-weight:600;color:var(--text)">{{ $total > 0 ? $total : '' }}</span>
                <div style="width:100%;background:var(--blue);border-radius:4px 4px 0 0;height:{{ max($h,4) }}%;min-height:4px;transition:height .3s"></div>
                <span style="font-size:10px;color:var(--text-muted)">{{ $dia }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Por estado --}}
    <div class="card">
        <div class="card-title" style="margin-bottom:16px">🔄 Distribución por estado</div>
        @php
            $estadoColors = ['nuevo'=>'cyan','abierto'=>'blue','asignado'=>'purple','en_proceso'=>'orange','pendiente'=>'amber','resuelto'=>'green'];
            $estadoLabels = \App\Models\Ticket::estados();
            $totalEstados = $graficas['por_estado']->sum();
        @endphp
        @forelse($graficas['por_estado'] as $estado => $cantidad)
        @php $pct = $totalEstados > 0 ? round($cantidad/$totalEstados*100) : 0; $color = $estadoColors[$estado] ?? 'gray'; @endphp
        <div style="margin-bottom:10px">
            <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                <span style="font-size:12.5px;font-weight:500">{{ $estadoLabels[$estado] ?? $estado }}</span>
                <span style="font-size:12px;color:var(--text-muted)">{{ $cantidad }} ({{ $pct }}%)</span>
            </div>
            <div style="height:6px;background:var(--surface-2);border-radius:4px">
                <div style="height:6px;background:var(--{{ $color }});border-radius:4px;width:{{ $pct }}%"></div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:16px"><p>Sin datos</p></div>
        @endforelse
    </div>

    {{-- Por categoría --}}
    <div class="card">
        <div class="card-title" style="margin-bottom:16px">🗂️ Por categoría (este mes)</div>
        @php $maxCat = max($graficas['por_categoria']->values()->toArray() ?: [1]); @endphp
        @forelse($graficas['por_categoria'] as $nombre => $cantidad)
        @php $pct = $maxCat > 0 ? round($cantidad/$maxCat*100) : 0; @endphp
        <div style="margin-bottom:10px">
            <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                <span style="font-size:12.5px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">{{ $nombre }}</span>
                <span style="font-size:12px;color:var(--text-muted)">{{ $cantidad }}</span>
            </div>
            <div style="height:6px;background:var(--surface-2);border-radius:4px">
                <div style="height:6px;background:var(--purple);border-radius:4px;width:{{ $pct }}%"></div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:16px"><p>Sin tickets este mes</p></div>
        @endforelse
    </div>

    {{-- Por técnico --}}
    <div class="card">
        <div class="card-title" style="margin-bottom:16px">👨‍💻 Carga por técnico</div>
        @php $maxTec = max($graficas['por_tecnico']->values()->toArray() ?: [1]); @endphp
        @forelse($graficas['por_tecnico'] as $nombre => $cantidad)
        @php $pct = $maxTec > 0 ? round($cantidad/$maxTec*100) : 0; @endphp
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
            <div style="width:30px;height:30px;background:var(--blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0">{{ strtoupper(substr($nombre,0,1)) }}</div>
            <div style="flex:1">
                <div style="display:flex;justify-content:space-between;margin-bottom:3px">
                    <span style="font-size:12.5px;font-weight:500">{{ $nombre }}</span>
                    <span style="font-size:12px;color:var(--text-muted)">{{ $cantidad }} tickets</span>
                </div>
                <div style="height:6px;background:var(--surface-2);border-radius:4px">
                    <div style="height:6px;background:var(--green);border-radius:4px;width:{{ $pct }}%"></div>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:16px"><p>Sin tickets asignados</p></div>
        @endforelse
    </div>
</div>
@endif

{{-- Tickets recientes y críticos --}}
<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Tickets Recientes</div>
            <a href="{{ route('tickets.index') }}" class="link" style="font-size:12.5px">Ver todos →</a>
        </div>
        @if($recientes->isEmpty())
            <div class="empty-state">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <p>No hay tickets activos</p>
            </div>
        @else
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($recientes as $t)
            <a href="{{ route('tickets.show',$t) }}" style="display:flex;align-items:center;gap:12px;text-decoration:none;padding:10px;border-radius:8px;border:1px solid var(--border);transition:background .15s" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
                <span class="prio-dot prio-{{ $t->prioridad }}"></span>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->titulo }}</div>
                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">{{ $t->numero }} · {{ $t->categoria->icono }} {{ $t->categoria->nombre }}</div>
                </div>
                <span class="badge badge-{{ $t->estado_color }}">{{ $t->estado_label }}</span>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">🔴 Tickets Críticos</div></div>
        @if($criticos->isEmpty())
            <div class="empty-state">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>Sin tickets críticos activos 🎉</p>
            </div>
        @else
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($criticos as $t)
            <a href="{{ route('tickets.show',$t) }}" style="display:flex;align-items:center;gap:12px;text-decoration:none;padding:10px;border-radius:8px;background:var(--red-light);border:1px solid #fca5a5;transition:opacity .15s" onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:600;color:#7f1d1d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->titulo }}</div>
                    <div style="font-size:11.5px;color:#991b1b;margin-top:2px">{{ $t->numero }} · {{ $t->solicitante->nombre }}</div>
                </div>
                @if($t->estaVencido())<span class="vencido-badge">VENCIDO</span>@endif
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
