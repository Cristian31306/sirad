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
use Illuminate\Support\Facades\Storage;

class AlertaVencimientoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [60, 300, 900, 1800]; // Reintentos exponenciales en segundos

    public $radicado;
    public $responsable;
    public $diasFaltantes;

    public function __construct(Radicado $radicado, $responsable = null, ?int $diasFaltantes = null)
    {
        $this->radicado = $radicado;
        $this->responsable = $responsable;
        $this->diasFaltantes = $diasFaltantes;
    }

    public function envelope(): Envelope
    {
        $tipoDiasTexto = ($this->radicado->tipoTramite && $this->radicado->tipoTramite->tipo_dias === 'calendario') ? 'días cal.' : 'días háb.';
        $subject = $this->radicado->estado === 'vencido'
            ? '⚠️ TRÁMITE VENCIDO: ' . $this->radicado->numero_radicado
            : '⏰ ALERTA: ' . $this->radicado->numero_radicado . ' (restan ' . ($this->diasFaltantes ?? 2) . ' ' . $tipoDiasTexto . ')';

        return new Envelope(
            subject: $subject,
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

        foreach ($this->radicado->adjuntos()->where('tipo', 'entrada')->get() as $adjunto) {
            if (Storage::disk('local')->exists($adjunto->path)) {
                $attachments[] = Attachment::fromPath(Storage::disk('local')->path($adjunto->path))
                    ->as($adjunto->nombre_original);
            }
        }

        return $attachments;
    }
}
