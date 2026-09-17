@extends('mcp.layout')
@section('content')
<p class="label">HELPDESK · MICROSOFT 365</p>
<h1>Mis conexiones de IA</h1>
@if(session('status'))<p role="status" class="box">{{ session('status') }}</p>@endif
<p>Revocar una conexión impide que esa aplicación siga utilizando sus tokens actuales, incluida su renovación.</p>
@forelse($tokens->groupBy('client_id') as $clientId => $clientTokens)
<article>
    <h2>{{ $clientTokens->first()->client?->name ?? 'Aplicación eliminada' }}</h2>
    <p class="label">Autorizada con tu cuenta de HelpDesk.</p>
    <form method="POST" action="{{ route('mcp.connections.destroy', $clientId) }}">
        @csrf @method('DELETE')
        <button type="submit" class="secondary">Revocar conexión</button>
    </form>
</article>
@empty
<p class="box">No tienes conexiones OAuth activas.</p>
@endforelse
<p><a href="{{ route('dashboard') }}">Volver al HelpDesk</a></p>
@endsection
