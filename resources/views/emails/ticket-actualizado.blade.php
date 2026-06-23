<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:DM Sans,Arial,sans-serif;background:#f4f6fa;padding:32px 0">
<div style="background:#fff;border-radius:12px;max-width:560px;margin:0 auto;padding:32px;border:1px solid #e2e8f0">
  <div style="background:#0f172a;border-radius:10px 10px 0 0;padding:20px 28px;margin:-32px -32px 28px">
    <div style="color:#fff;font-size:18px;font-weight:700"><img src="{{ asset('images/logo-blanco.png') }}" alt="AMCHAMDR" style="height:32px;width:auto;vertical-align:middle;margin-right:8px"> HelpDesk · Soporte TI</div>
  </div>
  <h2 style="font-size:18px;color:#0f172a;margin-bottom:8px">Actualización en tu solicitud</h2>
  <p style="color:#475569;margin-bottom:20px">Hola <strong>{{ $ticket->solicitante->nombre }}</strong>, el técnico ha publicado una actualización en tu solicitud <strong>{{ $ticket->numero }}</strong>.</p>
  <div style="background:#eff6ff;border-left:4px solid #1B2D5B;border-radius:0 8px 8px 0;padding:14px 16px;margin-bottom:20px">
    <div style="font-size:12px;color:#1e40af;font-weight:600;margin-bottom:6px">{{ $comentario->usuario->nombre }} escribió:</div>
    <div style="font-size:14px;color:#1e3a8a;line-height:1.6">{{ $comentario->contenido }}</div>
  </div>
  <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:20px">
    <table style="width:100%;font-size:13px">
      <tr><td style="color:#64748b;padding:3px 0;width:120px">Ticket:</td><td style="font-family:monospace;font-weight:600">{{ $ticket->numero }}</td></tr>
      <tr><td style="color:#64748b;padding:3px 0">Estado actual:</td><td><strong>{{ $ticket->estado_label }}</strong></td></tr>
    </table>
  </div>
  <a href="{{ route('tickets.show', $ticket) }}" style="display:inline-block;background:#1B2D5B;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;margin:20px 0">Ver mi solicitud →</a>
  <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;text-align:center">AMCHAMDR · HelpDesk TI · {{ now()->year }}</div>
</div>
</body></html>
