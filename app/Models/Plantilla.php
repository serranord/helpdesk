<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model {
    protected $table    = 'plantillas';
    protected $fillable = ['nombre','titulo','descripcion','categoria_id','prioridad','creado_por','activa'];
    protected $casts    = ['activa' => 'boolean'];

    public function categoria()  { return $this->belongsTo(Categoria::class); }
    public function creadoPor()  { return $this->belongsTo(Usuario::class, 'creado_por'); }
}
