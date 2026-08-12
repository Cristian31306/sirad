<?php

namespace App\Console\Commands;

use App\Models\Festivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncFestivosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sirad:sync-festivos {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los días festivos de Colombia usando una API pública';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? date('Y');

        $this->info("Obteniendo festivos de Colombia para el año {$year}...");

        $response = Http::withoutVerifying()->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/CO");

        if ($response->successful()) {
            $festivos = $response->json();
            $count = 0;

            foreach ($festivos as $festivo) {
                Festivo::updateOrCreate(
                    ['fecha' => $festivo['date']],
                    ['descripcion' => $festivo['localName']]
                );
                $count++;
            }

            $this->info("¡Completado! Se sincronizaron {$count} festivos para el año {$year}.");
        } else {
            $this->error('No se pudo obtener la información de la API. Código de estado: '.$response->status());
        }
    }
}
