<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model {
    protected $table    = 'calificaciones';
    protected $fillable = ['ticket_id','usuario_id','estrellas','comentario'];
    public function ticket()  { return $this->belongsTo(Ticket::class); }
    public function usuario() { return $this->belongsTo(Usuario::class); }
}
