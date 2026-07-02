<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Iniciar Sesión — HelpDesk AMCHAMDR</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',system-ui,sans-serif;background:#f4f6f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wrap{width:100%;max-width:420px}
.brand{text-align:center;margin-bottom:28px}
.brand img{height:60px;width:auto;margin-bottom:12px}
.brand-sub{font-size:12px;color:#64748b;margin-top:3px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:32px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.card-title{font-size:18px;font-weight:700;color:#002049;margin-bottom:4px}
.card-sub{font-size:13px;color:#64748b;margin-bottom:24px}
.btn-microsoft{width:100%;padding:12px;background:#fff;color:#1a202c;border:1.5px solid #e2e8f0;border-radius:7px;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:all .15s;text-decoration:none;margin-bottom:20px}
.btn-microsoft:hover{background:#f8fafc;border-color:#002049}
.divider{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.divider-line{flex:1;height:1px;background:#e2e8f0}
.divider-text{font-size:12px;color:#94a3b8;white-space:nowrap}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:13px;font-weight:600;color:#1a202c;margin-bottom:6px}
.form-control{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:14px;font-family:inherit;color:#1a202c;background:#fff;transition:border-color .15s;appearance:none}
.form-control:focus{outline:none;border-color:#002049;box-shadow:0 0 0 3px rgba(0,32,73,.1)}
.form-control.is-error{border-color:#dc2626}
.error-box{background:#fee2e2;color:#7f1d1d;padding:10px 14px;border-radius:7px;font-size:13px;margin-bottom:18px;border:1px solid #fca5a5}
.remember{display:flex;align-items:center;gap:8px;font-size:13px;color:#475569;margin-bottom:22px;cursor:pointer}
.btn-login{width:100%;padding:11px;background:#002049;color:#fff;border:none;border-radius:7px;font-size:15px;font-weight:600;font-family:inherit;cursor:pointer;transition:background .15s}
.btn-login:hover{background:#001535}
.register-link{text-align:center;margin-top:16px;font-size:13px;color:#64748b}
.register-link a{color:#002049;font-weight:600;text-decoration:none}
.footer{text-align:center;margin-top:20px;font-size:12px;color:#94a3b8}
</style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <img src="{{ asset('images/logo-color.png') }}" alt="AMCHAMDR">
        <div class="brand-sub">Sistema de Soporte · Departamento de TI</div>
    </div>

    <div class="card">
        <div class="card-title">Iniciar Sesión</div>
        <div class="card-sub">Accede con tu cuenta institucional de AmCham</div>

        @if($errors->any())
        <div class="error-box">⚠️ @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif

        {{-- Botón Microsoft siempre visible --}}
        <a href="{{ route('microsoft.redirect') }}" class="btn-microsoft">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 23 23">
                <path fill="#f3f3f3" d="M0 0h23v23H0z"/>
                <path fill="#f35325" d="M1 1h10v10H1z"/>
                <path fill="#81bc06" d="M12 1h10v10H12z"/>
                <path fill="#05a6f0" d="M1 12h10v10H1z"/>
                <path fill="#ffba08" d="M12 12h10v10H12z"/>
            </svg>
            Continuar con Microsoft 365
        </a>

        @php $soloSSO = \App\Models\Configuracion::soloSSO(); @endphp

        @if(!$soloSSO)
        <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text">o ingresa con correo y contraseña</span>
            <div class="divider-line"></div>
        </div>

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="correo" class="form-control {{ $errors->has('correo') ? 'is-error':'' }}"
                    value="{{ old('correo') }}" required autofocus placeholder="usuario@amcham.org.do">
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <label class="remember">
                <input type="checkbox" name="remember" value="1" style="accent-color:#002049"> Mantener sesión iniciada
            </label>
            <button type="submit" class="btn-login">Entrar al sistema</button>
        </form>
        @endif
    </div>

    @php $registroHabilitado = \App\Models\Configuracion::registroHabilitado(); @endphp
    @if($registroHabilitado && !$soloSSO)
    <div class="register-link">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></div>
    @endif
    <div class="footer">AMCHAMDR · HelpDesk TI · {{ now()->year }}</div>
</div>
</body>
</html>
