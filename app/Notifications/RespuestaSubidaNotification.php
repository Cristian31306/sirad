<?php

namespace App\Notifications;

use App\Models\Radicado;
use App\Models\Responsable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RespuestaSubidaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $radicado;
    public $responsable;
    public $nombresArchivos;

    public function __construct(Radicado $radicado, Responsable $responsable, array $nombresArchivos = [])
    {
        $this->radicado = $radicado;
        $this->responsable = $responsable;
        $this->nombresArchivos = $nombresArchivos;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Respuesta recibida - Radicado: ' . $this->radicado->numero_radicado)
            ->greeting('Hola, ' . $notifiable->name)
            ->line('El responsable **' . $this->responsable->nombre . '** ha subido documento(s) de respuesta para el radicado **' . $this->radicado->numero_radicado . '**.')
            ->line('**Asunto del trámite:** ' . $this->radicado->asunto);

        if (!empty($this->nombresArchivos)) {
            $mail->line('**Archivos adjuntados:**');
            foreach ($this->nombresArchivos as $nombre) {
                $mail->line('• ' . $nombre);
            }
        }

        return $mail
            ->action('Revisar Radicado en SIRAD', route('radicados.show', $this->radicado))
            ->line('El radicado permanece en estado **' . ucfirst($this->radicado->estado) . '** para que el equipo operativo revise la documentación y realice el cierre formal cuando corresponda.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'radicado_id' => $this->radicado->id,
            'numero_radicado' => $this->radicado->numero_radicado,
            'mensaje' => 'El responsable ' . $this->responsable->nombre . ' subió documento(s) de respuesta.',
            'archivos' => $this->nombresArchivos,
            'url' => route('radicados.show', $this->radicado),
        ];
    }
}
