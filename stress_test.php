<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\TipoTramite;
use App\Models\User;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Iniciando prueba de estrés del sistema SIRAD...\n";

// 1. Crear Responsables masivamente
echo "Generando 50 Responsables...\n";
$responsablesIds = [];
for ($i = 0; $i < 50; $i++) {
    $resp = Responsable::create([
        'nombre' => 'Responsable Stress ' . $i,
        'correo' => 'stress'.$i.'@test.com',
        'especialidad' => 'Test',
    ]);
    $responsablesIds[] = $resp->id;
}

// 2. Crear Tipos de Trámite masivamente
echo "Generando 20 Tipos de Trámite...\n";
$tiposIds = [];
for ($i = 0; $i < 20; $i++) {
    $tipo = TipoTramite::create([
        'nombre' => 'Trámite Stress ' . $i,
        'dias_habiles' => rand(5, 30),
    ]);
    $tiposIds[] = $tipo->id;
}

// 3. Crear Radicados masivamente (500) y adjuntar responsables
echo "Generando 500 Radicados con transacciones...\n";
$start = microtime(true);
\Illuminate\Support\Facades\DB::beginTransaction();
try {
    for ($i = 0; $i < 500; $i++) {
        $radicado = Radicado::create([
            'numero_radicado' => 'STRESS-RAD-' . Str::uuid(),
            'fecha_radicacion' => now()->subDays(rand(1, 30))->toDateString(),
            'remitente' => 'Remitente Stress ' . $i,
            'empresa' => 'Empresa Stress',
            'asunto' => 'Asunto de prueba de estrés ' . $i,
            'tipo_tramite_id' => $tiposIds[array_rand($tiposIds)],
            'medio' => 'Correo Electrónico',
            'prioridad' => 'Alta',
            'fecha_limite' => now()->addDays(rand(1, 30))->toDateString(),
            'estado' => 'pendiente',
        ]);
        
        $radicado->responsables()->attach([
            $responsablesIds[array_rand($responsablesIds)],
            $responsablesIds[array_rand($responsablesIds)]
        ]);
    }
    \Illuminate\Support\Facades\DB::commit();
    $time = microtime(true) - $start;
    echo "500 Radicados creados exitosamente en " . round($time, 2) . " segundos.\n";
} catch (\Exception $e) {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Error en transacción de radicados: " . $e->getMessage() . "\n";
}

// 4. Probar Auditoría (Contar total)
$totalAuditorias = \App\Models\Auditoria::count();
echo "Total de registros de auditoría generados automáticamente: " . $totalAuditorias . "\n";

// 5. Correr comando de vencimientos
echo "Ejecutando proceso de vencimientos y encolado de correos (colas)...\n";
Artisan::call('radicados:check-vencimientos');
echo "Comando ejecutado.\n";

echo "Limpiando base de datos generada...\n";
// Borrar lo generado
Radicado::where('numero_radicado', 'like', 'STRESS-RAD-%')->forceDelete();
Responsable::where('nombre', 'like', 'Responsable Stress %')->forceDelete();
TipoTramite::where('nombre', 'like', 'Trámite Stress %')->forceDelete();

echo "¡Prueba de estrés finalizada!\n";
