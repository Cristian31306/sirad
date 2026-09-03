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
    public $nota;

    public function __construct(Radicado $radicado, Responsable $responsable, array $nombresArchivos = [], ?string $nota = null)
    {
        $this->radicado = $radicado;
        $this->responsable = $responsable;
        $this->nombresArchivos = $nombresArchivos;
        $this->nota = $nota;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('✅ Respuesta lista para revisión - Radicado: ' . $this->radicado->numero_radicado)
            ->greeting('Hola, ' . $notifiable->name)
            ->line('El responsable **' . $this->responsable->nombre . '** ha finalizado los documentos y ha marcado la respuesta del radicado **' . $this->radicado->numero_radicado . '** como **LISTA PARA REVISIÓN Y CIERRE**.')
            ->line('**Asunto del trámite:** ' . $this->radicado->asunto);

        if (!empty($this->nota)) {
            $mail->line('**Nota u observación del responsable:**');
            $mail->line('"' . $this->nota . '"');
        }

        if (!empty($this->nombresArchivos)) {
            $mail->line('**Archivos adjuntados:**');
            foreach ($this->nombresArchivos as $nombre) {
                $mail->line('• ' . $nombre);
            }
        }

        return $mail
            ->action('Revisar y Completar en SIRAD', route('radicados.show', $this->radicado))
            ->line('Ya puedes ingresar a SIRAD a verificar los archivos y cerrar formalmente el trámite.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'radicado_id' => $this->radicado->id,
            'numero_radicado' => $this->radicado->numero_radicado,
            'mensaje' => 'El responsable ' . $this->responsable->nombre . ' marcó la respuesta como LISTA PARA REVISIÓN.',
            'nota' => $this->nota,
            'archivos' => $this->nombresArchivos,
            'url' => route('radicados.show', $this->radicado),
        ];
    }
}
