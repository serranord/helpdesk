@extends('layouts.app')
@section('title','Base de Conocimientos')
@section('page-title','Base de Conocimientos')
@section('topbar-actions')
    @if(auth()->user()->puedeGestionar())
    <a href="{{ route('kb.admin') }}" class="btn btn-outline btn-sm">⚙️ Gestionar artículos</a>
    @endif
@endsection
@section('content')

{{-- Buscador --}}
<div style="background:linear-gradient(135deg,#1e3a5f,#2563eb);border-radius:12px;padding:32px;margin-bottom:24px;text-align:center">
    <div style="font-size:22px;font-weight:700;color:#fff;margin-bottom:6px">¿En qué podemos ayudarte?</div>
    <div style="font-size:14px;color:rgba(255,255,255,.7);margin-bottom:20px">Encuentra respuestas antes de abrir un ticket</div>
    <form method="GET" style="max-width:500px;margin:0 auto;display:flex;gap:8px">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar artículos..."
            style="flex:1;padding:11px 16px;border-radius:8px;border:none;font-size:14px;font-family:inherit;outline:none">
        <button type="submit" style="padding:11px 20px;background:#E8192C;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer">Buscar</button>
    </form>
</div>

{{-- Destacados --}}
@if($destacados->count() && !request('buscar'))
<div style="margin-bottom:24px">
    <div class="section-title">⭐ Artículos destacados</div>
    <div class="grid-2" style="grid-template-columns:repeat(3,1fr)">
        @foreach($destacados as $a)
        <a href="{{ route('kb.show',$a) }}" style="display:block;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:18px;text-decoration:none;transition:box-shadow .15s,transform .15s" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
            <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px;line-height:1.4">{{ $a->titulo }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px">{{ $a->tamano_contenido }}</div>
            @if($a->categoria)
            <span class="badge badge-blue">{{ $a->categoria->icono }} {{ $a->categoria->nombre }}</span>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- Listado --}}
<div class="card" style="padding:0">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <div class="card-title">
            @if(request('buscar'))
                Resultados para "{{ request('buscar') }}" ({{ $articulos->total() }})
            @else
                Todos los artículos
            @endif
        </div>
        <form method="GET">
            <select name="categoria" class="form-control" style="width:auto" onchange="this.form.submit()">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $c)
                <option value="{{ $c->id }}" @selected(request('categoria')==$c->id)>{{ $c->icono }} {{ $c->nombre }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @forelse($articulos as $a)
    <a href="{{ route('kb.show',$a) }}" style="display:flex;align-items:center;gap:16px;padding:16px 20px;border-bottom:1px solid var(--border);text-decoration:none;transition:background .15s" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background=''">
        <div style="width:40px;height:40px;background:var(--blue-light);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
            {{ $a->categoria?->icono ?? '📄' }}
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:3px">{{ $a->titulo }}</div>
            <div style="font-size:12.5px;color:var(--text-muted)">
                {{ $a->tamano_contenido }} · {{ $a->created_at->format('d/m/Y') }}
                @if($a->categoria) · {{ $a->categoria->nombre }}@endif
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:6px;color:var(--text-muted);font-size:12px;flex-shrink:0">
            👁 {{ $a->vistas }}
        </div>
    </a>
    @empty
    <div class="empty-state">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <p>No hay artículos publicados aún</p>
        @if(auth()->user()->puedeGestionar())
        <a href="{{ route('kb.admin') }}" class="btn btn-primary btn-sm" style="margin-top:12px">Crear primer artículo</a>
        @endif
    </div>
    @endforelse
    <div style="padding:16px 20px">{{ $articulos->links() }}</div>
</div>
@endsection
