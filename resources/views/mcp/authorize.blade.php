@extends('mcp.layout')
@section('content')
<p class="label">HELPDESK · CONEXIÓN CON IA</p>
<h1>¿Autorizar a {{ $client->name }}?</h1>
<p>Estás conectado como <strong>{{ $user->nombre }}</strong> ({{ $user->correo }}).</p>
<div class="box">
    <strong>La aplicación podrá:</strong>
    <ul><li>Consultar tickets y estadísticas de todo el HelpDesk.</li><li>Ver categorías y crear tickets en nombre de solicitantes activos.</li><li>Mantener la conexión sin que tengas el navegador abierto.</li></ul>
</div>
<p>El nombre lo proporciona la aplicación. Comprueba que corresponde a la conexión que acabas de iniciar.</p>
<p class="label">Volverás a: {{ $request->input('redirect_uri') ?: ($client->redirect_uris[0] ?? '') }}</p>
<div class="actions">
    <form method="POST" action="{{ route('passport.authorizations.approve') }}">
        @csrf
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        <button type="submit">Autorizar conexión</button>
    </form>
    <form method="POST" action="{{ route('passport.authorizations.deny') }}">
        @csrf @method('DELETE')
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        <button type="submit" class="secondary">Cancelar</button>
    </form>
</div>
<p class="label">Puedes revocar el acceso desde <a href="{{ route('mcp.connections') }}">Mis conexiones de IA</a>.</p>
@endsection
