<?php

namespace App\Mail;

use App\Models\Radicado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertaVencimientoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [60, 300, 900, 1800]; // Reintentos exponenciales en segundos

    public $radicado;
    public $responsable;

    public function __construct(Radicado $radicado, $responsable = null)
    {
        $this->radicado = $radicado;
        $this->responsable = $responsable;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ALERTA DE VENCIMIENTO: '.$this->radicado->numero_radicado,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerta_vencimiento',
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->radicado->hasArchivoEntrada()) {
            $path = storage_path('app/public/' . $this->radicado->archivo_entrada_path);
            if (file_exists($path)) {
                $attachments[] = Attachment::fromPath($path)
                    ->as($this->radicado->archivo_entrada_nombre);
            }
        }

        return $attachments;
    }
}
