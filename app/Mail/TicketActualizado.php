<?php
namespace App\Mail;
use App\Models\Ticket; use App\Models\Comentario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketActualizado extends Mailable {
    use Queueable, SerializesModels;
    public function __construct(public Ticket $ticket, public Comentario $comentario) {}
    public function envelope(): Envelope {
        return new Envelope(subject: "🔧 Actualización en tu solicitud — {$this->ticket->numero}");
    }
    public function content(): Content {
        return new Content(view: 'emails.ticket-actualizado');
    }
}
