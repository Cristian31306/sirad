<?php

namespace App\Services;

use App\Models\Festivo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiasHabilesService
{
    /**
     * Calcula la fecha límite en días hábiles a partir de una fecha inicial
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
     * Verifica si una fecha es día hábil en Colombia (excluye fines de semana y festivos)
     */
    public function esDiaHabil(Carbon $fecha): bool
    {
        // 1. Si es fin de semana (Sábado o Domingo), no es hábil
        if ($fecha->isWeekend()) {
            return false;
        }

        // 2. Garantizar que los festivos del año de la fecha estén cargados en la base de datos
        $this->asegurarFestivosAno($fecha->year);

        // 3. Consultar si la fecha está registrada como festivo
        return ! Festivo::whereDate('fecha', $fecha->format('Y-m-d'))->exists();
    }

    /**
     * Asegura de manera autónoma que existan los festivos del año especificado
     */
    public function asegurarFestivosAno(int $year): void
    {
        if (Festivo::whereYear('fecha', $year)->exists()) {
            return;
        }

        $this->sincronizarFestivos($year);
    }

    /**
     * Sincroniza festivos de Colombia consumiendo la API con fallback legal autónomo (Ley Emiliani)
     */
    public function sincronizarFestivos(int $year): void
    {
        try {
            $response = Http::timeout(4)->withoutVerifying()->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/CO");

            if ($response->successful()) {
                $festivos = $response->json();
                foreach ($festivos as $festivo) {
                    $fecha = Carbon::parse($festivo['date'])->format('Y-m-d');
                    $existente = Festivo::whereDate('fecha', $fecha)->first();

                    if ($existente) {
                        if (! str_contains($existente->descripcion, $festivo['localName'])) {
                            $existente->update([
                                'descripcion' => $existente->descripcion.' / '.$festivo['localName'],
                            ]);
                        }
                    } else {
                        Festivo::create([
                            'fecha' => $fecha,
                            'descripcion' => $festivo['localName'],
                        ]);
                    }
                }

                return;
            }
        } catch (\Exception $e) {
            Log::info("API de festivos inaccesible para {$year}, utilizando generador normativo colombiano: ".$e->getMessage());
        }

        // Fallback robusto conforme a la Ley 51 de 1983 (Ley Emiliani de Colombia)
        $this->generarFestivosLeyEmiliani($year);
    }

    /**
     * Generador normativo de festivos oficiales de Colombia (Ley Emiliani + Pascua)
     */
    private function generarFestivosLeyEmiliani(int $year): void
    {
        $festivos = [];

        // 1. Festivos Inamovibles (Fijos)
        $festivos[] = ['fecha' => "{$year}-01-01", 'descripcion' => 'Año Nuevo'];
        $festivos[] = ['fecha' => "{$year}-05-01", 'descripcion' => 'Día del Trabajo'];
        $festivos[] = ['fecha' => "{$year}-07-20", 'descripcion' => 'Día de la Independencia'];
        $festivos[] = ['fecha' => "{$year}-08-07", 'descripcion' => 'Batalla de Boyacá'];
        $festivos[] = ['fecha' => "{$year}-12-08", 'descripcion' => 'Inmaculada Concepción'];
        $festivos[] = ['fecha' => "{$year}-12-25", 'descripcion' => 'Navidad'];

        // 2. Festivos trasladados al siguiente lunes (Ley 51 de 1983)
        $festivos[] = ['fecha' => $this->siguienteLunes(Carbon::create($year, 1, 6)), 'descripcion' => 'Día de los Reyes Magos'];
        $festivos[] = ['fecha' => $this->siguienteLunes(Carbon::create($year, 3, 19)), 'descripcion' => 'Día de San José'];
        $festivos[] = ['fecha' => $this->siguienteLunes(Carbon::create($year, 6, 29)), 'descripcion' => 'San Pedro y San Pablo'];
        $festivos[] = ['fecha' => $this->siguienteLunes(Carbon::create($year, 8, 15)), 'descripcion' => 'La Asunción de la Virgen'];
        $festivos[] = ['fecha' => $this->siguienteLunes(Carbon::create($year, 10, 12)), 'descripcion' => 'Día de la Raza'];
        $festivos[] = ['fecha' => $this->siguienteLunes(Carbon::create($year, 11, 1)), 'descripcion' => 'Día de Todos los Santos'];
        $festivos[] = ['fecha' => $this->siguienteLunes(Carbon::create($year, 11, 11)), 'descripcion' => 'Independencia de Cartagena'];

        // 3. Festivos calculados a partir del Domingo de Pascua (Semana Santa y fiestas móviles)
        $pascua = $this->calcularDomingoPascua($year);

        $juevesSanto = $pascua->copy()->subDays(3);
        $viernesSanto = $pascua->copy()->subDays(2);
        $ascension = $this->siguienteLunes($pascua->copy()->addDays(39));
        $corpusChristi = $this->siguienteLunes($pascua->copy()->addDays(60));
        $sagradoCorazon = $this->siguienteLunes($pascua->copy()->addDays(68));

        $festivos[] = ['fecha' => $juevesSanto->format('Y-m-d'), 'descripcion' => 'Jueves Santo'];
        $festivos[] = ['fecha' => $viernesSanto->format('Y-m-d'), 'descripcion' => 'Viernes Santo'];
        $festivos[] = ['fecha' => $ascension, 'descripcion' => 'Ascensión del Señor'];
        $festivos[] = ['fecha' => $corpusChristi, 'descripcion' => 'Corpus Christi'];
        $festivos[] = ['fecha' => $sagradoCorazon, 'descripcion' => 'Sagrado Corazón de Jesús'];

        foreach ($festivos as $f) {
            $existente = Festivo::whereDate('fecha', $f['fecha'])->first();
            if ($existente) {
                if (! str_contains($existente->descripcion, $f['descripcion'])) {
                    $existente->update(['descripcion' => $existente->descripcion.' / '.$f['descripcion']]);
                }
            } else {
                Festivo::create([
                    'fecha' => $f['fecha'],
                    'descripcion' => $f['descripcion'],
                ]);
            }
        }
    }

    /**
     * Mueve una fecha al siguiente lunes si no cae en lunes
     */
    private function siguienteLunes(Carbon $fecha): string
    {
        if ($fecha->isMonday()) {
            return $fecha->format('Y-m-d');
        }

        return $fecha->next(Carbon::MONDAY)->format('Y-m-d');
    }

    /**
     * Calcula la fecha del Domingo de Pascua usando el algoritmo de Gauss / Anonymous Gregorian
     */
    private function calcularDomingoPascua(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $mes, $dia);
    }
}
