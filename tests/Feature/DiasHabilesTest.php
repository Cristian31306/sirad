<?php

namespace Tests\Feature;

use App\Models\Festivo;
use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\TipoTramite;
use App\Models\User;
use App\Services\DiasHabilesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DiasHabilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dias_habiles_skips_weekends(): void
    {
        $service = new DiasHabilesService();

        // Elegimos un viernes (ej: 2026-08-07 es festivo en Colombia, busquemos un viernes cualquiera)
        // 2026-08-14 es Viernes. 1 día hábil después debe ser Lunes 2026-08-17 (si no es festivo) o Martes 2026-08-18 (si el 17 es festivo de la Asunción)
        $viernes = Carbon::create(2026, 8, 14); // Viernes
        $resultado = $service->calcularFechaLimite($viernes, 1);

        // No debe ser sábado ni domingo
        $this->assertFalse($resultado->isWeekend());
    }

    public function test_dias_habiles_skips_registered_festivos(): void
    {
        $service = new DiasHabilesService();

        // Lunes 2026-07-20 (Independencia de Colombia)
        Festivo::create([
            'fecha' => '2026-07-20',
            'descripcion' => 'Día de la Independencia',
        ]);

        $viernesAntes = Carbon::create(2026, 7, 17); // Viernes antes del festivo
        $fechaLimite = $service->calcularFechaLimite($viernesAntes, 1);

        // 1 día hábil después del viernes 17 no puede ser 18 (sáb), ni 19 (dom), ni 20 (festivo) -> debe ser martes 21
        $this->assertEquals('2026-07-21', $fechaLimite->format('Y-m-d'));
    }

    public function test_check_vencimientos_command_updates_states(): void
    {
        $tipo = TipoTramite::firstOrCreate(
            ['nombre' => 'Petición Especial'],
            ['dias_habiles' => 15, 'activo' => true]
        );

        $resp = Responsable::create([
            'nombre' => 'Funcionario Alerta',
            'correo' => 'funcionario@sirad.gov.co',
        ]);

        // 1. Radicado vencido en el pasado
        $rVencido = Radicado::create([
            'numero_radicado' => 'RAD-VENCIDO-01',
            'fecha_radicacion' => Carbon::today()->subDays(30)->toDateString(),
            'remitente' => 'Vencido User',
            'tipo_tramite_id' => $tipo->id,
            'asunto' => 'Prueba vencido',
            'medio' => 'Físico',
            'prioridad' => 'Alta',
            'fecha_limite' => Carbon::today()->subDays(2)->toDateString(),
            'estado' => 'pendiente',
        ]);
        $rVencido->responsables()->attach($resp);

        // 2. Radicado que vence en 2 días (Alerta)
        $rAlerta = Radicado::create([
            'numero_radicado' => 'RAD-ALERTA-01',
            'fecha_radicacion' => Carbon::today()->subDays(13)->toDateString(),
            'remitente' => 'Alerta User',
            'tipo_tramite_id' => $tipo->id,
            'asunto' => 'Prueba alerta',
            'medio' => 'Físico',
            'prioridad' => 'Media',
            'fecha_limite' => Carbon::today()->addDays(2)->toDateString(),
            'estado' => 'pendiente',
        ]);
        $rAlerta->responsables()->attach($resp);

        Artisan::call('radicados:check-vencimientos');

        $rVencido->refresh();
        $rAlerta->refresh();

        $this->assertEquals('vencido', $rVencido->estado);
        $this->assertEquals('alerta', $rAlerta->estado);
    }

    public function test_dias_habiles_auto_syncs_unloaded_year(): void
    {
        $service = new DiasHabilesService();

        $this->assertFalse(Festivo::whereYear('fecha', 2030)->exists());

        $fecha = Carbon::create(2030, 1, 1); // 1 de enero de 2030 es Año Nuevo (Festivo)
        $esHabil = $service->esDiaHabil($fecha);

        $this->assertFalse($esHabil);
        $this->assertTrue(Festivo::whereDate('fecha', '2030-01-01')->where('descripcion', 'Año Nuevo')->exists());
    }
}
