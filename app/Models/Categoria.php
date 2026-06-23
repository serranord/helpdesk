<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table    = 'categorias';
    protected $fillable = ['nombre','descripcion','icono','sla_horas','activa'];
    protected $casts    = ['activa' => 'boolean'];

    public function tickets() {
        return $this->hasMany(Ticket::class);
    }

    public function kb_articulos() {
        return $this->hasMany(KbArticulo::class);
    }
}