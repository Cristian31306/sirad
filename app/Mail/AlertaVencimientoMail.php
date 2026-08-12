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

    public $radicado;

    /**
     * Create a new message instance.
     */
    public function __construct(Radicado $radicado)
    {
        $this->radicado = $radicado;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ALERTA DE VENCIMIENTO: '.$this->radicado->numero_radicado,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.alerta_vencimiento',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
