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
            'archivos_entrada' => [$archivo],
        ];

        $response = $this->actingAs($this->secretaria)->post(route('radicados.store'), $data);

        $response->assertRedirect(route('radicados.index'));
        $this->assertDatabaseHas('radicados', [
            'numero_radicado' => 'RAD-2026-TEST-01',
            'remitente' => 'Juan Pérez',
            'estado' => 'pendiente',
        ]);

        $radicado = Radicado::where('numero_radicado', 'RAD-2026-TEST-01')->first();
        $this->assertCount(1, $radicado->adjuntos);
        $this->assertEquals('solicitud_comunidad.pdf', $radicado->adjuntos->first()->nombre_original);
        $storage->assertExists($radicado->adjuntos->first()->path);

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
            'archivos_salida' => [$archivoRespuesta],
        ]);

        $response->assertRedirect(route('radicados.show', $radicado));
        $radicado->refresh();
        $this->assertEquals('completado', $radicado->estado);
        $this->assertNotNull($radicado->fecha_salida);
        $this->assertCount(1, $radicado->adjuntos);
        $this->assertEquals('oficio_respuesta.pdf', $radicado->adjuntos->first()->nombre_original);
        $storage->assertExists($radicado->adjuntos->first()->path);
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
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $adjunto = $radicado->adjuntos()->create([
            'tipo' => 'entrada',
            'path' => $path,
            'nombre_original' => 'test_doc.pdf',
        ]);

        // 1. Descargar
        $descargaResponse = $this->actingAs($this->secretaria)->get(route('radicados.archivo.descargar', $adjunto));
        $descargaResponse->assertStatus(200);

        // 2. Previsualizar
        $verResponse = $this->actingAs($this->secretaria)->get(route('radicados.archivo.ver', $adjunto));
        $verResponse->assertStatus(200);

        // 3. Descargar todos (.zip)
        $zipResponse = $this->actingAs($this->secretaria)->get(route('radicados.adjuntos.descargar-todos', $radicado));
        $zipResponse->assertStatus(200);
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

    public function test_can_upload_and_manage_multiple_attachments_and_download_zip(): void
    {
        $storage = Storage::fake('local');
        Mail::fake();

        $file1 = UploadedFile::fake()->create('documento_principal.pdf', 1024, 'application/pdf');
        $file2 = UploadedFile::fake()->create('anexo_tecnico.docx', 512, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $file3 = UploadedFile::fake()->create('presupuesto.xlsx', 256, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $data = [
            'numero_radicado' => 'RAD-MULTI-001',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Consorcio Vial',
            'empresa' => 'Consorcio Vial 2026',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Radicación con múltiples anexos',
            'medio' => 'Correo Electrónico',
            'prioridad' => 'Alta',
            'observaciones' => 'Se anexan 3 documentos de entrada',
            'responsables' => [$this->responsable->id],
            'archivos_entrada' => [$file1, $file2, $file3],
        ];

        $response = $this->actingAs($this->secretaria)->post(route('radicados.store'), $data);
        $response->assertRedirect(route('radicados.index'));

        $radicado = Radicado::where('numero_radicado', 'RAD-MULTI-001')->first();
        $this->assertNotNull($radicado);
        $this->assertCount(3, $radicado->adjuntos);

        $entradas = $radicado->adjuntos()->where('tipo', 'entrada')->get();
        $this->assertCount(3, $entradas);
        $this->assertTrue($entradas->pluck('nombre_original')->contains('documento_principal.pdf'));
        $this->assertTrue($entradas->pluck('nombre_original')->contains('anexo_tecnico.docx'));
        $this->assertTrue($entradas->pluck('nombre_original')->contains('presupuesto.xlsx'));

        foreach ($entradas as $adj) {
            $storage->assertExists($adj->path);
        }

        // Probar vista show con los 3 archivos
        $showResponse = $this->actingAs($this->secretaria)->get(route('radicados.show', $radicado));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('documento_principal.pdf');
        $showResponse->assertSee('anexo_tecnico.docx');
        $showResponse->assertSee('presupuesto.xlsx');
        $showResponse->assertSee('Descargar todos (.ZIP)');

        // Probar descarga de ZIP con los 3 archivos
        $zipResponse = $this->actingAs($this->secretaria)->get(route('radicados.adjuntos.descargar-todos', $radicado));
        $zipResponse->assertStatus(200);
        $this->assertStringContainsString('application/zip', strtolower($zipResponse->headers->get('Content-Type')));
    }

    public function test_only_durancristian31306_can_delete_radicados(): void
    {
        $storage = Storage::fake('local');

        $superAdmin = User::factory()->create([
            'email' => 'durancristian31306@gmail.com',
            'role' => 'admin',
        ]);

        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-DELETE-ME',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Usuario Para Borrar',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Radicado a borrar',
            'medio' => 'Físico',
            'prioridad' => 'Media',
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $radicado->responsables()->attach($this->responsable->id);

        $path = 'radicados/entradas/doc_delete.pdf';
        $storage->put($path, 'sample file');
        $radicado->adjuntos()->create([
            'tipo' => 'entrada',
            'path' => $path,
            'nombre_original' => 'doc_delete.pdf',
        ]);

        // 1. El superadmin puede ver el botón de borrar
        $showResponse = $this->actingAs($superAdmin)->get(route('radicados.show', $radicado));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Borrar Radicado');

        // 2. El superadmin puede ejecutar la eliminación
        $deleteResponse = $this->actingAs($superAdmin)->delete(route('radicados.destroy', $radicado));
        $deleteResponse->assertRedirect(route('radicados.index'));

        // 3. El radicado ya no existe en BD
        $this->assertDatabaseMissing('radicados', ['id' => $radicado->id]);
        $this->assertDatabaseMissing('radicado_adjuntos', ['radicado_id' => $radicado->id]);
        $storage->assertMissing($path);

        // 4. Se registró en auditoría
        $this->assertDatabaseHas('auditorias', [
            'accion' => 'Eliminó permanentemente el radicado',
            'modelo' => 'Radicado',
            'modelo_id' => $radicado->id,
        ]);
    }

    public function test_other_users_and_other_admins_cannot_delete_radicados(): void
    {
        $otherAdmin = User::factory()->create([
            'email' => 'otro_admin@sirad.gov.co',
            'role' => 'admin',
        ]);

        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-PROTECTED',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Usuario Protegido',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Radicado protegido contra borrado',
            'medio' => 'Físico',
            'prioridad' => 'Media',
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);

        // 1. Secretaria no ve el botón y recibe 403
        $showResponseSecretaria = $this->actingAs($this->secretaria)->get(route('radicados.show', $radicado));
        $showResponseSecretaria->assertStatus(200);
        $showResponseSecretaria->assertDontSee('Borrar Radicado');

        $deleteResponseSecretaria = $this->actingAs($this->secretaria)->delete(route('radicados.destroy', $radicado));
        $deleteResponseSecretaria->assertStatus(403);

        // 2. Otro administrador tampoco ve el botón y recibe 403
        $showResponseAdmin = $this->actingAs($otherAdmin)->get(route('radicados.show', $radicado));
        $showResponseAdmin->assertStatus(200);
        $showResponseAdmin->assertDontSee('Borrar Radicado');

        $deleteResponseAdmin = $this->actingAs($otherAdmin)->delete(route('radicados.destroy', $radicado));
        $deleteResponseAdmin->assertStatus(403);

        // 3. El radicado sigue intacto en BD
        $this->assertDatabaseHas('radicados', ['id' => $radicado->id]);
    }

    public function test_user_or_admin_can_upload_response_files_without_marking_completed_from_portal(): void
    {
        $storage = Storage::fake('local');

        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-PORTAL-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Remitente Test',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Prueba subida sin completar',
            'medio' => 'Físico',
            'prioridad' => 'Media',
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $archivo1 = UploadedFile::fake()->create('oficio_parcial.pdf', 1024, 'application/pdf');

        // Subir archivo seleccionando accion => 'adjuntar' (sin completar)
        $response = $this->actingAs($this->secretaria)->patch(route('radicados.cierre', $radicado), [
            'accion' => 'adjuntar',
            'archivos_salida' => [$archivo1],
        ]);

        $response->assertRedirect(route('radicados.show', $radicado));
        $response->assertSessionHas('success');

        $radicado->refresh();
        $this->assertEquals('pendiente', $radicado->estado);
        $this->assertNull($radicado->fecha_salida);
        $this->assertCount(1, $radicado->adjuntos()->where('tipo', 'salida')->get());

        // Luego se decide completar formalmente
        $responseCompletar = $this->actingAs($this->secretaria)->patch(route('radicados.cierre', $radicado), [
            'accion' => 'completar',
        ]);

        $responseCompletar->assertRedirect(route('radicados.show', $radicado));
        $radicado->refresh();
        $this->assertEquals('completado', $radicado->estado);
        $this->assertNotNull($radicado->fecha_salida);
    }

    public function test_validation_error_shows_spanish_message_for_responsables(): void
    {
        $response = $this->actingAs($this->secretaria)->post(route('radicados.store'), [
            'numero_radicado' => 'RAD-ERR-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Test',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Falta responsable',
            'medio' => 'Físico',
            'prioridad' => 'Media',
            // Omitimos 'responsables'
        ]);

        $response->assertSessionHasErrors(['responsables' => 'Debe seleccionar al menos un funcionario responsable.']);
    }
}
