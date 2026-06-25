<?php
namespace App\Services;

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
                    'content'     => array_filter($card),
                ]]
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Teams webhook error: ' . $e->getMessage());
            return false;
        }
    }

    public function notificarTicketNuevo(\App\Models\Ticket $ticket): void
    {
        $this->enviar([
            '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'    => 'AdaptiveCard',
            'version' => '1.4',
            'body'    => [
                ['type'=>'TextBlock','text'=>'🎫 Nuevo Ticket de Soporte','weight'=>'Bolder','size'=>'Medium','color'=>'Accent'],
                ['type'=>'FactSet','facts'=>[
                    ['title'=>'Número:',   'value'=>$ticket->numero],
                    ['title'=>'Título:',   'value'=>$ticket->titulo],
                    ['title'=>'Categoría:','value'=>$ticket->categoria->icono.' '.$ticket->categoria->nombre],
                    ['title'=>'Prioridad:','value'=>ucfirst($ticket->prioridad)],
                    ['title'=>'SLA:',      'value'=>$ticket->fecha_limite?->format('d/m/Y H:i') ?? '—'],
                ]],
                ['type'=>'TextBlock','text'=>'📝 '.Str::limit($ticket->descripcion,150),'wrap'=>true],
            ],
            'actions'=>[['type'=>'Action.OpenUrl','title'=>'Ver ticket →','url'=>route('tickets.show',$ticket)]],
        ]);
    }

    public function notificarTicketCritico(\App\Models\Ticket $ticket): void
    {
        $this->enviar([
            '$schema'=>'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'=>'AdaptiveCard','version'=>'1.4',
            'body'=>[
                ['type'=>'TextBlock','text'=>'🔴 TICKET CRÍTICO — Atención inmediata','weight'=>'Bolder','size'=>'Medium','color'=>'Attention'],
                ['type'=>'FactSet','facts'=>[
                    ['title'=>'Número:',   'value'=>$ticket->numero],
                    ['title'=>'Título:',   'value'=>$ticket->titulo],
                    ['title'=>'Categoría:','value'=>$ticket->categoria->icono.' '.$ticket->categoria->nombre],
                    ['title'=>'SLA:',      'value'=>$ticket->fecha_limite?->format('d/m/Y H:i') ?? '—'],
                ]],
            ],
            'actions'=>[['type'=>'Action.OpenUrl','title'=>'🚨 Atender ahora →','url'=>route('tickets.show',$ticket)]],
        ]);
    }

    public function notificarSLAVencido(\App\Models\Ticket $ticket): void
    {
        $this->enviar([
            '$schema'=>'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'=>'AdaptiveCard','version'=>'1.4',
            'body'=>[
                ['type'=>'TextBlock','text'=>'⚠️ SLA VENCIDO','weight'=>'Bolder','size'=>'Medium','color'=>'Warning'],
                ['type'=>'FactSet','facts'=>[
                    ['title'=>'Número:', 'value'=>$ticket->numero],
                    ['title'=>'Título:', 'value'=>$ticket->titulo],
                    ['title'=>'Técnico:','value'=>$ticket->tecnico?->nombre ?? 'Sin asignar'],
                    ['title'=>'Venció:', 'value'=>$ticket->fecha_limite?->format('d/m/Y H:i') ?? '—'],
                    ['title'=>'Hace:',   'value'=>$ticket->fecha_limite?->diffForHumans() ?? '—'],
                ]],
            ],
            'actions'=>[['type'=>'Action.OpenUrl','title'=>'Ver ticket →','url'=>route('tickets.show',$ticket)]],
        ]);
    }

    public function notificarResuelto(\App\Models\Ticket $ticket): void
    {
        $this->enviar([
            '$schema'=>'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'=>'AdaptiveCard','version'=>'1.4',
            'body'=>[
                ['type'=>'TextBlock','text'=>'✅ Ticket Resuelto','weight'=>'Bolder','size'=>'Medium','color'=>'Good'],
                ['type'=>'FactSet','facts'=>[
                    ['title'=>'Número:',  'value'=>$ticket->numero],
                    ['title'=>'Título:',  'value'=>$ticket->titulo],
                    ['title'=>'Técnico:', 'value'=>$ticket->tecnico?->nombre ?? '—'],
                    ['title'=>'Hora:',    'value'=>now()->format('d/m/Y H:i')],
                ]],
            ],
            'actions'=>[['type'=>'Action.OpenUrl','title'=>'Ver ticket →','url'=>route('tickets.show',$ticket)]],
        ]);
    }

    public function notificarReabierto(\App\Models\Ticket $ticket): void
    {
        $this->enviar([
            '$schema'=>'http://adaptivecards.io/schemas/adaptive-card.json',
            'type'=>'AdaptiveCard','version'=>'1.4',
            'body'=>[
                ['type'=>'TextBlock','text'=>'🔄 Ticket Reabierto','weight'=>'Bolder','size'=>'Medium','color'=>'Warning'],
                ['type'=>'FactSet','facts'=>[
                    ['title'=>'Número:','value'=>$ticket->numero],
                    ['title'=>'Título:','value'=>$ticket->titulo],
                    ['title'=>'Motivo:','value'=>'El problema persiste según el solicitante'],
                ]],
            ],
            'actions'=>[['type'=>'Action.OpenUrl','title'=>'Ver ticket →','url'=>route('tickets.show',$ticket)]],
        ]);
    }
}
