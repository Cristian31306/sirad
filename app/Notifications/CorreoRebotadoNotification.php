<?php

namespace App\Notifications;

use App\Models\Radicado;
use App\Models\Responsable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CorreoRebotadoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $radicado;
    public $responsable;
    public $tipoRebote;

    public function __construct(Radicado $radicado, Responsable $responsable, string $tipoRebote = 'hard_bounce')
    {
        $this->radicado = $radicado;
        $this->responsable = $responsable;
        $this->tipoRebote = $tipoRebote;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tipo = $this->tipoRebote === 'hard_bounce' ? 'Rebote definitivo (dirección inexistente o mal escrita)' : 'Rebote temporal (buzón lleno o error de servidor)';

        return (new MailMessage)
            ->subject('⚠️ Alerta: Correo rebotado en Radicado ' . $this->radicado->numero_radicado)
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Brevo reportó que la notificación por correo electrónico **no pudo ser entregada** al funcionario asignado.')
            ->line('**Radicado:** ' . $this->radicado->numero_radicado)
            ->line('**Asunto:** ' . $this->radicado->asunto)
            ->line('**Funcionario:** ' . $this->responsable->nombre . ' (' . $this->responsable->correo . ')')
            ->line('**Tipo de fallo:** ' . $tipo)
            ->action('Revisar Radicado en SIRAD', route('radicados.show', $this->radicado))
            ->line('Por favor verifique que la dirección de correo del responsable esté correcta en el sistema o contáctelo por un canal alternativo.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'radicado_id' => $this->radicado->id,
            'numero_radicado' => $this->radicado->numero_radicado,
            'responsable' => $this->responsable->nombre,
            'correo' => $this->responsable->correo,
            'mensaje' => 'Correo rebotado para el radicado ' . $this->radicado->numero_radicado . ' (' . $this->responsable->nombre . ')',
            'url' => route('radicados.show', $this->radicado),
        ];
    }
}
