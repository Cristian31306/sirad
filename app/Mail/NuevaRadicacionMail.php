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

class NuevaRadicacionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [60, 300, 900, 1800]; // Reintentos exponenciales en segundos

    public $radicado;
    public $responsable;

    /**
     * Create a new message instance.
     */
    public function __construct(Radicado $radicado, $responsable = null)
    {
        $this->radicado = $radicado;
        $this->responsable = $responsable;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Radicación Asignada: '.$this->radicado->numero_radicado,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nueva_radicacion',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        $totalSize = 0;
        $maxTotalSize = 15 * 1024 * 1024; // 15MB límite para correos

        $adjuntosEntrada = $this->radicado->adjuntos()->where('tipo', 'entrada')->get();

        foreach ($adjuntosEntrada as $adjunto) {
            if ($adjunto->path && \Illuminate\Support\Facades\Storage::disk('local')->exists($adjunto->path)) {
                $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($adjunto->path);
                $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;

                if ($totalSize + $fileSize <= $maxTotalSize) {
                    $attachments[] = Attachment::fromPath($fullPath)
                        ->as($adjunto->nombre_original ?: basename($adjunto->path));
                    $totalSize += $fileSize;
                }
            }
        }

        return $attachments;
    }
}
