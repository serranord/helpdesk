@extends('layouts.app')
@section('title','Panel Técnico')
@section('page-title','Mi Panel de Trabajo')
@section('content')

<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card purple">
        <div class="stat-label">Mis Tickets Activos</div>
        <div class="stat-value">{{ $stats['mis_abiertos'] }}</div>
        <div class="stat-sub">Asignados a mí</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Vencidos</div>
        <div class="stat-value">{{ $stats['mis_vencidos'] }}</div>
        <div class="stat-sub">SLA excedido</div>
    </div>
    <div class="stat-card cyan">
        <div class="stat-label">Sin Asignar</div>
        <div class="stat-value">{{ $stats['sin_asignar'] }}</div>
        <div class="stat-sub">Disponibles para tomar</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Resueltos Hoy</div>
        <div class="stat-value">{{ $stats['resueltos_hoy'] }}</div>
        <div class="stat-sub">¡Buen trabajo!</div>
    </div>
</div>

<div class="grid-2" style="align-items:start">

    {{-- MIS TICKETS --}}
    <div class="card" style="padding:0">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
            <div class="card-title">Mis Tickets Asignados</div>
        </div>
        @forelse($misTickets as $t)
        <a href="{{ route('tickets.show',$t) }}" style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border);text-decoration:none;transition:background .15s" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
            <span class="prio-dot prio-{{ $t->prioridad }}" style="width:10px;height:10px"></span>
            <div style="flex:1;min-width:0">
                <div style="font-size:13.5px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->titulo }}</div>
                <div style="font-size:11.5px;color:var(--text-muted);margin-top:3px;display:flex;gap:8px;align-items:center">
                    <span class="mono">{{ $t->numero }}</span>
                    <span>·</span>
                    <span>{{ $t->solicitante->nombre }}</span>
                    <span>·</span>
                    <span>{{ $t->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
                <span class="badge badge-{{ $t->estado_color }}">{{ $t->estado_label }}</span>
                @if($t->estaVencido())
                    <span style="font-size:10px;background:var(--red);color:#fff;padding:1px 6px;border-radius:10px;font-weight:700">VENCIDO</span>
                @elseif($t->fecha_limite)
                    <span style="font-size:10.5px;color:{{ $t->fecha_limite->diffInHours(now()) < 2 ? 'var(--amber)' : 'var(--text-muted)' }}">
                        ⏱ {{ $t->fecha_limite->diffForHumans() }}
                    </span>
                @endif
            </div>
        </a>
        @empty
        <div class="empty-state"><p>No tienes tickets asignados 🎉</p></div>
        @endforelse
    </div>

    {{-- SIN ASIGNAR --}}
    <div class="card" style="padding:0">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <div class="card-title">Tickets Sin Asignar</div>
            <span class="badge badge-cyan">{{ $sinAsignar->count() }}</span>
        </div>
        @forelse($sinAsignar as $t)
        <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
            <span class="prio-dot prio-{{ $t->prioridad }}" style="width:10px;height:10px"></span>
            <div style="flex:1;min-width:0">
                <a href="{{ route('tickets.show',$t) }}" style="font-size:13.5px;font-weight:600;color:var(--text);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block">{{ $t->titulo }}</a>
                <div style="font-size:11.5px;color:var(--text-muted);margin-top:3px">
                    <span class="mono">{{ $t->numero }}</span> · {{ $t->categoria->icono }} {{ $t->categoria->nombre }} · {{ $t->created_at->diffForHumans() }}
                </div>
            </div>
            {{-- Tomar ticket --}}
            <form action="{{ route('tickets.asignar',$t) }}" method="POST">
                @csrf
                <input type="hidden" name="tecnico_id" value="{{ $user->id }}">
                <button type="submit" class="btn btn-primary btn-sm">Tomar</button>
            </form>
        </div>
        @empty
        <div class="empty-state"><p>No hay tickets sin asignar</p></div>
        @endforelse
    </div>
</div>
@endsection
