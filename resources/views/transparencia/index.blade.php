@extends('layouts.app')
@section('title','Transparencia TI')
@section('page-title','Panel de Transparencia TI')
@section('content')

{{-- AVISOS ACTIVOS --}}
@foreach($avisos as $aviso)
<div style="background:var(--{{ $aviso->color }}-light);border:1px solid;border-color:{{ match($aviso->color) {'blue'=>'#93c5fd','amber'=>'#fcd34d','red'=>'#fca5a5','purple'=>'#c4b5fd','green'=>'#86efac',default=>'#e2e8f0'} }};border-radius:10px;padding:14px 18px;margin-bottom:12px;display:flex;align-items:flex-start;gap:12px">
    <span style="font-size:22px;flex-shrink:0">{{ $aviso->icono }}</span>
    <div style="flex:1">
        <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:3px">{{ $aviso->titulo }}</div>
        <div style="font-size:13px;color:var(--text-muted);line-height:1.5">{{ $aviso->mensaje }}</div>
        <div style="font-size:11.5px;color:var(--text-muted);margin-top:6px">
            Publicado por {{ $aviso->creadoPor->nombre }} · {{ $aviso->created_at->diffForHumans() }}
            @if($aviso->expira_en) · Expira {{ $aviso->expira_en->format('d/m/Y H:i') }}@endif
        </div>
    </div>
    @if($user->puedeGestionar())
    <form action="{{ route('avisos.destroy',$aviso) }}" method="POST">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline btn-sm">✕ Desactivar</button>
    </form>
    @endif
</div>
@endforeach

{{-- Publicar aviso (solo gestores) --}}
@if($user->puedeGestionar())
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title">📢 Publicar aviso al equipo</div>
        <button onclick="document.getElementById('form-aviso').style.display=document.getElementById('form-aviso').style.display==='none'?'block':'none'" class="btn btn-outline btn-sm">+ Nuevo aviso</button>
    </div>
    <div id="form-aviso" style="display:none">
        <form action="{{ route('avisos.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group span-2">
                    <label class="form-label">Título <span class="required">*</span></label>
                    <input type="text" name="titulo" class="form-control" required placeholder="Ej: Falla en servidor principal">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Mensaje <span class="required">*</span></label>
                    <textarea name="mensaje" class="form-control" rows="2" required placeholder="Describe la situación con más detalle..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="info">ℹ️ Información</option>
                        <option value="advertencia">⚠️ Advertencia</option>
                        <option value="critico">🔴 Crítico</option>
                        <option value="mantenimiento">🔧 Mantenimiento</option>
                        <option value="resuelto">✅ Resuelto</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Expira en (opcional)</label>
                    <input type="datetime-local" name="expira_en" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:12px">Publicar aviso</button>
        </form>
    </div>
</div>
@endif

{{-- KPIs --}}
<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card green">
        <div class="stat-label">Resueltos hoy</div>
        <div class="stat-value">{{ $stats['hoy_resueltos'] }}</div>
        <div class="stat-sub">¡Gran trabajo!</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Esta semana</div>
        <div class="stat-value">{{ $stats['semana'] }}</div>
        <div class="stat-sub">Tickets recibidos</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-label">En atención</div>
        <div class="stat-value">{{ $stats['en_proceso'] }}</div>
        <div class="stat-sub">Tickets activos</div>
    </div>
    @if($stats['criticos'] > 0)
    <div class="stat-card red">
        <div class="stat-label">🔴 Críticos</div>
        <div class="stat-value">{{ $stats['criticos'] }}</div>
        <div class="stat-sub">Requieren atención urgente</div>
    </div>
    @endif
    <div class="stat-card amber">
        <div class="stat-label">Sin asignar</div>
        <div class="stat-value">{{ $sinAsignar }}</div>
        <div class="stat-sub">En cola</div>
    </div>
</div>

<div class="grid-2" style="align-items:start">

    {{-- CARGA POR TÉCNICO --}}
    <div class="card">
        <div class="card-title" style="margin-bottom:16px">👨‍💻 Carga del equipo TI</div>
        @forelse($tecnicos as $t)
        <div style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--border)">
            <div style="width:38px;height:38px;background:var(--blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0">
                {{ strtoupper(substr($t->nombre,0,1)) }}
            </div>
            <div style="flex:1">
                <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $t->nombre }}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    {{ $t->activos }} ticket(s) activo(s)
                    @if($t->criticos > 0)
                    · <span style="color:var(--red);font-weight:600">🔴 {{ $t->criticos }} crítico(s)</span>
                    @endif
                </div>
            </div>
            <div>
                @if($t->activos === 0)
                    <span class="badge badge-green">Disponible</span>
                @elseif($t->activos <= 3)
                    <span class="badge badge-blue">Normal</span>
                @elseif($t->activos <= 6)
                    <span class="badge badge-amber">Ocupado</span>
                @else
                    <span class="badge badge-red">Alta carga</span>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:20px"><p>No hay técnicos registrados</p></div>
        @endforelse
    </div>

    {{-- TICKETS EN PROGRESO --}}
    <div class="card" style="padding:0">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
            <div class="card-title">🔧 Lo que estamos atendiendo ahora</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:3px">Tickets en proceso en este momento</div>
        </div>
        @forelse($enProceso as $t)
        <div style="display:flex;align-items:center;gap:12px;padding:13px 18px;border-bottom:1px solid var(--border)">
            <span class="prio-dot prio-{{ $t->prioridad }}" style="width:10px;height:10px;flex-shrink:0"></span>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $t->titulo }}
                </div>
                <div style="font-size:11.5px;color:var(--text-muted);margin-top:3px;display:flex;gap:8px">
                    <span class="mono">{{ $t->numero }}</span>
                    <span>·</span>
                    <span>{{ $t->categoria->icono }} {{ $t->categoria->nombre }}</span>
                    <span>·</span>
                    <span>{{ $t->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:12px;font-weight:500;color:var(--text-muted)">
                    {{ $t->tecnico?->nombre ?? 'Sin asignar' }}
                </div>
                <span class="badge badge-{{ $t->estado_color }}" style="margin-top:3px">{{ $t->estado_label }}</span>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:32px">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>No hay tickets en proceso en este momento 🎉</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
