<?php

namespace App\Console\Commands;

use App\Mail\AlertaVencimientoMail;
use App\Models\Radicado;
use App\Models\User;
use App\Services\DiasHabilesService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('radicados:check-vencimientos')]
#[Description('Verifica los radicados próximos a vencer y dispara alertas')]
class CheckVencimientos extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DiasHabilesService $service)
    {
        $pendientes = Radicado::with('responsables', 'tipoTramite')->whereIn('estado', ['pendiente', 'alerta'])->get();
        $hoy = Carbon::now();

        foreach ($pendientes as $radicado) {
            $fechaLimite = Carbon::parse($radicado->fecha_limite)->endOfDay();

            // Destinatarios según regla de negocio
            $usuarios = User::where('role', 'usuario')->pluck('email')->toArray();
            $todos = User::whereIn('role', ['admin', 'usuario'])->pluck('email')->toArray();

            if ($hoy->greaterThan($fechaLimite)) {
                if ($radicado->estado !== 'vencido') {
                    $radicado->update(['estado' => 'vencido']);

                    foreach ($radicado->responsables as $responsable) {
                        if ($responsable->correo) {
                            try {
                                Mail::to($responsable->correo)
                                    ->cc($todos)
                                    ->queue(new AlertaVencimientoMail($radicado, $responsable));
                            } catch (\Exception $e) {
                                \Log::error('Mail Error Vencido: '.$e->getMessage());
                            }
                        }
                    }
                }

                continue;
            }

            // Contar días faltantes según si es trámite en días calendario o hábiles
            $esCalendario = $radicado->tipoTramite && $radicado->tipoTramite->tipo_dias === 'calendario';

            if ($esCalendario) {
                // Para días calendario: diferencia directa en días
                $diasFaltantes = (int) max(0, $hoy->diffInDays($fechaLimite, false));
            } else {
                // Para días hábiles: contar de lunes a viernes excluyendo festivos
                $diasFaltantes = 0;
                $fechaTemp = $hoy->copy()->startOfDay();
                $fechaLimiteTemp = $fechaLimite->copy()->startOfDay();

                while ($fechaTemp->lessThan($fechaLimiteTemp)) {
                    $fechaTemp->addDay();
                    if ($service->esDiaHabil($fechaTemp)) {
                        $diasFaltantes++;
                    }
                }
            }

            if ($diasFaltantes <= 5 && $radicado->estado === 'pendiente') {
                $radicado->update(['estado' => 'alerta']);

                // Correos
                foreach ($radicado->responsables as $responsable) {
                    if ($responsable->correo) {
                        try {
                            Mail::to($responsable->correo)
                                ->cc($usuarios)
                                ->queue(new AlertaVencimientoMail($radicado, $responsable));
                        } catch (\Exception $e) {
                            \Log::error('Mail Error Alerta: '.$e->getMessage());
                        }
                    }
                }
            }
        }

        $this->info('Vencimientos verificados correctamente.');
    }
}
