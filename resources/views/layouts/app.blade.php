<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','HelpDesk') — HelpDesk DR</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

<nav class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
        </div>
        <div>
            <span class="brand-title">HelpDesk DR</span>
            <span class="brand-sub">AmCham · Soporte TI</span>
        </div>
    </div>

    <div class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>

        <div class="nav-section">Soporte</div>

        <a href="{{ route('tickets.index') }}" class="nav-item {{ request()->routeIs('tickets.index','tickets.show') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            Mis Tickets
            @php
                $abiertos = \App\Models\Ticket::whereNotIn('estado',['resuelto','cerrado'])
                    ->when(auth()->user()->esTecnico(), fn($q)=>$q->where(fn($q2)=>$q2->where('tecnico_id',auth()->id())->orWhereNull('tecnico_id')))
                    ->when(auth()->user()->esSolicitante(), fn($q)=>$q->where('solicitante_id',auth()->id()))
                    ->count();
            @endphp
            @if($abiertos > 0)
            <span style="background:#dc2626;color:#fff;border-radius:20px;padding:1px 7px;font-size:10px;font-weight:700;margin-left:auto;">{{ $abiertos }}</span>
            @endif
        </a>

        <a href="{{ route('tickets.create') }}" class="nav-item {{ request()->routeIs('tickets.create') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nueva Solicitud
        </a>
        <a href="{{ route('kb.index') }}" class="nav-item {{ request()->routeIs('kb.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Base de Conocimientos
        </a>

        @if(auth()->user()->puedeGestionar())
        <div class="nav-section">Gestión TI</div>
        <a href="{{ route('tickets.index') }}?estado=nuevo" class="nav-item">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            Tickets Nuevos
            @php $nuevos = \App\Models\Ticket::where('estado','nuevo')->count(); @endphp
            @if($nuevos > 0)
            <span style="background:#0891b2;color:#fff;border-radius:20px;padding:1px 7px;font-size:10px;font-weight:700;margin-left:auto;">{{ $nuevos }}</span>
            @endif
        </a>
        <a href="{{ route('tecnico.panel') }}" class="nav-item {{ request()->routeIs('tecnico.panel') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Mi Panel
        </a>
        <a href="{{ route('plantillas.index') }}" class="nav-item {{ request()->routeIs('plantillas.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
            Plantillas
        </a>
        <a href="{{ route('tickets.index') }}?estado=en_proceso" class="nav-item">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            En Proceso
        </a>
        @endif

        @if(auth()->user()->esAdministrador())
        <div class="nav-section">Administración</div>
        <a href="{{ route('reportes.index') }}" class="nav-item {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Reportes
        </a>
        <a href="{{ route('usuarios.index') }}" class="nav-item {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Usuarios
        </a>
        <a href="{{ route('categorias.index') }}" class="nav-item {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            Categorías
        </a>
        @endif
    </div>

    <div class="sidebar-footer">
        <a href="{{ route('perfil.show') }}" style="text-decoration:none">
            <div class="user-info" style="cursor:pointer;padding:6px;border-radius:8px;transition:background .15s" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background=''">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->nombre,0,1)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->nombre }}</div>
                    <div class="user-role">{{ auth()->user()->rol_label }} · Ver perfil</div>
                </div>
            </div>
        </a>
        <form action="{{ route('logout') }}" method="POST" style="margin-top:10px">
            @csrf
            <button type="submit" style="background:none;border:none;color:#64748b;font-size:12px;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:6px;padding:4px 6px;transition:color .15s;border-radius:6px" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</nav>

<div class="main-wrapper">
    <div class="topbar">
        <div class="page-title">@yield('page-title','Dashboard')</div>
        <div class="flex items-center gap-3">
            {{-- Campana notificaciones --}}
            <div style="position:relative" id="notif-wrap">
                <button onclick="toggleNotif()" style="background:none;border:none;cursor:pointer;width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;position:relative;transition:background .15s" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;color:var(--text-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span id="notif-badge" style="display:none;position:absolute;top:4px;right:4px;width:16px;height:16px;background:#dc2626;color:#fff;border-radius:50%;font-size:9px;font-weight:700;align-items:center;justify-content:center">0</span>
                </button>
                <div id="notif-panel" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:340px;background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:200">
                    <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:13px;font-weight:600">Notificaciones</span>
                        <a href="{{ route('notificaciones.index') }}" style="font-size:12px;color:var(--blue);text-decoration:none">Ver todas</a>
                    </div>
                    <div id="notif-list" style="max-height:320px;overflow-y:auto">
                        <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">Cargando...</div>
                    </div>
                </div>
            </div>
            {{-- Búsqueda global --}}
            <form action="{{ route('tickets.index') }}" method="GET" style="position:relative">
                <input type="text" name="buscar" placeholder="🔍 Buscar ticket..." value="{{ request('buscar') }}"
                    style="padding:7px 14px;border:1px solid var(--border);border-radius:20px;font-size:13px;font-family:inherit;background:var(--surface-2);color:var(--text);width:220px;outline:none;transition:border-color .15s"
                    onfocus="this.style.borderColor='var(--blue)'" onblur="this.style.borderColor='var(--border)'">
            </form>
            @yield('topbar-actions')
        </div>
    </div>
    <div class="content">
        @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-error">⚠️ {{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</div>


<script>
// Campana de notificaciones
let notifOpen = false;

function toggleNotif() {
    notifOpen = !notifOpen;
    const panel = document.getElementById('notif-panel');
    panel.style.display = notifOpen ? 'block' : 'none';
    if (notifOpen) cargarNotificaciones();
}

function cargarNotificaciones() {
    fetch('{{ route("notificaciones.no-leidas") }}', {
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
    })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('notif-badge');
        const list  = document.getElementById('notif-list');
        // Badge
        if (data.count > 0) {
            badge.style.display = 'flex';
            badge.textContent = data.count > 9 ? '9+' : data.count;
        } else {
            badge.style.display = 'none';
        }
        // Lista
        if (data.items.length === 0) {
            list.innerHTML = '<div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">Sin notificaciones nuevas 🎉</div>';
        } else {
            list.innerHTML = data.items.map(n => `
                <a href="${n.url || '#'}" style="display:flex;gap:10px;padding:12px 16px;border-bottom:1px solid #f1f5f9;text-decoration:none;transition:background .15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <span style="font-size:18px">${n.icono}</span>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:12.5px;font-weight:600;color:#1a202c">${n.titulo}</div>
                        <div style="font-size:12px;color:#718096;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${n.mensaje}</div>
                    </div>
                </a>
            `).join('');
        }
    }).catch(() => {});
}

// Revisar badge cada 30 segundos
function revisarBadge() {
    fetch('{{ route("notificaciones.no-leidas") }}', {
        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
    })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('notif-badge');
        if (data.count > 0) {
            badge.style.display = 'flex';
            badge.textContent = data.count > 9 ? '9+' : data.count;
        } else {
            badge.style.display = 'none';
        }
    }).catch(() => {});
}

document.addEventListener('click', function(e) {
    const wrap = document.getElementById('notif-wrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('notif-panel').style.display = 'none';
        notifOpen = false;
    }
});

revisarBadge();
setInterval(revisarBadge, 30000);
</script>
</body>
</html>
