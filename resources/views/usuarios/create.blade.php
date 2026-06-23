@extends('layouts.app')
@section('title','Nuevo Usuario')
@section('page-title','Nuevo Usuario')
@section('content')
<div style="max-width:600px">
<div class="card">
    <div class="card-header"><div class="card-title">Crear Usuario</div></div>
    @if($errors->any())
    <div class="alert alert-error"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group span-2">
                <label class="form-label">Nombre completo <span class="required">*</span></label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
            </div>
            <div class="form-group span-2">
                <label class="form-label">Correo electrónico <span class="required">*</span></label>
                <input type="email" name="correo" class="form-control" value="{{ old('correo') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña <span class="required">*</span></label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label">Confirmar contraseña <span class="required">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Rol <span class="required">*</span></label>
                <select name="rol" class="form-control" required>
                    <option value="solicitante" @selected(old('rol','solicitante')==='solicitante')>Solicitante</option>
                    <option value="tecnico"       @selected(old('rol')==='tecnico')>Técnico</option>
                    <option value="administrador" @selected(old('rol')==='administrador')>Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Departamento</label>
                <input type="text" name="departamento" class="form-control" value="{{ old('departamento') }}" placeholder="Ej: Contabilidad">
            </div>
            <div class="form-group span-2">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="809-000-0000">
            </div>
        </div>
        <div class="flex gap-2" style="margin-top:20px">
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
</div>
@endsection
