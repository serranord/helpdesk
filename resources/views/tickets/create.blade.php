@extends('layouts.app')
@section('title','Nuevo Ticket')
@section('page-title','Nueva Solicitud')
@section('content')

<div style="max-width:700px">

{{-- Selector de plantillas (solo gestores) --}}
@if(auth()->user()->puedeGestionar() && $plantillas->count() > 0)
<div class="card" style="margin-bottom:16px;padding:16px 20px">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <span style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap">⚡ Usar plantilla:</span>
        @foreach($plantillas as $p)
        <button type="button" onclick="usarPlantilla({{ $p->id }})" class="btn btn-outline btn-sm">{{ $p->nombre }}</button>
        @endforeach
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Nueva Solicitud de Soporte</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:3px">Describe tu problema con el mayor detalle posible.</div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-error">
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('tickets.store') }}" method="POST">
        @csrf
        <div style="display:flex;flex-direction:column;gap:18px">

            <div class="form-group">
                <label class="form-label">¿Cuál es tu problema? <span class="required">*</span></label>
                <input type="text" name="titulo" id="f-titulo" class="form-control" value="{{ old('titulo') }}" required
                    placeholder="Ej: No puedo imprimir, Mi computadora no enciende...">
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de solicitud <span class="required">*</span></label>
                <select name="categoria_id" id="f-categoria" class="form-control" required>
                    <option value="">Seleccionar...</option>
                    @foreach($categorias as $c)
                    <option value="{{ $c->id }}" @selected(old('categoria_id')==$c->id)>{{ $c->icono }} {{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Descripción detallada <span class="required">*</span></label>
                <textarea name="descripcion" id="f-descripcion" class="form-control" rows="6" required
                    placeholder="Explica con detalle:&#10;- ¿Qué está pasando?&#10;- ¿Desde cuándo?&#10;- ¿Qué intentaste hacer?">{{ old('descripcion') }}</textarea>
            </div>

            @if(auth()->user()->puedeGestionar())
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:16px">
                <div class="section-title" style="margin-bottom:12px">⚙️ Opciones de gestión (solo TI)</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Solicitante</label>
                        <select name="solicitante_id" class="form-control">
                            <option value="">— Yo mismo —</option>
                            @foreach($solicitantes as $u)
                            <option value="{{ $u->id }}" @selected(old('solicitante_id')==$u->id)>{{ $u->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asignar a técnico</label>
                        <select name="tecnico_id" class="form-control">
                            <option value="">Sin asignar</option>
                            @foreach($tecnicos as $t)
                            <option value="{{ $t->id }}" @selected(old('tecnico_id')==$t->id)>{{ $t->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" id="f-prioridad" class="form-control">
                            <option value="baja">🟢 Baja</option>
                            <option value="media" selected>🔵 Media</option>
                            <option value="alta">🟡 Alta</option>
                            <option value="critica">🔴 Crítica</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="flex gap-2" style="margin-top:24px">
            <button type="submit" class="btn btn-primary">Enviar solicitud</button>
            <a href="{{ route('tickets.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
</div>

{{-- Datos de plantillas en JSON --}}
@if(auth()->user()->puedeGestionar())
<script>
const plantillasData = @json($plantillas->keyBy('id'));
function usarPlantilla(id) {
    const p = plantillasData[id];
    if (!p) return;
    document.getElementById('f-titulo').value = p.titulo;
    document.getElementById('f-descripcion').value = p.descripcion;
    document.getElementById('f-categoria').value = p.categoria_id;
    const prio = document.getElementById('f-prioridad');
    if (prio) prio.value = p.prioridad;
    document.getElementById('f-titulo').focus();
}
// Cargar plantilla desde URL si viene el parámetro
@if(request('plantilla'))
window.addEventListener('DOMContentLoaded', () => usarPlantilla({{ request('plantilla') }}));
@endif
</script>
@endif
@endsection
