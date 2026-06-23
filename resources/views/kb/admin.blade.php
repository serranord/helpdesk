@extends('layouts.app')
@section('title','Gestión KB')
@section('page-title','Base de Conocimientos — Gestión')
@section('topbar-actions')
    <a href="{{ route('kb.index') }}" class="btn btn-outline btn-sm">Ver KB pública</a>
    <button onclick="document.getElementById('modal-nuevo').classList.add('open')" class="btn btn-primary btn-sm">+ Nuevo artículo</button>
@endsection
@section('content')
<div class="card" style="padding:0">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Título</th><th>Categoría</th><th>Estado</th><th>Vistas</th><th>Autor</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
                @forelse($articulos as $a)
                <tr>
                    <td><span class="fw-600">{{ $a->titulo }}</span>@if($a->destacado) <span style="font-size:10px;background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:10px;margin-left:4px">⭐ Destacado</span>@endif</td>
                    <td>{{ $a->categoria?->icono }} {{ $a->categoria?->nombre ?? '—' }}</td>
                    <td><span class="badge {{ $a->estado==='publicado' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($a->estado) }}</span></td>
                    <td>{{ $a->vistas }}</td>
                    <td style="font-size:12.5px">{{ $a->autor->nombre }}</td>
                    <td class="mono text-muted">{{ $a->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('kb.edit',$a) }}" class="btn btn-outline btn-sm">Editar</a>
                            <form action="{{ route('kb.destroy',$a) }}" method="POST" onsubmit="return confirm('¿Eliminar este artículo?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">✕</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted" style="padding:32px">Sin artículos aún</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px 20px">{{ $articulos->links() }}</div>
</div>

<div class="modal-overlay" id="modal-nuevo" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box" style="width:640px;max-height:90vh;overflow-y:auto">
        <div class="modal-title">Nuevo artículo</div>
        <form action="{{ route('kb.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom:14px">
                <label class="form-label">Título <span class="required">*</span></label>
                <input type="text" name="titulo" class="form-control" required placeholder="Título del artículo">
            </div>
            <div class="form-grid" style="margin-bottom:14px">
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $c)
                        <option value="{{ $c->id }}">{{ $c->icono }} {{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-control">
                        <option value="borrador">Borrador</option>
                        <option value="publicado">Publicado</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:14px">
                <label class="form-label">Contenido <span class="required">*</span></label>
                <textarea name="contenido" class="form-control" rows="10" required placeholder="Escribe el contenido del artículo..."></textarea>
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:16px;cursor:pointer">
                <input type="checkbox" name="destacado" value="1"> ⭐ Marcar como destacado
            </label>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Crear artículo</button>
                <button type="button" onclick="document.getElementById('modal-nuevo').classList.remove('open')" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>
</div>
@endsection
