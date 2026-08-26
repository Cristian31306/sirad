<?php

namespace App\Observers;

use App\Models\Radicado;

class RadicadoObserver
{
    /**
     * Handle the Radicado "created" event.
     */
    public function created(Radicado $radicado): void
    {
        \App\Models\Auditoria::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'accion' => 'Creó el radicado',
            'modelo' => 'Radicado',
            'modelo_id' => $radicado->id,
            'detalles' => $radicado->toArray(),
        ]);
    }

    /**
     * Handle the Radicado "updated" event.
     */
    public function updated(Radicado $radicado): void
    {
        $changes = $radicado->getChanges();
        unset($changes['updated_at']);

        if (count($changes) > 0) {
            $accion = 'Actualizó el radicado';
            
            if (isset($changes['estado'])) {
                $accion = 'Cambió el estado del radicado a ' . $changes['estado'];
            } elseif (isset($changes['archivo_salida_path']) && empty($radicado->getOriginal('archivo_salida_path'))) {
                $accion = 'Subió respuesta al radicado';
            }

            \App\Models\Auditoria::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'accion' => $accion,
                'modelo' => 'Radicado',
                'modelo_id' => $radicado->id,
                'detalles' => [
                    'original' => array_intersect_key($radicado->getOriginal(), $changes),
                    'nuevo' => $changes,
                ],
            ]);
        }
    }

    /**
     * Handle the Radicado "deleted" event.
     */
    public function deleted(Radicado $radicado): void
    {
        \App\Models\Auditoria::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'accion' => 'Eliminó el radicado',
            'modelo' => 'Radicado',
            'modelo_id' => $radicado->id,
            'detalles' => $radicado->toArray(),
        ]);
    }

    /**
     * Handle the Radicado "restored" event.
     */
    public function restored(Radicado $radicado): void
    {
        //
    }

    /**
     * Handle the Radicado "force deleted" event.
     */
    public function forceDeleted(Radicado $radicado): void
    {
        //
    }
}
