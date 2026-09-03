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

    public function test_soft_deleted_responsable_remains_visible_in_radicado(): void
    {
        $adjunto = $this->radicado->adjuntos()->create([
            'tipo' => 'salida',
            'path' => 'test/path.pdf',
            'nombre_original' => 'informe.pdf',
            'responsable_id' => $this->responsable1->id,
        ]);

        $nota = $this->radicado->notas()->create([
            'responsable_id' => $this->responsable1->id,
            'autor_nombre' => $this->responsable1->nombre,
            'contenido' => 'Nota de prueba antes de ser eliminado del catalogo',
        ]);

        $this->radicado->update([
            'respuesta_marcada_por' => $this->responsable1->id,
        ]);

        // Simular que el responsable es eliminado del catálogo general (soft delete)
        $this->responsable1->delete();
        $this->assertSoftDeleted('responsables', ['id' => $this->responsable1->id]);

        // Verificar que en el radicado sigue apareciendo en responsables, adjuntos y notas
        $this->radicado->refresh();
        $this->assertTrue($this->radicado->responsables->contains('id', $this->responsable1->id));
        $this->assertNotNull($this->radicado->respuestaMarcadaPor);
        $this->assertEquals('Carlos Mendoza', $this->radicado->respuestaMarcadaPor->nombre);

        $this->assertNotNull($adjunto->fresh()->responsable);
        $this->assertEquals('Carlos Mendoza', $adjunto->fresh()->responsable->nombre);

        $this->assertNotNull($nota->fresh()->responsable);
        $this->assertEquals('Carlos Mendoza', $nota->fresh()->responsable->nombre);
    }

    public function test_check_vencimientos_command_smart_threshold(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        // Crear trámite corto de 5 días calendario (ej: Tutela)
        $tipoCorto = \App\Models\TipoTramite::create([
            'nombre' => 'TUTELA TEST',
            'dias_habiles' => 5,
            'tipo_dias' => 'calendario',
            'activo' => true,
        ]);

        // Caso 1: Radicado creado hoy con 4 días restantes -> NO debe entrar en alerta (umbral es 2 días)
        $radicadoNuevo = \App\Models\Radicado::create([
            'numero_radicado' => 'RAD-TUTELA-NUEVO',
            'fecha_radicacion' => now()->toDateString(),
            'fecha_limite' => now()->addDays(4)->toDateString(),
            'remitente' => 'Juzgado 1',
            'tipo_tramite_id' => $tipoCorto->id,
            'medio' => 'Correo Electrónico',
            'prioridad' => 'alta',
            'estado' => 'pendiente',
            'asunto' => 'Tutela recién llegada',
        ]);
        $radicadoNuevo->responsables()->attach($this->responsable1->id);

        // Caso 2: Radicado con 2 días restantes -> SÍ debe entrar en alerta
        $radicadoUrgente = \App\Models\Radicado::create([
            'numero_radicado' => 'RAD-TUTELA-URGENTE',
            'fecha_radicacion' => now()->subDays(3)->toDateString(),
            'fecha_limite' => now()->addDays(2)->toDateString(),
            'remitente' => 'Juzgado 2',
            'tipo_tramite_id' => $tipoCorto->id,
            'medio' => 'Correo Electrónico',
            'prioridad' => 'alta',
            'estado' => 'pendiente',
            'asunto' => 'Tutela a 2 días de vencer',
        ]);
        $radicadoUrgente->responsables()->attach($this->responsable1->id);

        $this->artisan('radicados:check-vencimientos')->assertSuccessful();

        $radicadoNuevo->refresh();
        $radicadoUrgente->refresh();

        $this->assertEquals('pendiente', $radicadoNuevo->estado);
        $this->assertEquals('alerta', $radicadoUrgente->estado);
    }

    public function test_check_vencimientos_sends_last_day_alert_to_admin_and_users(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        // Crear un usuario admin
        $admin = \App\Models\User::create([
            'name' => 'Jefe Admin',
            'email' => 'jefe@algorah.bond',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
        ]);

        $tipo = \App\Models\TipoTramite::create([
            'nombre' => 'DERECHO PETICION TEST',
            'dias_habiles' => 15,
            'tipo_dias' => 'habiles',
            'activo' => true,
        ]);

        // Radicado que vence HOY
        $radicadoHoy = \App\Models\Radicado::create([
            'numero_radicado' => 'RAD-VENCE-HOY',
            'fecha_radicacion' => now()->subDays(15)->toDateString(),
            'fecha_limite' => now()->toDateString(),
            'remitente' => 'Ciudadano Preocupado',
            'tipo_tramite_id' => $tipo->id,
            'medio' => 'Físico',
            'prioridad' => 'urgente',
            'estado' => 'alerta',
            'asunto' => 'Trámite que vence hoy mismo',
        ]);
        $radicadoHoy->responsables()->attach($this->responsable1->id);

        $this->artisan('radicados:check-vencimientos')->assertSuccessful();

        $radicadoHoy->refresh();
        $this->assertTrue($radicadoHoy->alerta_ultimo_dia_enviada);

        // Verificar que se envió correo al responsable con copia al admin (jefe) y al usuario operativo
        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\AlertaVencimientoMail::class, function ($mail) use ($admin) {
            return $mail->diasFaltantes === 0 &&
                   $mail->hasCc($admin->email) &&
                   $mail->hasCc($this->userOperativo->email);
        });
    }
}
