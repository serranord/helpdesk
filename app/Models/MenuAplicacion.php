<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MenuAplicacion extends Model {
    protected $table    = 'menu_aplicaciones';
    protected $fillable = ['nombre','url','icono','orden','activo','nueva_ventana'];
    protected $casts    = ['activo' => 'boolean', 'nueva_ventana' => 'boolean'];

    public static function activos() {
        return static::where('activo', true)->orderBy('orden')->get();
    }
}
