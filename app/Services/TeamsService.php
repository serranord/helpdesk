<?php
namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamsService
{
    private string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('services.teams.webhook_url', '');
    }

    public function enviar(array $card): bool
    {
        if (empty($this->webhookUrl)) return false;
        try {
            $response = Http::timeout(10)->post($this->webhookUrl, [
                'type'        => 'message',
                'attachments' => [[
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'content'     => $card,
                ]]
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Teams webhook error: ' . $e->getMessage());
            return false;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function mencion(\App\Models\Usuario $usuario): string
    {
        return "<at>{$usuario->nombre}</at>";
    }

    private function accionVerTicket(\App\Models\Ticket $ticket): array
    {
        return ['type' => 'Action.OpenUrl', 'title' => 'Ver mi solicitud →', 'url' => route('tickets.show', $ticket)];
    }

    // ── TICKET CREADO — notifica al canal TI + mención al solicitante ─────────
    public function notificarTicketNuevo(\App\Models\Ticket $ticket): void
    {
        $esCritico = $ticket->prioridad === 'critica';
        $this->enviar([
            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'    => 'AdaptiveCard',
            'version' => '1.4',
            'msteams' => ['entities' => [[
                'type'      => 'mention',
                'text'      => "<at>{$ticket->solicitante->nombre}</at>",
                'mentioned' => ['id' => $ticket->solicitante->correo, 'name' => $ticket->solicitante->nombre],
            ]]],
            'body' => [
                [
                    'type'  => 'TextBlock',
                    'text'  => $esCritico ? '🔴 TICKET CRÍTICO — Atención inmediata' : '🎫 Nueva Solicitud de Soporte',
                    'weight'=> 'Bolder',
                    'size'  => 'Medium',
                    'color' => $esCritico ? 'Attention' : 'Accent',
                ],
                [
                    'type'  => 'TextBlock',
                    'text'  => "Solicitud registrada para <at>{$ticket->solicitante->nombre}</at>",
                    'wrap'  => true,
                ],
                [
                    'type'  => 'FactSet',
                    'facts' => [
                        ['title' => 'Número:',    'value' => $ticket->numero],
                        ['title' => 'Título:',    'value' => $ticket->titulo],
                        ['title' => 'Categoría:', 'value' => $ticket->categoria->icono . ' ' . $ticket->categoria->nombre],
                        ['title' => 'Prioridad:', 'value' => ucfirst($ticket->prioridad)],
                        ['title' => 'Estado:',    'value' => $ticket->estado_label],
                        ['title' => 'SLA:',       'value' => $ticket->fecha_limite?->format('d/m/Y H:i') ?? '—'],
                    ],
                ],
                [
                    'type' => 'TextBlock',
                    'text' => '📝 ' . Str::limit($ticket->descripcion, 150),
                    'wrap' => true,
                ],
            ],
            'actions' => [$this->accionVerTicket($ticket)],
        ]);
    }

    // ── TÉCNICO ASIGNADO — notifica al solicitante ────────────────────────────
    public function notificarTecnicoAsignado(\App\Models\Ticket $ticket): void
    {
        $this->enviar([
            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'    => 'AdaptiveCard',
            'version' => '1.4',
            'msteams' => ['entities' => [[
                'type'      => 'mention',
                'text'      => "<at>{$ticket->solicitante->nombre}</at>",
                'mentioned' => ['id' => $ticket->solicitante->correo, 'name' => $ticket->solicitante->nombre],
            ]]],
            'body' => [
                [
                    'type'  => 'TextBlock',
                    'text'  => '👨‍💻 Técnico Asignado a tu Solicitud',
                    'weight'=> 'Bolder',
                    'size'  => 'Medium',
                    'color' => 'Accent',
                ],
                [
                    'type' => 'TextBlock',
                    'text' => "<at>{$ticket->solicitante->nombre}</at>, tu solicitud fue asignada a un técnico.",
                    'wrap' => true,
                ],
                [
                    'type'  => 'FactSet',
                    'facts' => [
                        ['title' => 'Número:',  'value' => $ticket->numero],
                        ['title' => 'Título:',  'value' => $ticket->titulo],
                        ['title' => 'Técnico:', 'value' => $ticket->tecnico?->nombre ?? '—'],
                        ['title' => 'Estado:',  'value' => $ticket->estado_label],
                    ],
                ],
            ],
            'actions' => [$this->accionVerTicket($ticket)],
        ]);
    }

    // ── CAMBIO DE ESTADO — notifica al solicitante ────────────────────────────
    public function notificarCambioEstado(\App\Models\Ticket $ticket, string $estadoAnterior): void
    {
        $emoji = match($ticket->estado) {
            'en_proceso' => '🔧',
            'pendiente'  => '⏸️',
            'resuelto'   => '✅',
            'cerrado'    => '🔒',
            default      => '🔄',
        };

        $this->enviar([
            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'    => 'AdaptiveCard',
            'version' => '1.4',
            'msteams' => ['entities' => [[
                'type'      => 'mention',
                'text'      => "<at>{$ticket->solicitante->nombre}</at>",
                'mentioned' => ['id' => $ticket->solicitante->correo, 'name' => $ticket->solicitante->nombre],
            ]]],
            'body' => [
                [
                    'type'  => 'TextBlock',
                    'text'  => "{$emoji} Actualización en tu Solicitud",
                    'weight'=> 'Bolder',
                    'size'  => 'Medium',
                    'color' => 'Accent',
                ],
                [
                    'type' => 'TextBlock',
                    'text' => "<at>{$ticket->solicitante->nombre}</at>, el estado de tu solicitud fue actualizado.",
                    'wrap' => true,
                ],
                [
                    'type'  => 'FactSet',
                    'facts' => [
                        ['title' => 'Número:',          'value' => $ticket->numero],
                        ['title' => 'Título:',          'value' => $ticket->titulo],
                        ['title' => 'Estado anterior:', 'value' => $estadoAnterior],
                        ['title' => 'Estado actual:',   'value' => $ticket->estado_label],
                        ['title' => 'Técnico:',         'value' => $ticket->tecnico?->nombre ?? 'Sin asignar'],
                    ],
                ],
            ],
            'actions' => [$this->accionVerTicket($ticket)],
        ]);
    }

    // ── NUEVA ACTUALIZACIÓN/COMENTARIO — notifica al solicitante ─────────────
    public function notificarActualizacion(\App\Models\Ticket $ticket, string $comentario): void
    {
        $this->enviar([
            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'    => 'AdaptiveCard',
            'version' => '1.4',
            'msteams' => ['entities' => [[
                'type'      => 'mention',
                'text'      => "<at>{$ticket->solicitante->nombre}</at>",
                'mentioned' => ['id' => $ticket->solicitante->correo, 'name' => $ticket->solicitante->nombre],
            ]]],
            'body' => [
                [
                    'type'  => 'TextBlock',
                    'text'  => '💬 Nueva Actualización en tu Solicitud',
                    'weight'=> 'Bolder',
                    'size'  => 'Medium',
                    'color' => 'Accent',
                ],
                [
                    'type' => 'TextBlock',
                    'text' => "<at>{$ticket->solicitante->nombre}</at>, el técnico publicó una actualización.",
                    'wrap' => true,
                ],
                [
                    'type'  => 'FactSet',
                    'facts' => [
                        ['title' => 'Número:', 'value' => $ticket->numero],
                        ['title' => 'Título:', 'value' => $ticket->titulo],
                    ],
                ],
                [
                    'type'  => 'TextBlock',
                    'text'  => '💬 ' . Str::limit($comentario, 200),
                    'wrap'  => true,
                    'color' => 'Accent',
                ],
            ],
            'actions' => [$this->accionVerTicket($ticket)],
        ]);
    }

    // ── TICKET RESUELTO — notifica al solicitante ─────────────────────────────
    public function notificarResuelto(\App\Models\Ticket $ticket): void
    {
        $this->enviar([
            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'    => 'AdaptiveCard',
            'version' => '1.4',
            'msteams' => ['entities' => [[
                'type'      => 'mention',
                'text'      => "<at>{$ticket->solicitante->nombre}</at>",
                'mentioned' => ['id' => $ticket->solicitante->correo, 'name' => $ticket->solicitante->nombre],
            ]]],
            'body' => [
                [
                    'type'  => 'TextBlock',
                    'text'  => '✅ Tu Solicitud Fue Resuelta',
                    'weight'=> 'Bolder',
                    'size'  => 'Medium',
                    'color' => 'Good',
                ],
                [
                    'type' => 'TextBlock',
                    'text' => "<at>{$ticket->solicitante->nombre}</at>, tu solicitud ha sido resuelta satisfactoriamente.",
                    'wrap' => true,
                ],
                [
                    'type'  => 'FactSet',
                    'facts' => [
                        ['title' => 'Número:',  'value' => $ticket->numero],
                        ['title' => 'Título:',  'value' => $ticket->titulo],
                        ['title' => 'Técnico:', 'value' => $ticket->tecnico?->nombre ?? '—'],
                        ['title' => 'Hora:',    'value' => now()->format('d/m/Y H:i')],
                        $ticket->nota_cierre ? ['title' => 'Nota:', 'value' => Str::limit($ticket->nota_cierre, 150)] : null,
                    ],
                ],
            ],
            'actions' => [$this->accionVerTicket($ticket)],
        ]);
    }

    // ── TICKET REABIERTO ──────────────────────────────────────────────────────
    public function notificarReabierto(\App\Models\Ticket $ticket): void
    {
        $this->enviar([
            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'    => 'AdaptiveCard',
            'version' => '1.4',
            'body'    => [
                ['type' => 'TextBlock', 'text' => '🔄 Ticket Reabierto', 'weight' => 'Bolder', 'size' => 'Medium', 'color' => 'Warning'],
                ['type' => 'FactSet', 'facts' => [
                    ['title' => 'Número:', 'value' => $ticket->numero],
                    ['title' => 'Título:', 'value' => $ticket->titulo],
                    ['title' => 'Motivo:', 'value' => 'El problema persiste según el solicitante'],
                ]],
            ],
            'actions' => [$this->accionVerTicket($ticket)],
        ]);
    }

    // ── SLA VENCIDO — notifica al canal ───────────────────────────────────────
    public function notificarSLAVencido(\App\Models\Ticket $ticket): void
    {
        $this->enviar([
            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'    => 'AdaptiveCard',
            'version' => '1.4',
            'body'    => [
                ['type' => 'TextBlock', 'text' => '⚠️ SLA VENCIDO', 'weight' => 'Bolder', 'size' => 'Medium', 'color' => 'Warning'],
                ['type' => 'FactSet', 'facts' => [
                    ['title' => 'Número:',  'value' => $ticket->numero],
                    ['title' => 'Título:',  'value' => $ticket->titulo],
                    ['title' => 'Técnico:', 'value' => $ticket->tecnico?->nombre ?? 'Sin asignar'],
                    ['title' => 'Venció:',  'value' => $ticket->fecha_limite?->format('d/m/Y H:i') ?? '—'],
                    ['title' => 'Hace:',    'value' => $ticket->fecha_limite?->diffForHumans() ?? '—'],
                ]],
            ],
            'actions' => [$this->accionVerTicket($ticket)],
        ]);
    }
}
