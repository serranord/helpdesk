<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Iniciar Sesión — HelpDesk AMCHAMDR</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',system-ui,sans-serif;background:#f4f6fa;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.login-wrap{width:100%;max-width:420px}
.login-brand{text-align:center;margin-bottom:28px}
.brand-icon{width:56px;height:56px;background:#2563eb;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
.brand-icon svg{width:28px;height:28px;color:#fff;fill:none;stroke:currentColor;stroke-width:2}
.brand-name{font-size:22px;font-weight:700;color:#0f172a}
.brand-sub{font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-top:4px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:32px;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.card-title{font-size:18px;font-weight:700;color:#0f172a;margin-bottom:4px}
.card-sub{font-size:13px;color:#64748b;margin-bottom:24px}

/* Botón Microsoft */
.btn-microsoft{width:100%;padding:11px;background:#fff;color:#1a202c;border:1.5px solid #e2e8f0;border-radius:7px;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:background .15s,border-color .15s;text-decoration:none;margin-bottom:20px}
.btn-microsoft:hover{background:#f8fafc;border-color:#2563eb}
.btn-microsoft img{width:20px;height:20px}

.divider{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.divider-line{flex:1;height:1px;background:#e2e8f0}
.divider-text{font-size:12px;color:#94a3b8;white-space:nowrap}

.form-group{margin-bottom:16px}
.form-label{display:block;font-size:13px;font-weight:500;color:#1a202c;margin-bottom:6px}
.form-control{width:100%;padding:10px 13px;border:1px solid #e2e8f0;border-radius:7px;font-size:14px;font-family:inherit;color:#1a202c;background:#fff;transition:border-color .15s,box-shadow .15s;appearance:none}
.form-control:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.form-control.is-error{border-color:#dc2626}
.error-box{background:#fee2e2;color:#7f1d1d;padding:10px 14px;border-radius:7px;font-size:13px;margin-bottom:18px}
.remember{display:flex;align-items:center;gap:8px;font-size:13px;color:#475569;margin-bottom:22px;cursor:pointer}
.btn-login{width:100%;padding:11px;background:#2563eb;color:#fff;border:none;border-radius:7px;font-size:15px;font-weight:600;font-family:inherit;cursor:pointer;transition:background .15s}
.btn-login:hover{background:#1d4ed8}
.register-link{text-align:center;margin-top:16px;font-size:13px;color:#64748b}
.register-link a{color:#2563eb;font-weight:600;text-decoration:none}
.footer-note{text-align:center;margin-top:16px;font-size:12px;color:#94a3b8}
</style>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
        </div>
        <div class="brand-name">HelpDesk DR</div>
        <div class="brand-sub">AMCHAMDR · Sistema de Soporte TI</div>
    </div>

    <div class="card">
        <div class="card-title">Iniciar Sesión</div>
        <div class="card-sub">Accede con tu cuenta institucional de AmCham</div>

        @if($errors->any())
        <div class="error-box">⚠️ @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif

        {{-- Botón Microsoft SSO --}}
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

        {{-- Divider --}}
        <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text">o ingresa con correo y contraseña</span>
            <div class="divider-line"></div>
        </div>

        {{-- Login tradicional --}}
        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <input type="email" name="correo" id="correo"
                       class="form-control {{ $errors->has('correo') ? 'is-error' : '' }}"
                       value="{{ old('correo') }}" required autofocus placeholder="usuario@amcham.org.do">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" name="password" id="password"
                       class="form-control" required placeholder="••••••••">
            </div>
            <label class="remember">
                <input type="checkbox" name="remember" value="1"> Mantener sesión iniciada
            </label>
            <button type="submit" class="btn-login">Entrar al sistema</button>
        </form>
    </div>

    <div class="register-link">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></div>
    <div class="footer-note">AMCHAMDR · HelpDesk TI · {{ now()->year }}</div>
</div>
</body>
</html>
