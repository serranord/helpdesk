<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TicketVinculado extends Model {
    protected $table    = 'tickets_vinculados';
    protected $fillable = ['ticket_padre_id','ticket_hijo_id'];

    public function padre() { return $this->belongsTo(Ticket::class, 'ticket_padre_id'); }
    public function hijo()  { return $this->belongsTo(Ticket::class, 'ticket_hijo_id'); }
}
