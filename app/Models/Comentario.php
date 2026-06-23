<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $table    = 'comentarios';
    protected $fillable = ['ticket_id','usuario_id','contenido','es_interno'];
    protected $casts    = ['es_interno' => 'boolean'];

    public function ticket()  { return $this->belongsTo(Ticket::class); }
    public function usuario() { return $this->belongsTo(Usuario::class); }
}
