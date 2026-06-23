@extends('layouts.app')
@section('title','Editar Artículo')
@section('page-title','Editar Artículo')
@section('content')
<div style="max-width:800px">
<div class="card">
    <div class="card-header"><div class="card-title">{{ $kbArticulo->titulo }}</div></div>
    <form action="{{ route('kb.update',$kbArticulo) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-group" style="margin-bottom:14px">
            <label class="form-label">Título <span class="required">*</span></label>
            <input type="text" name="titulo" class="form-control" value="{{ old('titulo',$kbArticulo->titulo) }}" required>
        </div>
        <div class="form-grid" style="margin-bottom:14px">
            <div class="form-group">
                <label class="form-label">Categoría</label>
                <select name="categoria_id" class="form-control">
                    <option value="">Sin categoría</option>
                    @foreach($categorias as $c)
                    <option value="{{ $c->id }}" @selected($kbArticulo->categoria_id==$c->id)>{{ $c->icono }} {{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-control">
                    <option value="borrador" @selected($kbArticulo->estado==='borrador')>Borrador</option>
                    <option value="publicado" @selected($kbArticulo->estado==='publicado')>Publicado</option>
                </select>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:14px">
            <label class="form-label">Contenido <span class="required">*</span></label>
            <textarea name="contenido" class="form-control" rows="14" required>{{ old('contenido',$kbArticulo->contenido) }}</textarea>
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:16px;cursor:pointer">
            <input type="checkbox" name="destacado" value="1" @checked($kbArticulo->destacado)> ⭐ Marcar como destacado
        </label>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('kb.admin') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
</div>
@endsection
