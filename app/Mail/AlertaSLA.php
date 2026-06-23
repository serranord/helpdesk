<?php
namespace App\Mail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertaSLA extends Mailable {
    use Queueable, SerializesModels;
    public function __construct(public Ticket $ticket, public bool $vencido = false) {}

    public function envelope(): Envelope {
        $asunto = $this->vencido
            ? "🔴 SLA VENCIDO — {$this->ticket->numero}"
            : "⚠️ SLA próximo a vencer — {$this->ticket->numero}";
        return new Envelope(subject: $asunto);
    }
    public function content(): Content {
        return new Content(view: 'emails.alerta-sla');
    }
}
