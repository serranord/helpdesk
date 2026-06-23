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
<div class="card" style="padding:0">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Depto.</th><th>Estado</th><th>Creado</th><th></th></tr>
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
                    <td><span class="badge {{ $u->estado==='activo' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($u->estado) }}</span></td>
                    <td class="text-muted mono">{{ $u->created_at->format('d/m/Y') }}</td>
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
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted" style="padding:32px">Sin usuarios registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px 20px">{{ $usuarios->links() }}</div>
</div>
@endsection
