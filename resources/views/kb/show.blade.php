@extends('layouts.app')
@section('title',$kbArticulo->titulo)
@section('page-title','Base de Conocimientos')
@section('topbar-actions')
    <a href="{{ route('kb.index') }}" class="btn btn-outline btn-sm">← Volver</a>
    @if(auth()->user()->puedeGestionar())
    <a href="{{ route('kb.edit',$kbArticulo) }}" class="btn btn-outline btn-sm">✏️ Editar</a>
    @endif
@endsection
@section('content')

<div class="grid-2" style="align-items:start;grid-template-columns:1fr 280px">
    <div>
        <div class="card">
            <div style="margin-bottom:20px">
                @if($kbArticulo->categoria)
                <span class="badge badge-blue" style="margin-bottom:10px">{{ $kbArticulo->categoria->icono }} {{ $kbArticulo->categoria->nombre }}</span>
                @endif
                <h1 style="font-size:22px;font-weight:700;color:var(--text);line-height:1.3;margin-bottom:10px">{{ $kbArticulo->titulo }}</h1>
                <div style="font-size:12.5px;color:var(--text-muted);display:flex;gap:16px">
                    <span>✍️ {{ $kbArticulo->autor->nombre }}</span>
                    <span>📅 {{ $kbArticulo->created_at->format('d/m/Y') }}</span>
                    <span>👁 {{ $kbArticulo->vistas }} vistas</span>
                    <span>⏱ {{ $kbArticulo->tamano_contenido }}</span>
                </div>
            </div>
            <div style="border-top:1px solid var(--border);padding-top:20px;font-size:14.5px;line-height:1.8;color:var(--text)">
                {!! nl2br(e($kbArticulo->contenido)) !!}
            </div>
        </div>

        <div style="margin-top:16px;padding:16px 20px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:space-between">
            <div style="font-size:13px;color:var(--text-muted)">¿Este artículo te fue útil?</div>
            <div style="display:flex;gap:8px">
                <button style="padding:6px 14px;border:1px solid var(--border);border-radius:6px;background:var(--surface);cursor:pointer;font-size:13px">👍 Sí</button>
                <button style="padding:6px 14px;border:1px solid var(--border);border-radius:6px;background:var(--surface);cursor:pointer;font-size:13px">👎 No</button>
            </div>
            <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">¿Aún tienes problemas? Abrir ticket</a>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        @if($relacionados->count())
        <div class="card">
            <div class="section-title">Artículos relacionados</div>
            @foreach($relacionados as $r)
            <a href="{{ route('kb.show',$r) }}" style="display:block;padding:10px 0;border-bottom:1px solid var(--border);text-decoration:none;font-size:13px;font-weight:500;color:var(--text);transition:color .15s" onmouseover="this.style.color='var(--blue)'" onmouseout="this.style.color='var(--text)'">
                {{ $r->titulo }}
                <div style="font-size:11.5px;color:var(--text-muted);font-weight:400;margin-top:2px">{{ $r->tamano_contenido }}</div>
            </a>
            @endforeach
        </div>
        @endif
        <div class="card" style="text-align:center">
            <div style="font-size:20px;margin-bottom:8px">🙋</div>
            <div style="font-size:13px;font-weight:600;margin-bottom:4px">¿No encontraste lo que buscabas?</div>
            <div style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px">Nuestro equipo de TI puede ayudarte</div>
            <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm" style="width:100%;justify-content:center">Abrir ticket de soporte</a>
        </div>
    </div>
</div>
@endsection
