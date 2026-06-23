<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistorialTicket extends Model {
    protected $table    = 'historial_ticket';
    protected $fillable = ['ticket_id','usuario_id','accion','campo','valor_anterior','valor_nuevo','descripcion'];

    public function ticket()  { return $this->belongsTo(Ticket::class); }
    public function usuario() { return $this->belongsTo(Usuario::class); }

    public static function registrar(int $ticketId, string $accion, string $descripcion, ?string $campo = null, ?string $anterior = null, ?string $nuevo = null): void {
        static::create([
            'ticket_id'       => $ticketId,
            'usuario_id'      => auth()->id(),
            'accion'          => $accion,
            'campo'           => $campo,
            'valor_anterior'  => $anterior,
            'valor_nuevo'     => $nuevo,
            'descripcion'     => $descripcion,
        ]);
    }

    public function getIconoAttribute(): string {
        return match($this->accion) {
            'creado'     => '🎫',
            'estado'     => '🔄',
            'asignacion' => '👨‍💻',
            'prioridad'  => '🚨',
            'comentario' => '💬',
            'resolucion' => '✅',
            default      => '📝',
        };
    }
}
