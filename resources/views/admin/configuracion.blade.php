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

            <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px">

                {{-- Toggle Registro manual --}}
                @php $registroOn = ($configs['registro_habilitado']->valor ?? '1') === '1'; @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--text)">Registro manual de usuarios</div>
                        <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Permite que los usuarios se registren con correo y contraseña</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:12px;color:var(--text-muted)" id="label-registro">{{ $registroOn ? 'Activado' : 'Desactivado' }}</span>
                        <div onclick="toggleSwitch('registro_habilitado','label-registro')"
                             id="switch-registro"
                             style="width:44px;height:24px;background:{{ $registroOn ? '#002049' : '#cbd5e1' }};border-radius:24px;cursor:pointer;position:relative;transition:background .3s;flex-shrink:0">
                            <div style="position:absolute;top:3px;left:{{ $registroOn ? '23px' : '3px' }};width:18px;height:18px;background:#fff;border-radius:50%;transition:left .3s;box-shadow:0 1px 3px rgba(0,0,0,.2)"
                                 id="dot-registro"></div>
                        </div>
                        <input type="hidden" name="registro_habilitado" id="registro_habilitado" value="{{ $registroOn ? '1' : '0' }}">
                    </div>
                </div>

                {{-- Toggle Solo SSO --}}
                @php $soloSSOOn = ($configs['solo_sso']->valor ?? '0') === '1'; @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--text)">Solo Microsoft 365 (SSO)</div>
                        <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px">Oculta el formulario de correo/contraseña y solo muestra el botón de Microsoft</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:12px;color:var(--text-muted)" id="label-sso">{{ $soloSSOOn ? 'Activado' : 'Desactivado' }}</span>
                        <div onclick="toggleSwitch('solo_sso','label-sso')"
                             id="switch-sso"
                             style="width:44px;height:24px;background:{{ $soloSSOOn ? '#002049' : '#cbd5e1' }};border-radius:24px;cursor:pointer;position:relative;transition:background .3s;flex-shrink:0">
                            <div style="position:absolute;top:3px;left:{{ $soloSSOOn ? '23px' : '3px' }};width:18px;height:18px;background:#fff;border-radius:50%;transition:left .3s;box-shadow:0 1px 3px rgba(0,0,0,.2)"
                                 id="dot-sso"></div>
                        </div>
                        <input type="hidden" name="solo_sso" id="solo_sso" value="{{ $soloSSOOn ? '1' : '0' }}">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Guardar configuración</button>
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

<script>
function toggleSwitch(campo, labelId) {
    const input  = document.getElementById(campo);
    const dot    = document.getElementById('dot-' + campo.replace('_habilitado','').replace('_','-').replace('solo-sso','sso').replace('registro-habilitado','registro'));
    const sw     = document.getElementById('switch-' + campo.replace('_habilitado','').replace('solo_sso','sso').replace('registro_habilitado','registro'));
    const label  = document.getElementById(labelId);
    const isOn   = input.value === '1';

    input.value       = isOn ? '0' : '1';
    sw.style.background = isOn ? '#cbd5e1' : '#002049';
    label.textContent   = isOn ? 'Desactivado' : 'Activado';

    // Mover el dot
    const dotEl = sw.querySelector('div');
    dotEl.style.left = isOn ? '3px' : '23px';
}
</script>
@endsection
