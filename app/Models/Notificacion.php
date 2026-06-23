<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model {
    protected $appends  = ['icono'];
    protected $table    = 'notificaciones';
    protected $fillable = ['usuario_id','tipo','titulo','mensaje','url','referencia','leida_en'];
    protected $casts    = ['leida_en' => 'datetime'];

    public function usuario() { return $this->belongsTo(Usuario::class); }
    public function estaLeida(): bool { return !is_null($this->leida_en); }

    public static function crear(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?string $url = null, ?string $referencia = null): void {
        static::create(compact('usuarioId','tipo','titulo','mensaje','url','referencia') + ['usuario_id' => $usuarioId]);
    }

    public function getIconoAttribute(): string {
        return match($this->tipo) {
            'ticket_asignado'   => '👨‍💻',
            'comentario'        => '💬',
            'estado_cambiado'   => '🔄',
            'ticket_nuevo'      => '🎫',
            'sla_vencido'       => '⚠️',
            default             => '🔔',
        };
    }
}
