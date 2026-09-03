<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\User;
use App\Notifications\CorreoRebotadoNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class BrevoWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Webhook Brevo recibido:', ['payload' => $payload]);

        // Brevo envia events como array o un solo object.
        $events = isset($payload['items']) ? $payload['items'] : (isset($payload[0]) ? $payload : [$payload]);

        $reboteEvents = ['hard_bounce', 'soft_bounce', 'blocked', 'invalid_email', 'invalid', 'error', 'complaint', 'spam'];

        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }

            $eventType = $event['event'] ?? '';
            $email = strtolower(trim($event['email'] ?? ''));
            $subject = $event['subject'] ?? '';

            Log::info("Webhook Brevo evento recibido: Tipo={$eventType}, Correo={$email}, Asunto={$subject}");

            if (in_array($eventType, $reboteEvents)) {
                $numeroRadicado = null;

                // 1. Extraer número de radicado del asunto (cualquier formato: RAD-..., 2026-..., etc.)
                if (preg_match('/Nueva Radicación Asignada:\s*(.*)$/ui', $subject, $matches)) {
                    $numeroRadicado = trim($matches[1]);
                } elseif (preg_match('/(RAD-[A-Za-z0-9\-]+)/i', $subject, $matches)) {
                    $numeroRadicado = trim($matches[1]);
                }

                $radicado = null;
                if ($numeroRadicado) {
                    $radicado = Radicado::where('numero_radicado', $numeroRadicado)->first();
                }

                // 2. Buscar al responsable por correo
                $responsable = Responsable::whereRaw('LOWER(TRIM(correo)) = ?', [$email])->first();

                // 3. Si no se encontró radicado por asunto pero sí el responsable, tomar su radicado más reciente
                if (!$radicado && $responsable) {
                    $radicado = $responsable->radicados()->latest()->first();
                }

                if ($radicado && $responsable) {
                    $radicado->responsables()->updateExistingPivot($responsable->id, [
                        'hubo_rebote' => true,
                        'fecha_rebote' => Carbon::now(),
                    ]);

                    Log::info("Webhook Brevo: Rebote ({$eventType}) registrado para radicado {$radicado->numero_radicado} y responsable {$responsable->correo}.");

                    // Enviar correo de alerta a usuarios operativos (rol 'usuario') o admin
                    $usuarios = User::where('role', 'usuario')->get();
                    if ($usuarios->isEmpty()) {
                        $usuarios = User::where('role', 'admin')->get();
                    }

                    if ($usuarios->isNotEmpty()) {
                        Notification::send($usuarios, new CorreoRebotadoNotification($radicado, $responsable, $eventType));
                        Log::info("Webhook Brevo: Notificación de rebote enviada a ".count($usuarios)." usuario(s).");
                    }
                } else {
                    Log::warning("Webhook Brevo: No se pudo asociar el rebote. Radicado=".($radicado ? $radicado->numero_radicado : 'NO ENCONTRADO').", Responsable=".($responsable ? $responsable->correo : 'NO ENCONTRADO'));
                }
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
