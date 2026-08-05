<?php

namespace App\Services;

use App\Models\Festivo;
use Carbon\Carbon;

class DiasHabilesService
{
    /**
     * Calcula la fecha límite (15 días hábiles a partir de la fecha dada)
     */
    public function calcularFechaLimite(Carbon $fechaInicial, int $diasHabiles = 15): Carbon
    {
        $fecha = $fechaInicial->copy();
        
        while ($diasHabiles > 0) {
            $fecha->addDay();
            if ($this->esDiaHabil($fecha)) {
                $diasHabiles--;
            }
        }
        
        return $fecha;
    }

    /**
     * Verifica si una fecha es día hábil
     */
    public function esDiaHabil(Carbon $fecha): bool
    {
        // Si es fin de semana, no es hábil
        if ($fecha->isWeekend()) {
            return false;
        }

        // Si es festivo, no es hábil
        if (Festivo::whereDate('fecha', $fecha->format('Y-m-d'))->exists()) {
            return false;
        }

        return true;
    }
}
