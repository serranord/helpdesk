@extends('layouts.app')
@section('title','Plantillas')
@section('page-title','Plantillas de Tickets')
@section('topbar-actions')
    <button onclick="document.getElementById('modal-nueva').classList.add('open')" class="btn btn-primary btn-sm">+ Nueva Plantilla</button>
@endsection
@section('content')

<div class="card" style="padding:0">
    @if($plantillas->isEmpty())
    <div class="empty-state"><p>No hay plantillas. Crea una para agilizar la creación de tickets frecuentes.</p></div>
    @else
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nombre</th><th>Título del ticket</th><th>Categoría</th><th>Prioridad</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @foreach($plantillas as $p)
                <tr>
                    <td><span class="fw-600">{{ $p->nombre }}</span></td>
                    <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->titulo }}</td>
                    <td>{{ $p->categoria->icono }} {{ $p->categoria->nombre }}</td>
                    <td><span style="display:inline-flex;align-items:center;gap:5px"><span class="prio-dot prio-{{ $p->prioridad }}"></span>{{ ucfirst($p->prioridad) }}</span></td>
                    <td><span class="badge {{ $p->activa ? 'badge-green' : 'badge-gray' }}">{{ $p->activa ? 'Activa' : 'Inactiva' }}</span></td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('tickets.create') }}?plantilla={{ $p->id }}" class="btn btn-primary btn-sm">Usar</a>
                            <button onclick="editarPlantilla({{ $p->id }},'{{ addslashes($p->nombre) }}','{{ addslashes($p->titulo) }}','{{ addslashes($p->descripcion) }}',{{ $p->categoria_id }},'{{ $p->prioridad }}',{{ $p->activa ? 1:0 }})" class="btn btn-outline btn-sm">Editar</button>
                            <form action="{{ route('plantillas.destroy',$p) }}" method="POST" onsubmit="return confirm('¿Eliminar esta plantilla?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">✕</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Modal Nueva --}}
<div class="modal-overlay" id="modal-nueva" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:540px">
        <div class="modal-title">Nueva Plantilla</div>
        <div class="modal-sub">Guarda un ticket frecuente para crearlo con un clic</div>
        <form action="{{ route('plantillas.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group span-2">
                    <label class="form-label">Nombre de la plantilla <span class="required">*</span></label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Configuración correo nuevo usuario">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Título del ticket <span class="required">*</span></label>
                    <input type="text" name="titulo" class="form-control" required placeholder="Título que tendrá el ticket al crearse">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría <span class="required">*</span></label>
                    <select name="categoria_id" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        @foreach($categorias as $c)
                        <option value="{{ $c->id }}">{{ $c->icono }} {{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridad</label>
                    <select name="prioridad" class="form-control">
                        <option value="baja">🟢 Baja</option>
                        <option value="media" selected>🔵 Media</option>
                        <option value="alta">🟡 Alta</option>
                        <option value="critica">🔴 Crítica</option>
                    </select>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Descripción predeterminada <span class="required">*</span></label>
                    <textarea name="descripcion" class="form-control" rows="4" required placeholder="Descripción que se llenará automáticamente..."></textarea>
                </div>
            </div>
            <div class="flex gap-2" style="margin-top:16px">
                <button type="submit" class="btn btn-primary">Crear plantilla</button>
                <button type="button" onclick="document.getElementById('modal-nueva').classList.remove('open')" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Editar --}}
<div class="modal-overlay" id="modal-editar" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:540px">
        <div class="modal-title">Editar Plantilla</div>
        <form id="form-editar" method="POST">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group span-2">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="nombre" id="e-nombre" class="form-control" required>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Título del ticket <span class="required">*</span></label>
                    <input type="text" name="titulo" id="e-titulo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" id="e-cat" class="form-control">
                        @foreach($categorias as $c)
                        <option value="{{ $c->id }}">{{ $c->icono }} {{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridad</label>
                    <select name="prioridad" id="e-prio" class="form-control">
                        <option value="baja">🟢 Baja</option>
                        <option value="media">🔵 Media</option>
                        <option value="alta">🟡 Alta</option>
                        <option value="critica">🔴 Crítica</option>
                    </select>
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="e-desc" class="form-control" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="activa" id="e-activa" class="form-control">
                        <option value="1">Activa</option>
                        <option value="0">Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2" style="margin-top:16px">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <button type="button" onclick="document.getElementById('modal-editar').classList.remove('open')" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarPlantilla(id, nombre, titulo, desc, catId, prio, activa) {
    document.getElementById('form-editar').action = '{{ url("/plantillas") }}/' + id;
    document.getElementById('e-nombre').value = nombre;
    document.getElementById('e-titulo').value = titulo;
    document.getElementById('e-desc').value = desc;
    document.getElementById('e-cat').value = catId;
    document.getElementById('e-prio').value = prio;
    document.getElementById('e-activa').value = activa;
    document.getElementById('modal-editar').classList.add('open');
}
</script>
@endsection
