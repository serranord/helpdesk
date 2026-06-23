<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:DM Sans,Arial,sans-serif;background:#f4f6fa;padding:32px 0">
<div style="background:#fff;border-radius:12px;max-width:560px;margin:0 auto;padding:32px;border:1px solid #e2e8f0">
  <div style="background:#16a34a;border-radius:10px 10px 0 0;padding:20px 28px;margin:-32px -32px 28px">
    <div style="color:#fff;font-size:18px;font-weight:700"><img src="{{ asset('images/logo-blanco.png') }}" alt="AMCHAMDR" style="height:32px;width:auto;vertical-align:middle;margin-right:8px"> HelpDesk · Soporte TI</div>
  </div>
  <h2 style="font-size:18px;color:#0f172a;margin-bottom:8px">¡Tu solicitud fue resuelta!</h2>
  <p style="color:#475569;margin-bottom:20px">Hola <strong>{{ $ticket->solicitante->nombre }}</strong>, nos complace informarte que tu solicitud <strong>{{ $ticket->numero }}</strong> ha sido resuelta satisfactoriamente.</p>
  @if($ticket->nota_cierre)
  <div style="background:#dcfce7;border-left:4px solid #16a34a;border-radius:0 8px 8px 0;padding:14px 16px;margin-bottom:20px">
    <div style="font-size:12px;color:#166534;font-weight:600;margin-bottom:6px">Nota de resolución:</div>
    <div style="font-size:14px;color:#14532d;line-height:1.6">{{ $ticket->nota_cierre }}</div>
  </div>
  @endif
  <p style="color:#475569;margin-bottom:20px">¿Quedaste satisfecho/a con la atención recibida? Tu opinión nos ayuda a mejorar.</p>
  <a href="{{ route('tickets.show', $ticket) }}" style="display:inline-block;background:#1B2D5B;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;margin:20px 0">Calificar atención ⭐</a>
  <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;text-align:center">AMCHAMDR · HelpDesk TI · {{ now()->year }}</div>
</div>
</body></html>
