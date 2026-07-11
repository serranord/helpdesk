@extends('layouts.app')
@section('title','Trabajando Hoy')
@section('page-title','📋 Trabajando Hoy')
@section('content')

<div style="margin-bottom:16px;font-size:13px;color:var(--text-muted)">
    Selecciona los tickets que se van a trabajar hoy (por ejemplo, tras la reunión matutina con el coordinador). Esta selección no afecta el estado, prioridad ni SLA del ticket — es solo una vista de planificación diaria.
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

    {{-- Columna: Trabajando hoy --}}
    <div class="card" style="padding:0">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <div class="card-title" style="margin-bottom:0">✅ Trabajando Hoy ({{ $seleccionados->count() }})</div>
        </div>
        <div style="max-height:70vh;overflow-y:auto">
            @forelse($seleccionados as $t)
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 18px;border-bottom:1px solid var(--border)">
                <div style="min-width:0">
                    <a href="{{ route('tickets.show',$t) }}" style="font-size:13.5px;font-weight:600;color:var(--text);text-decoration:none">{{ $t->numero }} — {{ $t->titulo }}</a>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:3px;display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                        <span class="badge badge-{{ $t->prioridad_color }}">{{ ucfirst($t->prioridad) }}</span>
                        <span class="badge badge-{{ $t->estado_color }}">{{ $t->estado_label }}</span>
                        <span>{{ $t->solicitante?->nombre }}</span>
                        @if($t->tecnico) <span>· {{ $t->tecnico->nombre }}</span> @endif
                    </div>
                </div>
                <form action="{{ route('tickets.trabajando-hoy.toggle',$t) }}" method="POST" style="flex-shrink:0">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Quitar</button>
                </form>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">Aún no has seleccionado tickets para hoy. Agrégalos desde la columna de la derecha.</div>
            @endforelse
        </div>
    </div>

    {{-- Columna: Disponibles --}}
    <div class="card" style="padding:0">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border)">
            <div class="card-title" style="margin-bottom:0">🗂️ Tickets disponibles ({{ $disponibles->count() }})</div>
        </div>
        <div style="max-height:70vh;overflow-y:auto">
            @forelse($disponibles as $t)
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 18px;border-bottom:1px solid var(--border)">
                <div style="min-width:0">
                    <a href="{{ route('tickets.show',$t) }}" style="font-size:13.5px;font-weight:600;color:var(--text);text-decoration:none">{{ $t->numero }} — {{ $t->titulo }}</a>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:3px;display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                        <span class="badge badge-{{ $t->prioridad_color }}">{{ ucfirst($t->prioridad) }}</span>
                        <span class="badge badge-{{ $t->estado_color }}">{{ $t->estado_label }}</span>
                        <span>{{ $t->solicitante?->nombre }}</span>
                        @if($t->tecnico) <span>· {{ $t->tecnico->nombre }}</span> @endif
                    </div>
                </div>
                <form action="{{ route('tickets.trabajando-hoy.toggle',$t) }}" method="POST" style="flex-shrink:0">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">+ Agregar</button>
                </form>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">No hay más tickets disponibles.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
