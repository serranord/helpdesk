<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Mail\AlertaSLA;
use App\Services\TeamsService;
use Illuminate\Support\Facades\Mail;

class RecordarSLA extends Command {
    protected $signature   = 'helpdesk:recordar-sla';
    protected $description = 'Envía alertas de tickets próximos a vencer su SLA';

    public function handle(): void {
        $teams = new TeamsService();

        // Tickets ya vencidos
        $vencidos = Ticket::with(['tecnico','solicitante','categoria'])
            ->whereNotIn('estado', ['resuelto','cerrado'])
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '<', now())
            ->get();

        // Tickets que vencen en menos de 2 horas
        $proximos = Ticket::with(['tecnico','solicitante','categoria'])
            ->whereNotIn('estado', ['resuelto','cerrado'])
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '>', now())
            ->where('fecha_limite', '<=', now()->addHours(2))
            ->get();

        $enviados = 0;

        foreach ($vencidos as $ticket) {
            // Correo al técnico
            if ($ticket->tecnico) {
                try { Mail::to($ticket->tecnico->correo)->send(new AlertaSLA($ticket, true)); $enviados++; } catch (\Exception $e) {}
            }
            // Teams — solo notifica una vez (cuando acaba de vencer hace menos de 1 hora)
            if ($ticket->fecha_limite->diffInMinutes(now()) <= 60) {
                try { $teams->notificarSLAVencido($ticket); } catch (\Exception $e) {}
            }
        }

        foreach ($proximos as $ticket) {
            if ($ticket->tecnico) {
                try { Mail::to($ticket->tecnico->correo)->send(new AlertaSLA($ticket, false)); $enviados++; } catch (\Exception $e) {}
            }
        }

        $this->info("✅ Alertas SLA enviadas: {$enviados} | Vencidos notificados en Teams: {$vencidos->count()}");
    }
}
