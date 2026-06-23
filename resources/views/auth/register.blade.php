<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Registro — HelpDesk AMCHAMDR</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',system-ui,sans-serif;background:#f4f6fa;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.login-wrap{width:100%;max-width:460px}
.login-brand{text-align:center;margin-bottom:28px}
.logo-wrap{background:#2563eb;border-radius:14px;padding:20px 28px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px}
.logo-wrap img{height:44px;width:auto}
.brand-sub{font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:32px;box-shadow:0 4px 24px rgba(0,0,0,.06);border-top:3px solid #E8192C}
.card-title{font-size:18px;font-weight:700;color:#0f172a;margin-bottom:4px}
.card-sub{font-size:13px;color:#64748b;margin-bottom:16px}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:13px;font-weight:500;color:#1a202c;margin-bottom:6px}
.form-control{width:100%;padding:10px 13px;border:1px solid #e2e8f0;border-radius:7px;font-size:14px;font-family:inherit;color:#1a202c;background:#fff;transition:border-color .15s;appearance:none}
.form-control:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(27,45,91,.1)}
.form-control.is-error{border-color:#dc2626}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.error-box{background:#fee2e2;color:#7f1d1d;padding:10px 14px;border-radius:7px;font-size:13px;margin-bottom:16px}
.dominio-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:7px;padding:10px 14px;font-size:13px;color:#1e40af;margin-bottom:18px}
.btn-register{width:100%;padding:11px;background:#2563eb;color:#fff;border:none;border-radius:7px;font-size:15px;font-weight:600;font-family:inherit;cursor:pointer;transition:background .15s;margin-top:4px}
.btn-register:hover{background:#1d4ed8}
.login-link{text-align:center;margin-top:16px;font-size:13px;color:#64748b}
.login-link a{color:#2563eb;font-weight:600;text-decoration:none}
.login-link a:hover{color:#E8192C}
.footer-note{text-align:center;margin-top:16px;font-size:12px;color:#94a3b8}
</style>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-blanco.png') }}" alt="AMCHAMDR">
        </div>
        <div class="brand-sub">Sistema de Soporte TI</div>
    </div>
    <div class="card">
        <div class="card-title">Crear cuenta</div>
        <div class="card-sub">Regístrate para enviar solicitudes de soporte</div>
        <div class="dominio-box">ℹ️ Solo se permiten correos institucionales de AMCHAMDR — <strong>@amcham.org.do</strong></div>
        @if($errors->any())
        <div class="error-box">@foreach($errors->all() as $e)<div>⚠️ {{ $e }}</div>@endforeach</div>
        @endif
        <form action="{{ route('register.submit') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span style="color:#E8192C">*</span></label>
                    <input type="text" name="nombre" class="form-control {{ $errors->has('nombre') ? 'is-error':'' }}" value="{{ old('nombre') }}" required placeholder="Tu nombre">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido <span style="color:#E8192C">*</span></label>
                    <input type="text" name="apellido" class="form-control {{ $errors->has('apellido') ? 'is-error':'' }}" value="{{ old('apellido') }}" required placeholder="Tu apellido">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Correo institucional <span style="color:#E8192C">*</span></label>
                <input type="email" name="correo" class="form-control {{ $errors->has('correo') ? 'is-error':'' }}" value="{{ old('correo') }}" required autofocus placeholder="tunombre@amcham.org.do">
            </div>
            <div class="form-group">
                <label class="form-label">Departamento</label>
                <input type="text" name="departamento" class="form-control" value="{{ old('departamento') }}" placeholder="Ej: Contabilidad, Mercadeo...">
            </div>
            <div class="form-group">
                <label class="form-label">Contraseña <span style="color:#E8192C">*</span></label>
                <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-error':'' }}" required placeholder="Mínimo 6 caracteres" minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label">Confirmar contraseña <span style="color:#E8192C">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required placeholder="Repite tu contraseña">
            </div>
            <button type="submit" class="btn-register">Crear cuenta</button>
        </form>
    </div>
    <div class="login-link">¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a></div>
    <div class="footer-note">AMCHAMDR · HelpDesk TI · {{ now()->year }}</div>
</div>
</body>
</html>
