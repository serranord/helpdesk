<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Mail\AlertaSLA;
use Illuminate\Support\Facades\Mail;

class RecordarSLA extends Command {
    protected $signature   = 'helpdesk:recordar-sla';
    protected $description = 'Envía alertas de tickets próximos a vencer su SLA';

    public function handle(): void {
        // Tickets que vencen en menos de 2 horas y no están resueltos
        $proximos = Ticket::with(['tecnico','solicitante','categoria'])
            ->whereNotIn('estado', ['resuelto','cerrado'])
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '>', now())
            ->where('fecha_limite', '<=', now()->addHours(2))
            ->get();

        // Tickets ya vencidos
        $vencidos = Ticket::with(['tecnico','solicitante','categoria'])
            ->whereNotIn('estado', ['resuelto','cerrado'])
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '<', now())
            ->get();

        $enviados = 0;
        foreach ($proximos->merge($vencidos) as $ticket) {
            if ($ticket->tecnico) {
                try {
                    Mail::to($ticket->tecnico->correo)->send(new AlertaSLA($ticket, $ticket->estaVencido()));
                    $enviados++;
                } catch (\Exception $e) {}
            }
        }

        $this->info("✅ Alertas SLA enviadas: {$enviados}");
    }
}
