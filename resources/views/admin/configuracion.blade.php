@extends('layouts.app')
@section('title','Configuración')
@section('page-title','Configuración del Sistema')
@section('content')

<div style="max-width:800px;display:flex;flex-direction:column;gap:20px">

    {{-- Acceso y registro --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">🔐 Acceso y Registro</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Controla cómo los usuarios pueden acceder al sistema</div>
            </div>
        </div>
        <form action="{{ route('configuracion.update') }}" method="POST">
            @csrf @method('PUT')
            <div style="display:flex;flex-direction:column;gap:16px">

                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--text)">Registro manual de usuarios</div>
                        <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Permite que los usuarios se registren con correo y contraseña</div>
                    </div>
                    <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;cursor:pointer">
                        <input type="checkbox" name="registro_habilitado" value="1" {{ ($configs['registro_habilitado']->valor ?? '1') === '1' ? 'checked' : '' }} style="opacity:0;width:0;height:0">
                        <span style="position:absolute;inset:0;background:{{ ($configs['registro_habilitado']->valor ?? '1') === '1' ? '#002049' : '#cbd5e1' }};border-radius:24px;transition:.3s;cursor:pointer" onclick="this.style.background=this.previousElementSibling.checked?'#cbd5e1':'#002049';this.previousElementSibling.checked=!this.previousElementSibling.checked">
                            <span style="position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s;transform:{{ ($configs['registro_habilitado']->valor ?? '1') === '1' ? 'translateX(20px)' : 'translateX(0)' }}"></span>
                        </span>
                    </label>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--text)">Solo Microsoft 365 (SSO)</div>
                        <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Oculta el formulario de correo/contraseña y solo muestra el botón de Microsoft</div>
                    </div>
                    <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;cursor:pointer">
                        <input type="checkbox" name="solo_sso" value="1" {{ ($configs['solo_sso']->valor ?? '0') === '1' ? 'checked' : '' }} style="opacity:0;width:0;height:0">
                        <span style="position:absolute;inset:0;background:{{ ($configs['solo_sso']->valor ?? '0') === '1' ? '#002049' : '#cbd5e1' }};border-radius:24px;transition:.3s;cursor:pointer" onclick="this.style.background=this.previousElementSibling.checked?'#cbd5e1':'#002049';this.previousElementSibling.checked=!this.previousElementSibling.checked">
                            <span style="position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s;transform:{{ ($configs['solo_sso']->valor ?? '0') === '1' ? 'translateX(20px)' : 'translateX(0)' }}"></span>
                        </span>
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:16px">Guardar configuración</button>
        </form>
    </div>

    {{-- Menú de aplicaciones --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">🔗 Menú de Aplicaciones</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Enlaces que aparecen en la barra superior del sistema</div>
            </div>
            <button onclick="document.getElementById('modal-menu').classList.add('open')" class="btn btn-primary btn-sm">+ Agregar enlace</button>
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Ícono</th><th>Nombre</th><th>URL</th><th>Orden</th><th>Nueva ventana</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                    @forelse($menus as $m)
                    <tr>
                        <td style="font-size:20px">{{ $m->icono }}</td>
                        <td class="fw-600">{{ $m->nombre }}</td>
                        <td class="mono text-muted" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $m->url }}</td>
                        <td>{{ $m->orden }}</td>
                        <td>{{ $m->nueva_ventana ? '✅ Sí' : '—' }}</td>
                        <td>
                            <form action="{{ route('configuracion.menu.update',$m) }}" method="POST" style="display:inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="nombre" value="{{ $m->nombre }}">
                                <input type="hidden" name="url" value="{{ $m->url }}">
                                <input type="hidden" name="icono" value="{{ $m->icono }}">
                                <input type="hidden" name="orden" value="{{ $m->orden }}">
                                <input type="hidden" name="nueva_ventana" value="{{ $m->nueva_ventana ? 1 : 0 }}">
                                <input type="hidden" name="activo" value="{{ $m->activo ? 0 : 1 }}">
                                <button type="submit" class="badge {{ $m->activo ? 'badge-green' : 'badge-gray' }}" style="border:none;cursor:pointer">
                                    {{ $m->activo ? '✅ Activo' : '⏸ Inactivo' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('configuracion.menu.destroy',$m) }}" method="POST" onsubmit="return confirm('¿Eliminar este enlace?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">✕</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding:24px">Sin enlaces configurados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal nuevo enlace --}}
<div class="modal-overlay" id="modal-menu" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box">
        <div class="modal-title">Nuevo enlace</div>
        <div class="modal-sub">Agrega un sistema o página al menú superior</div>
        <form action="{{ route('configuracion.menu.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="nombre" class="form-control" required placeholder="Ej: Activos TI">
                </div>
                <div class="form-group">
                    <label class="form-label">Ícono (emoji)</label>
                    <input type="text" name="icono" class="form-control" value="🔗" maxlength="4">
                </div>
                <div class="form-group span-2">
                    <label class="form-label">URL <span class="required">*</span></label>
                    <input type="text" name="url" class="form-control" required placeholder="https://sistema.amcham.org.do">
                </div>
                <div class="form-group">
                    <label class="form-label">Orden</label>
                    <input type="number" name="orden" class="form-control" value="0" min="0">
                </div>
                <div class="form-group" style="justify-content:flex-end;padding-top:24px">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
                        <input type="checkbox" name="nueva_ventana" value="1" checked> Abrir en nueva ventana
                    </label>
                </div>
            </div>
            <div class="flex gap-2" style="margin-top:16px">
                <button type="submit" class="btn btn-primary">Agregar</button>
                <button type="button" onclick="document.getElementById('modal-menu').classList.remove('open')" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>
</div>
@endsection
