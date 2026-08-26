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

    public function __construct(Radicado $radicado, Responsable $responsable)
    {
        $this->radicado = $radicado;
        $this->responsable = $responsable;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Respuesta subida para Radicado: ' . $this->radicado->numero_radicado)
            ->greeting('Hola, ' . $notifiable->name)
            ->line('El responsable **' . $this->responsable->nombre . '** ha subido un documento de respuesta para el radicado **' . $this->radicado->numero_radicado . '**.')
            ->action('Ver Radicado', route('radicados.show', $this->radicado))
            ->line('Por favor, revise el documento y asigne el estado correspondiente.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'radicado_id' => $this->radicado->id,
            'numero_radicado' => $this->radicado->numero_radicado,
            'mensaje' => 'El responsable ' . $this->responsable->nombre . ' subió una respuesta.',
            'url' => route('radicados.show', $this->radicado),
        ];
    }
}
