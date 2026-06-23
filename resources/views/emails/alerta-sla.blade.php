<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:DM Sans,Arial,sans-serif;background:#f4f6fa;padding:32px 0">
<div style="background:#fff;border-radius:12px;max-width:560px;margin:0 auto;padding:32px;border:1px solid #e2e8f0">
  <div style="background:{{ $vencido ? '#dc2626' : '#d97706' }};border-radius:10px 10px 0 0;padding:20px 28px;margin:-32px -32px 28px">
    <div style="color:#fff;font-size:18px;font-weight:700">{{ $vencido ? '🔴 SLA VENCIDO' : '⚠️ SLA Próximo a Vencer' }} — HelpDesk DR</div>
  </div>
  <h2 style="font-size:17px;color:#0f172a;margin-bottom:8px">
    {{ $vencido ? 'El siguiente ticket ha superado su tiempo límite de resolución.' : 'El siguiente ticket vencerá en menos de 2 horas.' }}
  </h2>
  <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin:20px 0">
    <table style="width:100%;font-size:14px">
      <tr><td style="color:#64748b;padding:4px 0;width:140px">Ticket:</td><td style="font-weight:700;font-family:monospace">{{ $ticket->numero }}</td></tr>
      <tr><td style="color:#64748b;padding:4px 0">Título:</td><td>{{ $ticket->titulo }}</td></tr>
      <tr><td style="color:#64748b;padding:4px 0">Solicitante:</td><td>{{ $ticket->solicitante->nombre }}</td></tr>
      <tr><td style="color:#64748b;padding:4px 0">Categoría:</td><td>{{ $ticket->categoria->icono }} {{ $ticket->categoria->nombre }}</td></tr>
      <tr><td style="color:#64748b;padding:4px 0">Estado:</td><td><strong>{{ $ticket->estado_label }}</strong></td></tr>
      <tr><td style="color:#64748b;padding:4px 0">Límite SLA:</td><td style="color:{{ $vencido ? '#dc2626' : '#d97706' }};font-weight:700">{{ $ticket->fecha_limite->format('d/m/Y H:i') }}</td></tr>
    </table>
  </div>
  <a href="{{ route('tickets.show', $ticket) }}" style="display:inline-block;background:{{ $vencido ? '#dc2626' : '#d97706' }};color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px">
    Atender ahora →
  </a>
  <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;text-align:center">AMCHAMDR · HelpDesk TI · {{ now()->year }}</div>
</div>
</body></html>
