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

    // SLA (en horas) configurable por el administrador, según la prioridad del ticket
    public static function slaPorPrioridad(): array {
        $default = ['baja' => 72, 'media' => 48, 'alta' => 24, 'critica' => 4];
        $guardado = json_decode(static::get('sla_por_prioridad', ''), true);
        return is_array($guardado) ? array_merge($default, $guardado) : $default;
    }

    public static function setSlaPorPrioridad(array $horas): void {
        static::set('sla_por_prioridad', json_encode([
            'baja'    => (int) ($horas['baja'] ?? 72),
            'media'   => (int) ($horas['media'] ?? 48),
            'alta'    => (int) ($horas['alta'] ?? 24),
            'critica' => (int) ($horas['critica'] ?? 4),
        ]));
    }

    public static function slaHorasPara(string $prioridad): int {
        return static::slaPorPrioridad()[$prioridad] ?? 48;
    }

    // Si está activo, el SLA (automático o manual) salta sábados y domingos
    public static function saltarFinesSemana(): bool {
        return static::get('sla_saltar_fines_semana', '1') === '1';
    }
}
