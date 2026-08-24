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
            $fechaLimite = Carbon::parse($radicado->fecha_limite);

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
                                    ->queue(new AlertaVencimientoMail($radicado));
                            } catch (\Exception $e) {
                                \Log::error('Mail Error Vencido: '.$e->getMessage());
                            }
                        }
                    }
                }

                continue;
            }

            // Contar días hábiles desde hoy hasta la fecha límite
            $diasFaltantes = 0;
            $fechaTemp = $hoy->copy();

            while ($fechaTemp->lessThan($fechaLimite)) {
                $fechaTemp->addDay();
                if ($service->esDiaHabil($fechaTemp)) {
                    $diasFaltantes++;
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
                                ->queue(new AlertaVencimientoMail($radicado));
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
