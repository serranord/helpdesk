<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="font-family:DM Sans,Arial,sans-serif;background:#f4f6fa;padding:32px 0">
<div style="background:#fff;border-radius:12px;max-width:560px;margin:0 auto;padding:32px;border:1px solid #e2e8f0">
  <div style="background:#0f172a;border-radius:10px 10px 0 0;padding:20px 28px;margin:-32px -32px 28px">
    <div style="color:#fff;font-size:18px;font-weight:700"><img src="{{ asset('images/logo-blanco.png') }}" alt="AMCHAMDR" style="height:32px;width:auto;vertical-align:middle;margin-right:8px"> HelpDesk · Soporte TI</div>
  </div>
  <h2 style="font-size:18px;color:#0f172a;margin-bottom:8px">¡Solicitud recibida!</h2>
  <p style="color:#475569;margin-bottom:20px">Hola <strong>{{ $ticket->solicitante->nombre }}</strong>, hemos recibido tu solicitud correctamente. Nuestro equipo de TI la atenderá a la brevedad.</p>
  <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:20px">
    <div style="font-size:12px;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px;margin-bottom:12px">Detalle de la solicitud</div>
    <table style="width:100%;font-size:14px">
      <tr><td style="color:#64748b;padding:4px 0;width:140px">Número:</td><td style="font-weight:600;font-family:monospace">{{ $ticket->numero }}</td></tr>
      <tr><td style="color:#64748b;padding:4px 0">Título:</td><td style="font-weight:500">{{ $ticket->titulo }}</td></tr>
      <tr><td style="color:#64748b;padding:4px 0">Categoría:</td><td>{{ $ticket->categoria->icono }} {{ $ticket->categoria->nombre }}</td></tr>
      <tr><td style="color:#64748b;padding:4px 0">Estado:</td><td><span style="background:#cffafe;color:#164e63;padding:2px 8px;border-radius:20px;font-size:12px;font-weight:600">Nuevo</span></td></tr>
      <tr><td style="color:#64748b;padding:4px 0">Fecha:</td><td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td></tr>
    </table>
  </div>
  <a href="{{ route('tickets.show', $ticket) }}" style="display:inline-block;background:#1B2D5B;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;margin:20px 0">Ver mi solicitud →</a>
  <p style="font-size:13px;color:#94a3b8">Puedes hacer seguimiento del estado de tu solicitud en cualquier momento desde el sistema.</p>
  <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;text-align:center">AMCHAMDR · HelpDesk TI · {{ now()->year }}<br>Este es un correo automático, por favor no respondas a este mensaje.</div>
</div>
</body></html>
