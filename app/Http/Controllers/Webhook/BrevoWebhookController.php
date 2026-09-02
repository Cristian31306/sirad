<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Radicado;
use App\Models\Responsable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BrevoWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        // Brevo envia events como array o un solo object.
        $events = isset($payload['items']) ? $payload['items'] : (isset($payload[0]) ? $payload : [$payload]);

        foreach ($events as $event) {
            $eventType = $event['event'] ?? '';
            $email = $event['email'] ?? '';
            $subject = $event['subject'] ?? '';

            if (in_array($eventType, ['hard_bounce', 'soft_bounce'])) {
                // El subject es "Nueva Radicación Asignada: RAD-..."
                if (preg_match('/Nueva Radicación Asignada: (RAD-[A-Za-z0-9\-]+)/', $subject, $matches)) {
                    $numeroRadicado = $matches[1];

                    $radicado = Radicado::where('numero_radicado', $numeroRadicado)->first();
                    $responsable = Responsable::where('correo', $email)->first();

                    if ($radicado && $responsable) {
                        $radicado->responsables()->updateExistingPivot($responsable->id, [
                            'hubo_rebote' => true,
                            'fecha_rebote' => Carbon::now(),
                        ]);

                        Log::info("Webhook Brevo: Rebote registrado para radicado {$numeroRadicado} y responsable {$email}.");
                    }
                }
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
