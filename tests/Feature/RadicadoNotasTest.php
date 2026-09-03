<?php

namespace Tests\Feature;

use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\TipoTramite;
use App\Models\User;
use App\Notifications\RespuestaSubidaNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RadicadoNotasTest extends TestCase
{
    use RefreshDatabase;

    protected $tipoTramite;
    protected $responsable1;
    protected $responsable2;
    protected $radicado;
    protected $userOperativo;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->userOperativo = User::factory()->create([
            'role' => 'usuario',
            'permisos' => ['radicados.editar'],
        ]);

        $this->tipoTramite = TipoTramite::create([
            'nombre' => 'Petición',
            'dias_habiles' => 10,
            'tipo_dias' => 'habiles',
        ]);

        $this->responsable1 = Responsable::create([
            'nombre' => 'Carlos Mendoza',
            'correo' => 'carlos@sirad.gov.co',
            'especialidad' => 'Jurídica',
        ]);

        $this->responsable2 = Responsable::create([
            'nombre' => 'Jennifer Barrera',
            'correo' => 'jennifer@sirad.gov.co',
            'especialidad' => 'Técnica',
        ]);

        $this->radicado = Radicado::create([
            'numero_radicado' => 'RAD-2026-NOTAS-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Ciudadano Ejemplo',
            'asunto' => 'Solicitud de informe conjunto',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'medio' => 'Físico',
            'prioridad' => 'Media',
            'fecha_limite' => Carbon::today()->addDays(10)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $this->radicado->responsables()->attach([
            $this->responsable1->id,
            $this->responsable2->id,
        ]);
    }

    public function test_responsable_can_save_advance_without_sending_email_to_correspondencia(): void
    {
        Notification::fake();

        $signedUrl = URL::signedRoute('radicados.public.respuesta.store', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable1->id,
        ]);

        $file = UploadedFile::fake()->create('borrador_juridico.pdf', 500, 'application/pdf');

        $response = $this->post($signedUrl, [
            'archivos_salida' => [$file],
            'nota' => 'Subo el borrador de jurídica, quedo a la espera del concepto técnico de Jennifer.',
            'estado_entrega' => 'avance',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->radicado->refresh();
        $this->assertEquals('en_tramite', $this->radicado->estado_respuesta);

        // Adjunto guardado con responsable_id
        $adjunto = $this->radicado->adjuntos()->where('tipo', 'salida')->first();
        $this->assertNotNull($adjunto);
        $this->assertEquals($this->responsable1->id, $adjunto->responsable_id);

        // Nota guardada con autor
        $nota = $this->radicado->notas()->first();
        $this->assertNotNull($nota);
        $this->assertEquals($this->responsable1->id, $nota->responsable_id);
        $this->assertEquals('Carlos Mendoza', $nota->autor_nombre);
        $this->assertStringContainsString('borrador de jurídica', $nota->contenido);

        // NO se debe enviar notificación por correo a correspondencia en avances
        Notification::assertNothingSent();
    }

    public function test_responsable_can_finalize_response_and_notifies_correspondencia(): void
    {
        Notification::fake();

        $signedUrl = URL::signedRoute('radicados.public.respuesta.store', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable2->id,
        ]);

        $file = UploadedFile::fake()->create('informe_final_firmado.pdf', 800, 'application/pdf');

        $response = $this->post($signedUrl, [
            'archivos_salida' => [$file],
            'nota' => 'Documento final revisado y con firmas completas. Listo para radicar la salida.',
            'estado_entrega' => 'finalizar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->radicado->refresh();
        $this->assertEquals('lista_para_revision', $this->radicado->estado_respuesta);
        $this->assertEquals($this->responsable2->id, $this->radicado->respuesta_marcada_por);
        $this->assertNotNull($this->radicado->fecha_respuesta_marcada);

        // SÍ se debe notificar al usuario operativo por correo
        Notification::assertSentTo($this->userOperativo, RespuestaSubidaNotification::class, function ($notif) {
            return $notif->nota === 'Documento final revisado y con firmas completas. Listo para radicar la salida.';
        });
    }

    public function test_portal_user_can_add_note_to_radicado(): void
    {
        $response = $this->actingAs($this->userOperativo)->post(route('radicados.notas.store', $this->radicado), [
            'contenido' => 'Nota interna: Se solicita al equipo acelerar la entrega antes del viernes.',
        ]);

        $response->assertRedirect(route('radicados.show', $this->radicado));
        $response->assertSessionHas('success');

        $this->radicado->refresh();
        $nota = $this->radicado->notas()->first();
        $this->assertNotNull($nota);
        $this->assertEquals($this->userOperativo->id, $nota->user_id);
        $this->assertStringContainsString('acelerar la entrega', $nota->contenido);
    }
}
