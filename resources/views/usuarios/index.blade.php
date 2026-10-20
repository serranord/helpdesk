@extends('layouts.app')
@section('title','Usuarios')
@section('page-title','Gestión de Usuarios')
@section('topbar-actions')
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Nuevo Usuario
    </a>
@endsection
@section('content')
<nav aria-label="Listado de usuarios" class="flex gap-2" style="margin-bottom:16px;flex-wrap:wrap">
    <a href="{{ route('usuarios.index') }}" class="btn {{ $verEliminados ? 'btn-outline' : 'btn-primary' }}" @if(!$verEliminados) aria-current="page" @endif>Usuarios actuales</a>
    <a href="{{ route('usuarios.index', ['eliminados' => 1]) }}" class="btn {{ $verEliminados ? 'btn-primary' : 'btn-outline' }}" @if($verEliminados) aria-current="page" @endif>Cuentas eliminadas</a>
</nav>
@if($verEliminados)
<p class="text-muted" style="margin-bottom:16px">Estas cuentas fueron eliminadas y conservan su correo reservado. No pueden iniciar sesión.</p>
@endif
<div class="card" style="padding:0">
    <div class="card-header" style="padding:16px 20px;margin-bottom:0">
        <div class="card-title">{{ $verEliminados ? 'Cuentas eliminadas' : 'Usuarios actuales' }} ({{ $usuarios->total() }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Depto.</th><th>Estado</th><th>Creado</th><th>{{ $verEliminados ? 'Eliminado el' : 'Acciones' }}</th></tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                <tr>
                    <td><span class="fw-600">{{ $u->nombre }}</span></td>
                    <td class="mono">{{ $u->correo }}</td>
                    <td>
                        @php $rc = ['administrador'=>'red','tecnico'=>'blue','solicitante'=>'gray'][$u->rol] @endphp
                        <span class="badge badge-{{ $rc }}">{{ $u->rol_label }}</span>
                    </td>
                    <td>{{ $u->departamento ?? '—' }}</td>
                    <td><span class="badge {{ !$verEliminados && $u->estado==='activo' ? 'badge-green' : 'badge-gray' }}">{{ $verEliminados ? 'Eliminado' : ucfirst($u->estado) }}</span></td>
                    <td class="text-muted mono">{{ $u->created_at->format('d/m/Y') }}</td>
                    @if($verEliminados)
                    <td class="text-muted mono">{{ $u->deleted_at->format('d/m/Y H:i') }}</td>
                    @else
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('usuarios.edit',$u) }}" class="btn btn-outline btn-sm">Editar</a>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('usuarios.destroy',$u) }}" method="POST" onsubmit="return confirm('¿Eliminar este usuario?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                            @endif
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted" style="padding:32px">{{ $verEliminados ? 'No hay cuentas eliminadas.' : 'Sin usuarios registrados' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px 20px">{{ $usuarios->links() }}</div>
</div>
@endsection
