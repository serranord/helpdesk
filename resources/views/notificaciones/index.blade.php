@extends('layouts.app')
@section('title','Notificaciones')
@section('page-title','Mis Notificaciones')
@section('topbar-actions')
    <form action="{{ route('notificaciones.leer-todas') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">✓ Marcar todas como leídas</button>
    </form>
@endsection
@section('content')
<div style="max-width:700px">
<div class="card" style="padding:0">
    @forelse($notificaciones as $n)
    <div style="display:flex;gap:14px;padding:16px 20px;border-bottom:1px solid var(--border);background:{{ $n->estaLeida() ? 'transparent' : 'var(--blue-light)' }};transition:background .15s">
        <div style="font-size:24px;flex-shrink:0;width:36px;text-align:center">{{ $n->icono }}</div>
        <div style="flex:1;min-width:0">
            <div style="font-size:13.5px;font-weight:600;color:var(--text);margin-bottom:3px">{{ $n->titulo }}</div>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:6px">{{ $n->mensaje }}</div>
            <div style="font-size:11.5px;color:var(--text-muted)">{{ $n->created_at->diffForHumans() }}</div>
        </div>
        @if($n->url)
        <a href="{{ $n->url }}" class="btn btn-outline btn-sm" style="flex-shrink:0;align-self:center">Ver →</a>
        @endif
    </div>
    @empty
    <div class="empty-state">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <p>No tienes notificaciones</p>
    </div>
    @endforelse
    <div style="padding:16px 20px">{{ $notificaciones->links() }}</div>
</div>
</div>
@endsection
