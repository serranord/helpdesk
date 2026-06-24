@extends('layouts.app')
@section('title',$ticket->numero)
@section('page-title', $ticket->numero)
@section('topbar-actions')
    <a href="{{ route('tickets.index') }}" class="btn btn-outline btn-sm">← Volver</a>
@endsection
@section('content')

{{-- Alerta si es ticket reabierto --}}
@if($ticket->reabierto)
<div class="alert alert-warning">🔄 Este ticket fue reabierto por el solicitante porque el problema persistió.</div>
@endif

<div class="grid-2" style="align-items:start">

    {{-- COLUMNA PRINCIPAL --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Cabecera --}}
        <div class="card">
            <div class="ticket-header">
                <div style="flex:1;min-width:0">
                    <div class="ticket-numero">{{ $ticket->numero }} · Creado {{ $ticket->created_at->diffForHumans() }}</div>
                    <div class="ticket-titulo">{{ $ticket->titulo }}</div>
                    <div class="ticket-meta">
                        <span class="badge badge-{{ $ticket->estado_color }}">{{ $ticket->estado_label }}</span>
                        <span style="font-size:13px">{{ $ticket->categoria->icono }} {{ $ticket->categoria->nombre }}</span>
                        @if($ticket->estaVencido())<span class="vencido-badge">⚠️ SLA VENCIDO</span>@endif
                        @if($ticket->reabierto)<span style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">🔄 Reabierto</span>@endif
                    </div>
                </div>
            </div>

            {{-- Tiempo estimado visible para todos --}}
            @if($ticket->estimado_en && !in_array($ticket->estado,['resuelto','cerrado']))
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 14px;margin-top:12px;display:flex;align-items:center;gap:10px">
                <span style="font-size:18px">🕐</span>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#1e40af">Tiempo estimado de atención</div>
                    <div style="font-size:13px;color:#1e3a8a">{{ $ticket->estimado_en->format('d/m/Y H:i') }} ({{ $ticket->estimado_en->diffForHumans() }})</div>
                </div>
            </div>
            @endif

            <div class="section-title" style="margin-top:16px">Descripción del problema</div>
            <div style="font-size:14px;line-height:1.7;white-space:pre-wrap;background:var(--surface-2);padding:14px;border-radius:8px;border:1px solid var(--border)">{{ $ticket->descripcion }}</div>

            @if($ticket->nota_cierre)
            <div style="margin-top:16px">
                <div class="section-title">Nota de resolución</div>
                <div style="font-size:14px;line-height:1.7;background:var(--green-light);padding:14px;border-radius:8px;border:1px solid #86efac;color:#14532d">{{ $ticket->nota_cierre }}</div>
            </div>
            @endif
        </div>

        {{-- ADJUNTOS --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">📎 Archivos adjuntos ({{ $ticket->adjuntos->count() }})</div>
            </div>
            @if($ticket->adjuntos->count())
            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px">
                @foreach($ticket->adjuntos as $adj)
                <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px">
                    <span style="font-size:20px">{{ $adj->icono }}</span>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $adj->nombre_original }}</div>
                        <div style="font-size:11.5px;color:var(--text-muted)">{{ $adj->tamano_formateado }} · {{ $adj->usuario->nombre }} · {{ $adj->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <a href="{{ route('adjuntos.download',$adj) }}" class="btn btn-outline btn-sm">⬇ Descargar</a>
                    @if($user->puedeGestionar())
                    <form action="{{ route('adjuntos.destroy',$adj) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">✕</button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
            @if(!in_array($ticket->estado,['cerrado']))
            <form action="{{ route('adjuntos.store',$ticket) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group" style="margin-bottom:10px">
                    <label class="form-label">Adjuntar archivos</label>
                    <input type="file" name="archivos[]" multiple class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
                    <div class="form-hint">Máx. 5 archivos · 10MB cada uno</div>
                </div>
                <button type="submit" class="btn btn-outline btn-sm">📎 Subir archivos</button>
            </form>
            @endif
        </div>

        {{-- TIMELINE --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">📋 Seguimiento del caso</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Actualizaciones en tiempo real</div>
                </div>
            </div>
            <div class="timeline">
                <div class="timeline-item completado">
                    <div class="timeline-icon completado">✓</div>
                    <div class="timeline-body">
                        <div class="timeline-titulo">Solicitud recibida</div>
                        <div class="timeline-meta">{{ $ticket->created_at->format('d/m/Y H:i') }} · Ticket {{ $ticket->numero }} generado</div>
                    </div>
                </div>

                @if($ticket->tecnico)
                <div class="timeline-item completado">
                    <div class="timeline-icon completado">✓</div>
                    <div class="timeline-body">
                        <div class="timeline-titulo">Técnico asignado</div>
                        <div class="timeline-meta">Asignado a: <strong>{{ $ticket->tecnico->nombre }}</strong></div>
                    </div>
                </div>
                @else
                <div class="timeline-item pendiente">
                    <div class="timeline-icon pendiente">⏳</div>
                    <div class="timeline-body">
                        <div class="timeline-titulo" style="color:var(--text-muted)">Pendiente de asignación</div>
                        <div class="timeline-meta">El equipo de TI asignará un técnico pronto</div>
                    </div>
                </div>
                @endif

                @php $publicos = $ticket->comentarios->where('es_interno', false); @endphp
                @forelse($publicos as $i => $com)
                @php $esUltimo = $i === $publicos->keys()->last(); $estaFinal = in_array($ticket->estado,['resuelto','cerrado']); $clase = ($esUltimo && !$estaFinal) ? 'en_proceso' : 'completado'; @endphp
                <div class="timeline-item {{ $clase }}">
                    <div class="timeline-icon {{ $clase }}">{{ $clase === 'completado' ? '✓' : '🔄' }}</div>
                    <div class="timeline-body">
                        <div class="timeline-titulo">{{ $com->contenido }}</div>
                        <div class="timeline-meta">{{ $com->created_at->format('d/m/Y H:i') }} · {{ $com->usuario->nombre }}</div>
                    </div>
                </div>
                @empty
                    @if($ticket->tecnico && !in_array($ticket->estado,['resuelto','cerrado']))
                    <div class="timeline-item pendiente">
                        <div class="timeline-icon pendiente">⏳</div>
                        <div class="timeline-body">
                            <div class="timeline-titulo" style="color:var(--text-muted)">El técnico comenzará a trabajar pronto</div>
                        </div>
                    </div>
                    @endif
                @endforelse

                @if(in_array($ticket->estado,['resuelto','cerrado']))
                <div class="timeline-item completado">
                    <div class="timeline-icon completado" style="background:var(--green)">✓</div>
                    <div class="timeline-body">
                        <div class="timeline-titulo" style="color:var(--green);font-weight:700">✅ Caso {{ $ticket->estado === 'cerrado' ? 'cerrado' : 'resuelto' }}</div>
                        <div class="timeline-meta">{{ $ticket->fecha_resolucion?->format('d/m/Y H:i') }}</div>
                        @if($ticket->nota_cierre)<div class="timeline-nota">{{ $ticket->nota_cierre }}</div>@endif
                    </div>
                </div>
                @endif
            </div>

            @if($user->puedeGestionar() && !in_array($ticket->estado,['cerrado']))
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
                <div class="section-title">Agregar actualización</div>
                <form action="{{ route('tickets.comentar',$ticket) }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom:10px">
                        <textarea name="contenido" class="form-control" rows="2" required placeholder="Ej: Revisando el equipo, el problema es..."></textarea>
                        <div class="form-hint">Visible para el solicitante en su línea de tiempo.</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Publicar actualización</button>
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);cursor:pointer">
                            <input type="checkbox" name="es_interno" value="1"> 🔒 Solo equipo TI
                        </label>
                    </div>
                </form>
            </div>
            @endif

            @if($user->esSolicitante() && !in_array($ticket->estado,['cerrado','resuelto']))
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
                <div class="section-title">¿Tienes información adicional?</div>
                <form action="{{ route('tickets.comentar',$ticket) }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom:10px">
                        <textarea name="contenido" class="form-control" rows="2" required placeholder="Agrega cualquier detalle que pueda ayudar..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline btn-sm">Enviar información</button>
                </form>
            </div>
            @endif
        </div>

        {{-- TICKETS VINCULADOS --}}
        @if($user->puedeGestionar() || $ticket->hijosVinculados->count() > 0)
        <div class="card">
            <div class="card-header">
                <div class="card-title">🔗 Tickets vinculados ({{ $ticket->hijosVinculados->count() }})</div>
            </div>
            @if($ticket->hijosVinculados->count())
            <div style="margin-bottom:16px">
                @foreach($ticket->hijosVinculados as $vinculo)
                <div style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--border);border-radius:8px;margin-bottom:8px">
                    <span class="prio-dot prio-{{ $vinculo->hijo->prioridad }}"></span>
                    <div style="flex:1">
                        <a href="{{ route('tickets.show',$vinculo->hijo) }}" class="link" style="font-size:13px;font-weight:600">{{ $vinculo->hijo->numero }} — {{ $vinculo->hijo->titulo }}</a>
                        <div style="font-size:11.5px;color:var(--text-muted)">{{ $vinculo->hijo->estado_label }}</div>
                    </div>
                    @if($user->puedeGestionar())
                    <form action="{{ route('tickets.desvincular',$ticket->id) }}/{{ $vinculo->id }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm">✕</button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
            @if($user->puedeGestionar() && !in_array($ticket->estado,['cerrado']))
            <form action="{{ route('tickets.vincular',$ticket) }}" method="POST" style="display:flex;gap:8px">
                @csrf
                <select name="ticket_hijo_id" class="form-control" required>
                    <option value="">Seleccionar ticket a vincular...</option>
                    @foreach($ticketsVinculables as $tv)
                    <option value="{{ $tv->id }}">{{ $tv->numero }} — {{ Str::limit($tv->titulo,50) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-sm" style="white-space:nowrap">🔗 Vincular</button>
            </form>
            @endif
        </div>
        @endif
    </div>

    {{-- COLUMNA LATERAL --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Estado visual --}}
        <div class="card" style="text-align:center;padding:24px">
            @php
                $estadoInfo = match($ticket->estado) {
                    'nuevo'      => ['emoji'=>'📬','texto'=>'Solicitud Nueva',  'color'=>'var(--cyan)',   'sub'=>'En cola, esperando revisión'],
                    'abierto'    => ['emoji'=>'📂','texto'=>'Abierto',          'color'=>'var(--blue)',   'sub'=>'Revisada, pendiente de asignación'],
                    'asignado'   => ['emoji'=>'👨‍💻','texto'=>'Asignado',         'color'=>'var(--purple)', 'sub'=>'Un técnico tomó tu caso'],
                    'en_proceso' => ['emoji'=>'🔧','texto'=>'En Proceso',       'color'=>'var(--orange)', 'sub'=>'El técnico está trabajando'],
                    'pendiente'  => ['emoji'=>'⏸️','texto'=>'En Espera',        'color'=>'var(--amber)',  'sub'=>'Esperando información o recursos'],
                    'resuelto'   => ['emoji'=>'✅','texto'=>'Resuelto',         'color'=>'var(--green)',  'sub'=>'Tu caso ha sido resuelto'],
                    'cerrado'    => ['emoji'=>'🔒','texto'=>'Cerrado',          'color'=>'var(--text-muted)','sub'=>'Ticket finalizado'],
                    default      => ['emoji'=>'📋','texto'=>$ticket->estado_label,'color'=>'var(--text-muted)','sub'=>''],
                };
            @endphp
            <div style="font-size:44px;margin-bottom:10px">{{ $estadoInfo['emoji'] }}</div>
            <div style="font-size:17px;font-weight:700;color:{{ $estadoInfo['color'] }}">{{ $estadoInfo['texto'] }}</div>
            <div style="font-size:12.5px;color:var(--text-muted);margin-top:5px">{{ $estadoInfo['sub'] }}</div>
        </div>

        {{-- Info del ticket --}}
        <div class="card">
            <div class="section-title">Información</div>
            <div style="display:flex;flex-direction:column;gap:12px">
                <div class="detail-item"><label>Número</label><p class="mono">{{ $ticket->numero }}</p></div>
                <div class="detail-item"><label>Categoría</label><p>{{ $ticket->categoria->icono }} {{ $ticket->categoria->nombre }}</p></div>
                <div class="detail-item"><label>Solicitante</label><p>{{ $ticket->solicitante->nombre }}</p></div>
                @if($ticket->solicitante->departamento)
                <div class="detail-item"><label>Departamento</label><p>{{ $ticket->solicitante->departamento }}</p></div>
                @endif
                <div class="detail-item"><label>Técnico asignado</label><p>{{ $ticket->tecnico?->nombre ?? '⏳ Pendiente' }}</p></div>
                <div class="detail-item"><label>Fecha de apertura</label><p>{{ $ticket->created_at->format('d/m/Y H:i') }}</p></div>
                @if($ticket->estimado_en && !in_array($ticket->estado,['resuelto','cerrado']))
                <div class="detail-item">
                    <label>Atención estimada</label>
                    <p style="color:var(--blue);font-weight:600">🕐 {{ $ticket->estimado_en->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                @if($ticket->fecha_resolucion)
                <div class="detail-item"><label>Resuelto el</label><p>{{ $ticket->fecha_resolucion->format('d/m/Y H:i') }}</p></div>
                @endif
                @if($ticket->fecha_limite)
                <div class="detail-item">
                    <label>Límite SLA</label>
                    <p style="{{ $ticket->estaVencido() ? 'color:var(--red);font-weight:600' : '' }}">
                        {{ $ticket->fecha_limite->format('d/m/Y H:i') }} @if($ticket->estaVencido()) ⚠️@endif
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- Botón reabrir (solicitante) --}}
        @if($user->esSolicitante() && in_array($ticket->estado,['resuelto','cerrado']) && !$ticket->calificacion)
        <div class="card">
            <div class="section-title">¿El problema persiste?</div>
            <form action="{{ route('tickets.reabrir',$ticket) }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom:10px">
                    <textarea name="motivo" class="form-control" rows="2" placeholder="Explica brevemente por qué el problema persiste..."></textarea>
                </div>
                <button type="submit" class="btn btn-outline btn-sm" style="width:100%" onclick="return confirm('¿Confirmas que el problema persiste y deseas reabrir este ticket?')">
                    🔄 Reabrir ticket
                </button>
            </form>
        </div>
        @endif

        {{-- Panel técnico --}}
        @if($user->puedeGestionar())
        <div class="card">
            <div class="section-title">Cambiar Estado</div>
            <form action="{{ route('tickets.estado',$ticket) }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom:10px">
                    <select name="estado" class="form-control" id="select-estado">
                        @foreach($estados as $key => $label)
                        <option value="{{ $key }}" @selected($ticket->estado===$key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:10px" id="wrap-nota">
                    <textarea name="nota_cierre" class="form-control" rows="2" placeholder="Nota de resolución (opcional)...">{{ $ticket->nota_cierre }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="width:100%">Actualizar estado</button>
            </form>
        </div>

        <div class="card">
            <div class="section-title">Asignar Técnico</div>
            <form action="{{ route('tickets.asignar',$ticket) }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom:10px">
                    <select name="tecnico_id" class="form-control">
                        <option value="">Sin asignar</option>
                        @foreach($tecnicos as $t)
                        <option value="{{ $t->id }}" @selected($ticket->tecnico_id===$t->id)>{{ $t->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-outline btn-sm" style="width:100%">Asignar</button>
            </form>
        </div>

        <div class="card">
            <div class="section-title">Prioridad</div>
            <form action="{{ route('tickets.prioridad',$ticket) }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom:10px">
                    <select name="prioridad" class="form-control">
                        <option value="baja"    @selected($ticket->prioridad==='baja')>🟢 Baja</option>
                        <option value="media"   @selected($ticket->prioridad==='media')>🔵 Media</option>
                        <option value="alta"    @selected($ticket->prioridad==='alta')>🟡 Alta</option>
                        <option value="critica" @selected($ticket->prioridad==='critica')>🔴 Crítica</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-outline btn-sm" style="width:100%">Actualizar</button>
            </form>
        </div>

        <div class="card">
            <div class="section-title">🕐 Tiempo estimado de atención</div>
            <form action="{{ route('tickets.estimar',$ticket) }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom:10px">
                    <input type="datetime-local" name="estimado_en" class="form-control"
                        value="{{ $ticket->estimado_en?->format('Y-m-d\TH:i') }}">
                    <div class="form-hint">El solicitante verá esta fecha y recibirá una notificación.</div>
                </div>
                <button type="submit" class="btn btn-outline btn-sm" style="width:100%">Establecer estimación</button>
            </form>
        </div>

        {{-- Historial --}}
        <div class="card" style="padding:0">
            <div style="padding:14px 18px;border-bottom:1px solid var(--border)"><div class="section-title" style="margin-bottom:0">📋 Historial de cambios</div></div>
            <div style="max-height:280px;overflow-y:auto">
                @forelse($ticket->historial as $h)
                <div style="display:flex;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border);font-size:12.5px">
                    <span style="font-size:16px;flex-shrink:0">{{ $h->icono }}</span>
                    <div>
                        <div style="color:var(--text);font-weight:500">{{ $h->descripcion }}</div>
                        <div style="color:var(--text-muted);margin-top:2px">{{ $h->usuario?->nombre ?? 'Sistema' }} · {{ $h->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @empty
                <div style="padding:16px;text-align:center;color:var(--text-muted);font-size:12.5px">Sin historial</div>
                @endforelse
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal calificación --}}
@if($user->esSolicitante() && $ticket->estado === 'resuelto' && !$ticket->calificacion && $ticket->solicitante_id === $user->id)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;display:flex;align-items:center;justify-content:center" id="modal-cal">
    <div style="background:#fff;border-radius:14px;padding:32px;width:460px;max-width:94vw;text-align:center">
        <div style="font-size:40px;margin-bottom:12px">🎉</div>
        <div style="font-size:18px;font-weight:700;margin-bottom:6px">¡Tu solicitud fue resuelta!</div>
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:24px">¿Cómo calificarías la atención?</div>
        <form action="{{ route('tickets.calificar',$ticket) }}" method="POST">
            @csrf
            <div style="display:flex;justify-content:center;gap:8px;margin-bottom:16px">
                @for($i=1;$i<=5;$i++)
                <label style="cursor:pointer;font-size:36px;opacity:.3;transition:opacity .15s" id="star-{{ $i }}">
                    <input type="radio" name="estrellas" value="{{ $i }}" style="display:none" required onchange="marcarEstrellas({{ $i }})">⭐
                </label>
                @endfor
            </div>
            <div class="form-group" style="text-align:left;margin-bottom:16px">
                <textarea name="comentario" class="form-control" rows="2" placeholder="Comentario opcional..."></textarea>
            </div>
            <div class="flex gap-2" style="justify-content:center">
                <button type="submit" class="btn btn-primary">Enviar calificación</button>
                <button type="button" onclick="document.getElementById('modal-cal').style.display='none'" class="btn btn-outline">Ahora no</button>
            </div>
        </form>
    </div>
</div>
<script>
function marcarEstrellas(n){for(let i=1;i<=5;i++)document.getElementById('star-'+i).style.opacity=i<=n?'1':'.3';}
</script>
@endif

@if($ticket->calificacion)
<div style="margin-top:16px" class="card">
    <div class="section-title">Calificación del usuario</div>
    <div style="display:flex;align-items:center;gap:12px">
        <div style="font-size:28px">{{ str_repeat('⭐',$ticket->calificacion->estrellas) }}</div>
        <div>
            <div style="font-size:14px;font-weight:600">{{ $ticket->calificacion->estrellas }}/5</div>
            @if($ticket->calificacion->comentario)<div style="font-size:13px;color:var(--text-muted)">"{{ $ticket->calificacion->comentario }}"</div>@endif
        </div>
    </div>
</div>
@endif

<script>
const sel = document.getElementById('select-estado');
const wrap = document.getElementById('wrap-nota');
function toggleNota(){if(wrap)wrap.style.display=['resuelto','cerrado'].includes(sel?.value)?'':'none';}
sel?.addEventListener('change',toggleNota);
toggleNota();
</script>
@endsection
