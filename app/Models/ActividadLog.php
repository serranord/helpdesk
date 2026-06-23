<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActividadLog extends Model
{
    protected $table    = 'actividad_log';
    protected $fillable = ['usuario_id','accion','modulo','descripcion','referencia','ip'];

    public static function registrar(string $accion, string $modulo, string $descripcion, ?string $referencia = null): void {
        static::create([
            'usuario_id'  => Auth::id(),
            'accion'      => $accion,
            'modulo'      => $modulo,
            'descripcion' => $descripcion,
            'referencia'  => $referencia,
            'ip'          => Request::ip(),
        ]);
    }

    public function usuario() { return $this->belongsTo(Usuario::class); }
}
