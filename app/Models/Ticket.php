<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ticket extends Model
{
    protected $table    = 'tickets';
    protected $fillable = [
        'numero','titulo','descripcion','prioridad','estado','origen',
        'categoria_id','solicitante_id','tecnico_id','creado_por',
        'fecha_limite','fecha_resolucion','nota_cierre',
    ];
    protected $casts = ['fecha_limite' => 'datetime', 'fecha_resolucion' => 'datetime'];

    public function categoria()    { return $this->belongsTo(Categoria::class); }
    public function solicitante()  { return $this->belongsTo(Usuario::class, 'solicitante_id'); }
    public function tecnico()      { return $this->belongsTo(Usuario::class, 'tecnico_id'); }
    public function creadoPor()    { return $this->belongsTo(Usuario::class, 'creado_por'); }
    public function comentarios()  { return $this->hasMany(Comentario::class)->orderBy('created_at'); }
    public function calificacion() { return $this->hasOne(Calificacion::class); }
    public function historial()    { return $this->hasMany(HistorialTicket::class)->orderBy('created_at'); }
    public function adjuntos()     { return $this->hasMany(Adjunto::class)->orderBy('created_at'); }

    public function estaVencido(): bool {
        return $this->fecha_limite && now()->gt($this->fecha_limite)
            && !in_array($this->estado, ['resuelto','cerrado']);
    }

    public static function estados(): array {
        return [
            'nuevo'      => 'Nuevo',
            'abierto'    => 'Abierto',
            'asignado'   => 'Asignado',
            'en_proceso' => 'En Proceso',
            'pendiente'  => 'Pendiente',
            'resuelto'   => 'Resuelto',
            'cerrado'    => 'Cerrado',
        ];
    }

    public function getEstadoLabelAttribute(): string {
        return static::estados()[$this->estado] ?? ucfirst($this->estado);
    }

    public function getPrioridadColorAttribute(): string {
        return match($this->prioridad) {
            'baja'    => 'green',
            'media'   => 'blue',
            'alta'    => 'amber',
            'critica' => 'red',
            default   => 'gray',
        };
    }

    public function getEstadoColorAttribute(): string {
        return match($this->estado) {
            'nuevo'      => 'cyan',
            'abierto'    => 'blue',
            'asignado'   => 'purple',
            'en_proceso' => 'orange',
            'pendiente'  => 'amber',
            'resuelto'   => 'green',
            'cerrado'    => 'gray',
            default      => 'gray',
        };
    }

    public static function generarNumero(): string {
        $ultimo = static::orderByDesc('id')->value('numero');
        $n = $ultimo ? ((int) substr($ultimo, 4)) + 1 : 1;
        return 'TKT-' . str_pad($n, 5, '0', STR_PAD_LEFT);
    }
}