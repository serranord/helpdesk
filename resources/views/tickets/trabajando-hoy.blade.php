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
        <div id="tickets-trabajando-hoy" class="tickets-ordenables" style="max-height:70vh;overflow-y:auto">
            @forelse($seleccionados as $t)
            <div @class(['ticket-ordenable', 'ticket-diferido' => $t->estado === 'diferido']) data-ticket-id="{{ $t->id }}" draggable="true" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 18px;border-bottom:1px solid var(--border)">
                <span class="ticket-arrastre" title="Arrastra para cambiar el orden" aria-hidden="true">&#8285;</span>
                <div style="min-width:0;flex:1">
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
            <form method="GET" action="{{ route('tickets.trabajando-hoy') }}" style="margin-top:12px;display:flex;gap:8px;align-items:end;flex-wrap:wrap">
                <div style="flex:1;min-width:200px">
                    <label for="estado-disponibles" class="form-label">Estado</label>
                    <select id="estado-disponibles" name="estado" class="form-control">
                        <option value="" @selected(!$estado)>Todos los estados</option>
                        <option value="__abiertos__" @selected($estado === '__abiertos__')>Todos (excepto resueltos/cerrados)</option>
                        @foreach($estados as $key => $label)
                        <option value="{{ $key }}" @selected($estado === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                @if(request()->has('estado'))
                <a href="{{ route('tickets.trabajando-hoy') }}" class="btn btn-outline btn-sm">Limpiar</a>
                @endif
            </form>
        </div>
        <div style="max-height:70vh;overflow-y:auto">
            @forelse($disponibles as $t)
            <div @class(['ticket-diferido' => $t->estado === 'diferido']) style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 18px;border-bottom:1px solid var(--border)">
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
                    @if($t->estado === 'cerrado')
                    <span style="font-size:12px;color:var(--text-muted)">Cerrado</span>
                    @else
                    <button type="submit" class="btn btn-primary btn-sm">+ Agregar</button>
                    @endif
                </form>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">No hay tickets disponibles para el estado seleccionado.</div>
            @endforelse
        </div>
    </div>
</div>

@if($seleccionados->isNotEmpty())
<style>
    .ticket-ordenable{cursor:grab;transition:background .15s,opacity .15s}
    .ticket-ordenable:hover{background:var(--surface-2)}
    .ticket-ordenable.arrastrando{opacity:.45;cursor:grabbing}
    .ticket-ordenable.destino-arrastre{box-shadow:inset 0 3px 0 var(--brand-blue)}
    .ticket-arrastre{color:var(--text-muted);font-size:20px;line-height:1;cursor:grab;user-select:none}
</style>
<script>
(() => {
    const lista = document.getElementById('tickets-trabajando-hoy');
    const csrf = '{{ csrf_token() }}';
    const url = '{{ route('tickets.trabajando-hoy.orden') }}';
    let arrastrado = null;

    lista.addEventListener('dragstart', (event) => {
        const tarjeta = event.target.closest('.ticket-ordenable');
        if (!tarjeta) return;
        arrastrado = tarjeta;
        tarjeta.classList.add('arrastrando');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', tarjeta.dataset.ticketId);
    });

    lista.addEventListener('dragover', (event) => {
        event.preventDefault();
        const destino = event.target.closest('.ticket-ordenable');
        if (!arrastrado || !destino || destino === arrastrado) return;
        document.querySelectorAll('.destino-arrastre').forEach(el => el.classList.remove('destino-arrastre'));
        destino.classList.add('destino-arrastre');
        const despues = event.clientY > destino.getBoundingClientRect().top + destino.offsetHeight / 2;
        lista.insertBefore(arrastrado, despues ? destino.nextSibling : destino);
    });

    lista.addEventListener('dragend', async () => {
        if (!arrastrado) return;
        arrastrado.classList.remove('arrastrando');
        document.querySelectorAll('.destino-arrastre').forEach(el => el.classList.remove('destino-arrastre'));
        arrastrado = null;

        const tickets = [...lista.querySelectorAll('.ticket-ordenable')].map(el => Number(el.dataset.ticketId));
        try {
            const respuesta = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                body: JSON.stringify({tickets})
            });
            if (!respuesta.ok) throw new Error();
        } catch (error) {
            window.location.reload();
        }
    });
})();
</script>
@endif
@endsection
