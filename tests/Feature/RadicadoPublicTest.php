<?php

namespace Tests\Feature;

use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\TipoTramite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RadicadoPublicTest extends TestCase
{
    use RefreshDatabase;

    protected $tipoTramite;
    protected $responsable;
    protected $radicado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tipoTramite = TipoTramite::create([
            'nombre' => 'Petición General',
            'dias_habiles' => 15,
            'tipo_dias' => 'habiles',
        ]);

        $this->responsable = Responsable::create([
            'nombre' => 'Dra. Laura Vega',
            'correo' => 'laura@sirad.gov.co',
            'especialidad' => 'Jurídica',
        ]);

        $this->radicado = Radicado::create([
            'numero_radicado' => 'RAD-PUB-2026-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Ciudadano Andrés',
            'asunto' => 'Solicitud de concepto legal',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'medio' => 'Correo Electrónico',
            'prioridad' => 'Alta',
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $this->radicado->responsables()->attach($this->responsable->id);
    }

    public function test_can_view_public_form_and_incoming_attachments_with_signed_url(): void
    {
        $storage = Storage::fake('local');

        $path1 = 'radicados/entradas/peticion_inicial.pdf';
        $storage->put($path1, 'dummy pdf');
        $adj1 = $this->radicado->adjuntos()->create([
            'tipo' => 'entrada',
            'path' => $path1,
            'nombre_original' => 'peticion_inicial.pdf',
        ]);

        $path2 = 'radicados/entradas/anexo_soporte.xlsx';
        $storage->put($path2, 'dummy xlsx');
        $adj2 = $this->radicado->adjuntos()->create([
            'tipo' => 'entrada',
            'path' => $path2,
            'nombre_original' => 'anexo_soporte.xlsx',
        ]);

        $signedUrl = URL::signedRoute('radicados.public.respuesta', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable->id,
        ]);

        $response = $this->get($signedUrl);
        $response->assertStatus(200);
        $response->assertSee('RAD-PUB-2026-01');
        $response->assertSee('peticion_inicial.pdf');
        $response->assertSee('anexo_soporte.xlsx');
        $response->assertSee('Descargar todos (.ZIP)');

        // Probar ver archivo de entrada con firma
        $verUrl = URL::signedRoute('radicados.public.adjuntos.ver', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable->id,
            'adjunto' => $adj1->id,
        ]);
        $verResponse = $this->get($verUrl);
        $verResponse->assertStatus(200);

        // Probar descargar archivo de entrada con firma
        $descargarUrl = URL::signedRoute('radicados.public.adjuntos.descargar', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable->id,
            'adjunto' => $adj2->id,
        ]);
        $descargarResponse = $this->get($descargarUrl);
        $descargarResponse->assertStatus(200);

        // Probar descargar todos en ZIP
        $zipUrl = URL::signedRoute('radicados.public.adjuntos.descargar-todos', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable->id,
        ]);
        $zipResponse = $this->get($zipUrl);
        $zipResponse->assertStatus(200);
        $this->assertStringContainsString('application/zip', strtolower($zipResponse->headers->get('Content-Type')));
    }

    public function test_can_upload_multiple_response_files_without_completing_and_continue_uploading(): void
    {
        $storage = Storage::fake('local');
        Notification::fake();

        $usuarioOperativo = User::factory()->create([
            'email' => 'operario@sirad.gov.co',
            'role' => 'usuario',
        ]);

        $file1 = UploadedFile::fake()->create('oficio_respuesta_legal.pdf', 1024, 'application/pdf');
        $file2 = UploadedFile::fake()->create('anexo_resolucion.docx', 512, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $signedPostUrl = URL::signedRoute('radicados.public.respuesta.store', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable->id,
        ]);

        // 1. Primera carga de 2 archivos
        $response = $this->post($signedPostUrl, [
            'archivos_salida' => [$file1, $file2],
        ]);

        $response->assertRedirect($signedPostUrl);

        // El radicado NO debe marcarse como completado
        $this->radicado->refresh();
        $this->assertEquals('pendiente', $this->radicado->estado);
        $this->assertNull($this->radicado->fecha_salida);

        // Se verifica que se notificó a los usuarios con rol 'usuario'
        Notification::assertSentTo($usuarioOperativo, \App\Notifications\RespuestaSubidaNotification::class);

        // 2. Comprobar que al volver al formulario se ven los 2 archivos y se puede seguir subiendo
        $signedGetUrl = URL::signedRoute('radicados.public.respuesta', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable->id,
        ]);
        $getResponse = $this->get($signedGetUrl);
        $getResponse->assertStatus(200);
        $getResponse->assertSee('oficio_respuesta_legal.pdf');
        $getResponse->assertSee('anexo_resolucion.docx');
        $getResponse->assertSee('Adjuntar Documentos Adicionales');

        // 3. Segunda carga de otro archivo adicional
        $file3 = UploadedFile::fake()->create('adicional_soporte.pdf', 256, 'application/pdf');
        $response2 = $this->post($signedPostUrl, [
            'archivos_salida' => [$file3],
        ]);
        $response2->assertRedirect($signedPostUrl);

        $salidas = $this->radicado->adjuntos()->where('tipo', 'salida')->get();
        $this->assertCount(3, $salidas);
        $this->assertTrue($salidas->pluck('nombre_original')->contains('adicional_soporte.pdf'));

        // 4. Cuando el equipo interno formalmente completa el radicado
        $this->radicado->update(['estado' => 'completado', 'fecha_salida' => Carbon::today()->toDateString()]);

        $completedResponse = $this->get($signedGetUrl);
        $completedResponse->assertStatus(200);
        $completedResponse->assertSee('¡Respuesta Registrada con Éxito!');
    }

    public function test_unsigned_urls_are_forbidden(): void
    {
        $unsignedUrl = route('radicados.public.respuesta', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable->id,
        ]);

        $response = $this->get($unsignedUrl);
        $response->assertStatus(403);
    }
}
