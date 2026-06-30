@extends('layouts.app')
@section('title','Categorías')
@section('page-title','Categorías de Tickets')
@section('topbar-actions')
    <button onclick="document.getElementById('modal-nueva').classList.add('open')" class="btn btn-primary btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Nueva Categoría
    </button>
@endsection
@section('content')

<div class="card" style="padding:0">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Categoría</th><th>Descripción</th><th>SLA</th><th>Tickets</th><th>Visible usuario</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse($categorias as $c)
                <tr>
                    <td><span style="font-size:18px;margin-right:8px">{{ $c->icono }}</span><span class="fw-600">{{ $c->nombre }}</span></td>
                    <td class="text-muted">{{ $c->descripcion ?? '—' }}</td>
                    <td><span class="mono">{{ $c->sla_horas }}h</span></td>
                    <td>{{ $c->tickets_count }}</td>
                    <td>
                        <form action="{{ route('categorias.update',$c) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="nombre" value="{{ $c->nombre }}">
                            <input type="hidden" name="descripcion" value="{{ $c->descripcion }}">
                            <input type="hidden" name="icono" value="{{ $c->icono }}">
                            <input type="hidden" name="sla_horas" value="{{ $c->sla_horas }}">
                            <input type="hidden" name="activa" value="{{ $c->activa ? 1 : 0 }}">
                            <input type="hidden" name="visible_usuario" value="{{ $c->visible_usuario ? 0 : 1 }}">
                            <button type="submit" class="badge {{ $c->visible_usuario ? 'badge-green' : 'badge-gray' }}" style="border:none;cursor:pointer">
                                {{ $c->visible_usuario ? '✅ Visible' : '🔒 Solo TI' }}
                            </button>
                        </form>
                    </td>
                    <td><span class="badge {{ $c->activa ? 'badge-green' : 'badge-gray' }}">{{ $c->activa ? 'Activa' : 'Inactiva' }}</span></td>
                    <td>
                        <button onclick="abrirEditar({{ $c->id }},'{{ addslashes($c->nombre) }}','{{ addslashes($c->descripcion) }}','{{ $c->icono }}',{{ $c->sla_horas }},{{ $c->activa ? 1 : 0 }})" class="btn btn-outline btn-sm">Editar</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted" style="padding:32px">Sin categorías</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Nueva --}}
<div class="modal-overlay" id="modal-nueva" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
        <div class="modal-title">Nueva Categoría</div>
        <div class="modal-sub">Define una nueva categoría para clasificar tickets</div>
        <form action="{{ route('categorias.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ícono (emoji)</label>
                    <input type="text" name="icono" class="form-control" value="🖥️" maxlength="4">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" class="form-control" placeholder="Breve descripción de esta categoría">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">SLA (horas máx. para resolver) <span class="required">*</span></label>
                    <input type="number" name="sla_horas" class="form-control" value="24" min="1" required>
                </div>
            </div>
            <div class="flex gap-2" style="margin-top:16px">
                <button type="submit" class="btn btn-primary">Crear</button>
                <button type="button" onclick="document.getElementById('modal-nueva').classList.remove('open')" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Editar --}}
<div class="modal-overlay" id="modal-editar" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
        <div class="modal-title">Editar Categoría</div>
        <form id="form-editar" method="POST">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ícono</label>
                    <input type="text" name="icono" id="edit-icono" class="form-control" maxlength="4">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" id="edit-desc" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">SLA (horas) <span class="required">*</span></label>
                    <input type="number" name="sla_horas" id="edit-sla" class="form-control" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="activa" id="edit-activa" class="form-control">
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
function abrirEditar(id, nombre, desc, icono, sla, activa) {
    const base = '{{ url("/categorias") }}/';
    document.getElementById('form-editar').action = base + id;
    document.getElementById('edit-nombre').value = nombre;
    document.getElementById('edit-desc').value = desc;
    document.getElementById('edit-icono').value = icono;
    document.getElementById('edit-sla').value = sla;
    document.getElementById('edit-activa').value = activa;
    document.getElementById('modal-editar').classList.add('open');
}
</script>
@endsection
