<?php

namespace Tests\Feature;

use App\Mail\NuevaRadicacionMail;
use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\TipoTramite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RadicadoTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $secretaria;
    protected $tipoTramite;
    protected $responsable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'permisos' => [],
            'must_change_password' => false,
        ]);

        $this->secretaria = User::factory()->create([
            'role' => 'usuario',
            'permisos' => [
                'radicados.editar',
                'radicados.anular',
                'radicados.completar',
            ],
            'must_change_password' => false,
        ]);

        $this->tipoTramite = TipoTramite::firstOrCreate(
            ['nombre' => 'Derecho de Petición'],
            ['dias_habiles' => 15, 'activo' => true]
        );

        $this->responsable = Responsable::create([
            'nombre' => 'Ing. Benito Pérez',
            'correo' => 'benito@sirad.gov.co',
            'especialidad' => 'Vías',
        ]);
    }

    public function test_can_view_radicados_index(): void
    {
        $response = $this->actingAs($this->secretaria)->get(route('radicados.index'));

        $response->assertStatus(200);
        $response->assertSee('Historial de Radicados');
    }

    public function test_can_create_new_radicado_with_attachment(): void
    {
        Mail::fake();
        $storage = Storage::fake('local');

        $archivo = UploadedFile::fake()->create('solicitud_comunidad.pdf', 2048, 'application/pdf');

        $data = [
            'numero_radicado' => 'RAD-2026-TEST-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Juan Pérez',
            'empresa' => 'Comunidad Local',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Solicitud de información sobre obras con anexo',
            'medio' => 'Físico',
            'prioridad' => 'Alta',
            'observaciones' => 'Ninguna',
            'responsables' => [$this->responsable->id],
            'archivo_entrada' => $archivo,
        ];

        $response = $this->actingAs($this->secretaria)->post(route('radicados.store'), $data);

        $response->assertRedirect(route('radicados.index'));
        $this->assertDatabaseHas('radicados', [
            'numero_radicado' => 'RAD-2026-TEST-01',
            'remitente' => 'Juan Pérez',
            'archivo_entrada_nombre' => 'solicitud_comunidad.pdf',
            'estado' => 'pendiente',
        ]);

        $radicado = Radicado::where('numero_radicado', 'RAD-2026-TEST-01')->first();
        $storage->assertExists($radicado->archivo_entrada_path);

        Mail::assertQueued(NuevaRadicacionMail::class);
    }

    public function test_can_view_radicado_detail_without_errors(): void
    {
        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-2026-SHOW-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'María Gómez',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Detalle de prueba',
            'medio' => 'Correo Electrónico',
            'prioridad' => 'Media',
            'fecha_limite' => Carbon::today()->addDays(20)->toDateString(),
            'estado' => 'pendiente',
        ]);
        $radicado->responsables()->attach($this->responsable);

        $response = $this->actingAs($this->secretaria)->get(route('radicados.show', $radicado));

        $response->assertStatus(200);
        $response->assertSee('RAD-2026-SHOW-01');
        $response->assertSee('María Gómez');
        $response->assertSee('Ing. Benito Pérez');
    }

    public function test_can_update_radicado(): void
    {
        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-2026-EDIT-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Carlos López',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Asunto original',
            'medio' => 'Físico',
            'prioridad' => 'Baja',
            'fecha_limite' => Carbon::today()->addDays(20)->toDateString(),
            'estado' => 'pendiente',
        ]);
        $radicado->responsables()->attach($this->responsable);

        $updateData = [
            'numero_radicado' => 'RAD-2026-EDIT-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Carlos López Modificado',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Asunto modificado con éxito',
            'medio' => 'Físico',
            'prioridad' => 'Alta',
            'responsables' => [$this->responsable->id],
        ];

        $response = $this->actingAs($this->secretaria)->put(route('radicados.update', $radicado), $updateData);

        $response->assertRedirect(route('radicados.show', $radicado));
        $this->assertDatabaseHas('radicados', [
            'id' => $radicado->id,
            'remitente' => 'Carlos López Modificado',
            'asunto' => 'Asunto modificado con éxito',
            'prioridad' => 'Alta',
        ]);
    }

    public function test_can_complete_and_close_radicado_with_response_file(): void
    {
        $storage = Storage::fake('local');
        $archivoRespuesta = UploadedFile::fake()->create('oficio_respuesta.pdf', 1024, 'application/pdf');

        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-2026-CLOSE-01',
            'fecha_radicacion' => Carbon::today()->subDays(5)->toDateString(),
            'remitente' => 'Ana Ruiz',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Cierre de trámite con respuesta',
            'medio' => 'Físico',
            'prioridad' => 'Media',
            'fecha_limite' => Carbon::today()->addDays(10)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($this->secretaria)->patch(route('radicados.cierre', $radicado), [
            'archivo_salida' => $archivoRespuesta,
        ]);

        $response->assertRedirect(route('radicados.show', $radicado));
        $radicado->refresh();
        $this->assertEquals('completado', $radicado->estado);
        $this->assertNotNull($radicado->fecha_salida);
        $this->assertEquals('oficio_respuesta.pdf', $radicado->archivo_salida_nombre);
        $storage->assertExists($radicado->archivo_salida_path);
    }

    public function test_can_download_and_preview_attachments(): void
    {
        $storage = Storage::fake('local');
        $path = 'radicados/entradas/test_doc.pdf';
        $storage->put($path, 'dummy content');

        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-2026-ATT-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Test User',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Test attachments',
            'medio' => 'Portal Web',
            'prioridad' => 'Media',
            'archivo_entrada_path' => $path,
            'archivo_entrada_nombre' => 'test_doc.pdf',
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);

        // 1. Descargar
        $descargaResponse = $this->actingAs($this->secretaria)->get(route('radicados.archivo.descargar', [$radicado, 'entrada']));
        $descargaResponse->assertStatus(200);

        // 2. Previsualizar
        $verResponse = $this->actingAs($this->secretaria)->get(route('radicados.archivo.ver', [$radicado, 'entrada']));
        $verResponse->assertStatus(200);
    }

    public function test_can_anular_radicado_with_reason(): void
    {
        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-2026-ANULAR-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'David Morales',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Radicado duplicado',
            'medio' => 'Físico',
            'prioridad' => 'Baja',
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($this->secretaria)->patch(route('radicados.anular', $radicado), [
            'motivo_anulacion' => 'Documento ingresado por duplicidad.',
        ]);

        $response->assertRedirect(route('radicados.index'));
        $radicado->refresh();
        $this->assertEquals('anulado', $radicado->estado);
        $this->assertEquals('Documento ingresado por duplicidad.', $radicado->motivo_anulacion);
        $this->assertEquals($this->secretaria->id, $radicado->anulado_por);
    }

    public function test_can_export_radicados_to_csv(): void
    {
        Radicado::create([
            'numero_radicado' => 'RAD-2026-EXP-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Export User',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Export test',
            'medio' => 'Portal Web',
            'prioridad' => 'Alta',
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($this->secretaria)->get(route('radicados.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', strtolower($response->headers->get('Content-Type')));
    }
}
