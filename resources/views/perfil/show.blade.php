@extends('layouts.app')
@section('title','Mi Perfil')
@section('page-title','Mi Perfil')
@section('content')

<div style="max-width:680px;display:flex;flex-direction:column;gap:20px">

    {{-- Info personal --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Información personal</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Actualiza tu nombre, teléfono y departamento</div>
            </div>
            <div style="width:52px;height:52px;background:var(--blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff;flex-shrink:0">
                {{ strtoupper(substr($user->nombre,0,1)) }}
            </div>
        </div>

        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:14px 16px;margin-bottom:20px;display:flex;gap:16px;flex-wrap:wrap">
            <div><span style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px">Correo</span><div style="font-size:14px;font-weight:500;margin-top:2px">{{ $user->correo }}</div></div>
            <div><span style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px">Rol</span><div style="margin-top:4px"><span class="badge badge-blue">{{ $user->rol_label }}</span></div></div>
            <div><span style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px">Miembro desde</span><div style="font-size:14px;font-weight:500;margin-top:2px">{{ $user->created_at->format('d/m/Y') }}</div></div>
        </div>

        @if($errors->hasAny(['nombre','telefono','departamento']))
        <div class="alert alert-error"><ul>@foreach($errors->only(['nombre','telefono','departamento']) as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('perfil.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group span-2">
                    <label class="form-label">Nombre completo <span class="required">*</span></label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre',$user->nombre) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono',$user->telefono) }}" placeholder="809-000-0000">
                </div>
                <div class="form-group">
                    <label class="form-label">Departamento</label>
                    <input type="text" name="departamento" class="form-control" value="{{ old('departamento',$user->departamento) }}" placeholder="Ej: Contabilidad">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:8px">Guardar cambios</button>
        </form>
    </div>

    {{-- Cambiar contraseña --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Cambiar contraseña</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Usa una contraseña segura de al menos 6 caracteres</div>
            </div>
        </div>

        @if($errors->hasAny(['password_actual','password']))
        <div class="alert alert-error"><ul>@foreach($errors->only(['password_actual','password']) as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('perfil.password') }}" method="POST">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group span-2">
                    <label class="form-label">Contraseña actual <span class="required">*</span></label>
                    <input type="password" name="password_actual" class="form-control {{ $errors->has('password_actual') ? 'is-error':'' }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nueva contraseña <span class="required">*</span></label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar contraseña <span class="required">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-outline" style="margin-top:8px">Actualizar contraseña</button>
        </form>
    </div>

    {{-- Mis tickets --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Mis últimas solicitudes</div>
            <a href="{{ route('tickets.index') }}" class="link" style="font-size:12.5px">Ver todas →</a>
        </div>
        @php $misTickets = auth()->user()->ticketsCreados()->with('categoria')->orderByDesc('created_at')->limit(5)->get(); @endphp
        @forelse($misTickets as $t)
        <a href="{{ route('tickets.show',$t) }}" style="display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;border:1px solid var(--border);margin-bottom:8px;text-decoration:none;transition:background .15s" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
            <span class="prio-dot prio-{{ $t->prioridad }}"></span>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->titulo }}</div>
                <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">{{ $t->numero }} · {{ $t->categoria->icono }} {{ $t->categoria->nombre }}</div>
            </div>
            <span class="badge badge-{{ $t->estado_color }}">{{ $t->estado_label }}</span>
        </a>
        @empty
        <div class="empty-state" style="padding:20px"><p>Aún no tienes solicitudes</p></div>
        @endforelse
    </div>
</div>
@endsection
