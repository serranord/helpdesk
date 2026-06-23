@extends('layouts.app')
@section('title','Editar Usuario')
@section('page-title','Editar Usuario')
@section('content')
<div style="max-width:600px">
<div class="card">
    <div class="card-header"><div class="card-title">{{ $usuario->nombre }}</div></div>
    @if($errors->any())
    <div class="alert alert-error"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('usuarios.update',$usuario) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-grid">
            <div class="form-group span-2">
                <label class="form-label">Nombre completo <span class="required">*</span></label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre',$usuario->nombre) }}" required>
            </div>
            <div class="form-group span-2">
                <label class="form-label">Correo electrónico <span class="required">*</span></label>
                <input type="email" name="correo" class="form-control" value="{{ old('correo',$usuario->correo) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nueva contraseña <span class="form-hint">(dejar vacío para mantener)</span></label>
                <input type="password" name="password" class="form-control" minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Rol <span class="required">*</span></label>
                <select name="rol" class="form-control" required>
                    <option value="solicitante" @selected(old('rol',$usuario->rol)==='solicitante')>Solicitante</option>
                    <option value="tecnico"       @selected(old('rol',$usuario->rol)==='tecnico')>Técnico</option>
                    <option value="administrador" @selected(old('rol',$usuario->rol)==='administrador')>Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-control">
                    <option value="activo"   @selected(old('estado',$usuario->estado)==='activo')>Activo</option>
                    <option value="inactivo" @selected(old('estado',$usuario->estado)==='inactivo')>Inactivo</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Departamento</label>
                <input type="text" name="departamento" class="form-control" value="{{ old('departamento',$usuario->departamento) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="{{ old('telefono',$usuario->telefono) }}">
            </div>
        </div>
        <div class="flex gap-2" style="margin-top:20px">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
</div>
@endsection
