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

    public function test_can_upload_multiple_response_files_via_signed_url(): void
    {
        $storage = Storage::fake('local');
        Notification::fake();

        $file1 = UploadedFile::fake()->create('oficio_respuesta_legal.pdf', 1024, 'application/pdf');
        $file2 = UploadedFile::fake()->create('anexo_resolucion.docx', 512, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $signedPostUrl = URL::signedRoute('radicados.public.respuesta.store', [
            'radicado' => $this->radicado->id,
            'responsable' => $this->responsable->id,
        ]);

        $response = $this->post($signedPostUrl, [
            'archivos_salida' => [$file1, $file2],
        ]);

        $response->assertStatus(200);
        $response->assertSee('Respuesta Registrada con Éxito');
        $response->assertSee('oficio_respuesta_legal.pdf');
        $response->assertSee('anexo_resolucion.docx');

        $this->radicado->refresh();
        $this->assertEquals('completado', $this->radicado->estado);
        $this->assertNotNull($this->radicado->fecha_salida);

        $salidas = $this->radicado->adjuntos()->where('tipo', 'salida')->get();
        $this->assertCount(2, $salidas);
        $this->assertTrue($salidas->pluck('nombre_original')->contains('oficio_respuesta_legal.pdf'));
        $this->assertTrue($salidas->pluck('nombre_original')->contains('anexo_resolucion.docx'));

        foreach ($salidas as $salida) {
            $storage->assertExists($salida->path);
        }
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
