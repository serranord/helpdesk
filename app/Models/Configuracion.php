<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model {
    protected $table    = 'configuracion';
    protected $fillable = ['clave','valor','descripcion'];

    public static function get(string $clave, $default = null) {
        $config = static::where('clave', $clave)->first();
        return $config ? $config->valor : $default;
    }

    public static function set(string $clave, $valor): void {
        static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
    }

    public static function registroHabilitado(): bool {
        return static::get('registro_habilitado', '1') === '1';
    }

    public static function soloSSO(): bool {
        return static::get('solo_sso', '0') === '1';
    }
}
