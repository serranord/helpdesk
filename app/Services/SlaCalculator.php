<?php
namespace App\Services;

use App\Models\Configuracion;
use Carbon\Carbon;

class SlaCalculator
{
    /**
     * Suma horas "laborables" a una fecha. Si la configuración de saltar fines de semana
     * está activa, salta por completo los sábados y domingos; si no, suma horas normales.
     */
    public static function agregarHorasLaborables(Carbon $desde, int $horas): Carbon
    {
        if (!Configuracion::saltarFinesSemana()) {
            return $desde->copy()->addHours($horas);
        }

        $fecha = $desde->copy();

        // Si arrancamos en fin de semana, saltar al lunes a la misma hora
        while ($fecha->isWeekend()) {
            $fecha->addDay();
        }

        for ($i = 0; $i < $horas; $i++) {
            $fecha->addHour();
            while ($fecha->isWeekend()) {
                $fecha->addDay();
            }
        }

        return $fecha;
    }

    /**
     * Ajusta una fecha puesta manualmente (por un admin) para que no caiga en fin de semana,
     * si la configuración de saltar fines de semana está activa. La mueve al lunes siguiente
     * a la misma hora, sin restar ni sumar horas de más.
     */
    public static function ajustarSiCaeEnFinDeSemana(Carbon $fecha): Carbon
    {
        if (!Configuracion::saltarFinesSemana()) return $fecha;

        $ajustada = $fecha->copy();
        while ($ajustada->isWeekend()) {
            $ajustada->addDay();
        }
        return $ajustada;
    }

    /**
     * Horas laborables restantes entre ahora y una fecha límite (para mostrar "tiempo restante"
     * sin contar fines de semana). Devuelve 0 si ya venció.
     */
    public static function horasLaborablesRestantes(Carbon $fechaLimite): int
    {
        $ahora = now();
        if ($ahora->gte($fechaLimite)) return 0;

        $horas = 0;
        $cursor = $ahora->copy();
        while ($cursor->lt($fechaLimite)) {
            $cursor->addHour();
            if (!$cursor->isWeekend()) $horas++;
        }
        return $horas;
    }

    /** ¿Hoy el SLA está en pausa (es fin de semana y la config está activa)? */
    public static function enPausaHoy(): bool
    {
        return Configuracion::saltarFinesSemana() && now()->isWeekend();
    }
}

