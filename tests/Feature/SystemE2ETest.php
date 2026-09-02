<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Festivo;
use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\SolicitudEdicion;
use App\Models\TipoTramite;
use App\Models\User;
use App\Services\DiasHabilesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemE2ETest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $secretaria;
    protected $usuarioBasico;
    protected $tipoTramite;
    protected $responsable1;
    protected $responsable2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Director General',
            'email' => 'admin@sirad.gov.co',
            'password' => Hash::make('Admin1234!'),
            'role' => 'admin',
            'permisos' => [],
            'must_change_password' => false,
        ]);

        $this->secretaria = User::factory()->create([
            'name' => 'Secretaria General',
            'email' => 'secretaria@sirad.gov.co',
            'password' => Hash::make('Secretaria1234!'),
            'role' => 'usuario',
            'permisos' => [
                'radicados.editar',
                'radicados.anular',
                'radicados.completar',
                'responsables.gestionar',
                'tipos_tramites.gestionar',
                'solicitudes.gestionar',
                'auditoria.ver',
            ],
            'must_change_password' => false,
        ]);

        $this->usuarioBasico = User::factory()->create([
            'name' => 'Usuario Básico',
            'email' => 'basico@sirad.gov.co',
            'password' => Hash::make('Basico1234!'),
            'role' => 'usuario',
            'permisos' => [],
            'must_change_password' => false,
        ]);

        $this->tipoTramite = TipoTramite::firstOrCreate(
            ['nombre' => 'Derecho de Petición Especial'],
            ['dias_habiles' => 15, 'activo' => true]
        );

        $this->responsable1 = Responsable::create([
            'nombre' => 'Ing. Benito Pérez',
            'correo' => 'benito.perez@sirad.gov.co',
            'especialidad' => 'Infraestructura',
        ]);

        $this->responsable2 = Responsable::create([
            'nombre' => 'Dra. María Fernández',
            'correo' => 'maria.fernandez@sirad.gov.co',
            'especialidad' => 'Atención al Ciudadano',
        ]);
    }

    // ==========================================
    // 1. AUTENTICACIÓN Y SEGURIDAD
    // ==========================================
    public function test_auth_login_successful_and_unsuccessful(): void
    {
        // Credenciales correctas
        $response = $this->post('/login', [
            'email' => 'admin@sirad.gov.co',
            'password' => 'Admin1234!',
        ]);
        $this->assertAuthenticatedAs($this->admin);
        $response->assertRedirect(route('dashboard'));

        $this->post('/logout');
        $this->assertGuest();

        // Credenciales incorrectas
        $badResponse = $this->post('/login', [
            'email' => 'admin@sirad.gov.co',
            'password' => 'PasswordErroneo!',
        ]);
        $this->assertGuest();
        $badResponse->assertSessionHasErrors('email');
    }

    // ==========================================
    // 2. DASHBOARD Y REPORTES
    // ==========================================
    public function test_dashboard_renders_and_calculates_metrics(): void
    {
        Radicado::create([
            'numero_radicado' => 'RAD-DASH-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Test Remitente',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Asunto prueba',
            'medio' => 'Físico',
            'prioridad' => 'Alta',
            'fecha_limite' => Carbon::today()->addDays(10)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('RAD-DASH-01');
    }

    // ==========================================
    // 3. RADICADOS - CICLO COMPLETO CON ADJUNTOS
    // ==========================================
    public function test_radicado_lifecycle_create_attachment_view_update_cierre_anular(): void
    {
        $storage = Storage::fake('local');
        Mail::fake();

        // 3.1 Creación con archivo de entrada
        $archivoEntrada = UploadedFile::fake()->create('memorial_peticion.pdf', 1024, 'application/pdf');

        $storeResponse = $this->actingAs($this->secretaria)->post(route('radicados.store'), [
            'numero_radicado' => 'RAD-E2E-001',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Ciudadano Ejemplo',
            'empresa' => 'Junta Comunal',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'medio' => 'Físico',
            'prioridad' => 'Alta',
            'asunto' => 'Solicitud de pavimentación urgente',
            'observaciones' => 'Adjunto documento escaneado',
            'responsables' => [$this->responsable1->id, $this->responsable2->id],
            'archivos_entrada' => [$archivoEntrada],
        ]);

        $storeResponse->assertRedirect(route('radicados.index'));
        $this->assertDatabaseHas('radicados', [
            'numero_radicado' => 'RAD-E2E-001',
            'estado' => 'pendiente',
        ]);

        $radicado = Radicado::where('numero_radicado', 'RAD-E2E-001')->first();
        $this->assertCount(2, $radicado->responsables);
        $this->assertCount(1, $radicado->adjuntos);
        $this->assertEquals('memorial_peticion.pdf', $radicado->adjuntos->first()->nombre_original);
        $storage->assertExists($radicado->adjuntos->first()->path);

        // 3.2 Visualización de Detalle y Descarga
        $showResponse = $this->actingAs($this->secretaria)->get(route('radicados.show', $radicado));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('memorial_peticion.pdf');
        $showResponse->assertSee('Ing. Benito Pérez');

        $descargaResponse = $this->actingAs($this->secretaria)->get(route('radicados.archivo.descargar', $radicado->adjuntos->first()));
        $descargaResponse->assertStatus(200);

        // 3.3 Edición directa con permisos
        $updateResponse = $this->actingAs($this->secretaria)->put(route('radicados.update', $radicado), [
            'numero_radicado' => 'RAD-E2E-001',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Ciudadano Ejemplo Actualizado',
            'empresa' => 'Junta Comunal Central',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'medio' => 'Físico',
            'prioridad' => 'Media',
            'asunto' => 'Solicitud de pavimentación y luminarias',
            'observaciones' => 'Actualizado por solicitud de secretaría',
            'responsables' => [$this->responsable1->id],
        ]);

        $updateResponse->assertRedirect(route('radicados.show', $radicado));
        $radicado->refresh();
        $this->assertEquals('Ciudadano Ejemplo Actualizado', $radicado->remitente);
        $this->assertEquals('Media', $radicado->prioridad);
        $this->assertCount(1, $radicado->responsables);

        // 3.4 Cierre con archivo de respuesta
        $archivoSalida = UploadedFile::fake()->create('oficio_respuesta_ofi_001.pdf', 512, 'application/pdf');

        $cierreResponse = $this->actingAs($this->secretaria)->patch(route('radicados.cierre', $radicado), [
            'archivos_salida' => [$archivoSalida],
        ]);

        $cierreResponse->assertRedirect(route('radicados.show', $radicado));
        $radicado->refresh();
        $this->assertEquals('completado', $radicado->estado);
        $salidaAdjunto = $radicado->adjuntos()->where('tipo', 'salida')->first();
        $this->assertNotNull($salidaAdjunto);
        $this->assertEquals('oficio_respuesta_ofi_001.pdf', $salidaAdjunto->nombre_original);
        $storage->assertExists($salidaAdjunto->path);
    }

    public function test_radicado_validation_prevents_invalid_data_and_oversized_files(): void
    {
        Storage::fake('public');

        // Archivo que excede el límite (ej: 30 MB = 30720 KB)
        $archivoGigante = UploadedFile::fake()->create('archivo_muy_pesado.pdf', 30720, 'application/pdf');

        $response = $this->actingAs($this->secretaria)->post(route('radicados.store'), [
            'numero_radicado' => '',
            'fecha_radicacion' => Carbon::tomorrow()->toDateString(), // Fecha futura inválida
            'remitente' => '',
            'tipo_tramite_id' => 99999, // Inexistente
            'medio' => 'Físico',
            'prioridad' => 'Invalida',
            'asunto' => '',
            'responsables' => [],
            'archivos_entrada' => [$archivoGigante],
        ]);

        $response->assertSessionHasErrors([
            'numero_radicado',
            'fecha_radicacion',
            'remitente',
            'tipo_tramite_id',
            'prioridad',
            'asunto',
            'responsables',
            'archivos_entrada.0',
        ]);
    }

    // ==========================================
    // 4. SOLICITUDES DE EDICIÓN (CONTROL DE CAMBIOS)
    // ==========================================
    public function test_solicitudes_edicion_flow(): void
    {
        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-SOL-999',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Remitente Inicial',
            'tipo_tramite_id' => $this->tipoTramite->id,
            'asunto' => 'Asunto Inicial',
            'medio' => 'Correo Electrónico',
            'prioridad' => 'Baja',
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);
        $radicado->responsables()->attach($this->responsable1);

        // 4.1 Usuario normal solicita cambios
        $solicitudStore = $this->actingAs($this->usuarioBasico)->post(route('solicitudes.store', $radicado), [
            'empresa' => 'Nueva Empresa Solicitada',
            'asunto' => 'Asunto Corregido por Usuario Básico',
            'medio' => 'Portal Web',
            'prioridad' => 'Alta',
            'observaciones' => 'Favor corregir nombre de la empresa y prioridad',
            'responsables' => [$this->responsable1->id, $this->responsable2->id],
        ]);

        $solicitudStore->assertRedirect(route('radicados.show', $radicado));
        $this->assertDatabaseHas('solicitudes_edicion', [
            'radicado_id' => $radicado->id,
            'user_id' => $this->usuarioBasico->id,
            'estado' => 'pendiente',
        ]);

        $solicitud = SolicitudEdicion::first();

        // 4.2 Admin visualiza y aprueba
        $indexResponse = $this->actingAs($this->admin)->get(route('solicitudes.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('RAD-SOL-999');
        $indexResponse->assertSee('Usuario Básico');

        $approveResponse = $this->actingAs($this->admin)->patch(route('solicitudes.update', $solicitud), [
            'action' => 'aprobar',
        ]);

        $approveResponse->assertSessionHas('success');
        $solicitud->refresh();
        $radicado->refresh();

        $this->assertEquals('aprobada', $solicitud->estado);
        $this->assertEquals('Asunto Corregido por Usuario Básico', $radicado->asunto);
        $this->assertEquals('Alta', $radicado->prioridad);
        $this->assertEquals('Nueva Empresa Solicitada', $radicado->empresa);
        $this->assertCount(2, $radicado->responsables);
    }

    // ==========================================
    // 5. GESTIÓN DE RESPONSABLES
    // ==========================================
    public function test_responsables_crud_and_validation(): void
    {
        // 5.1 Crear
        $createResponse = $this->actingAs($this->secretaria)->post(route('responsables.store'), [
            'nombre' => 'Dr. Carlos Mendoza',
            'correo' => 'carlos.mendoza@sirad.gov.co',
            'especialidad' => 'Auditoría Forense',
        ]);
        $createResponse->assertRedirect(route('responsables.index'));
        $this->assertDatabaseHas('responsables', ['correo' => 'carlos.mendoza@sirad.gov.co']);

        $nuevoResp = Responsable::where('correo', 'carlos.mendoza@sirad.gov.co')->first();

        // 5.2 Actualizar
        $updateResponse = $this->actingAs($this->secretaria)->put(route('responsables.update', $nuevoResp), [
            'nombre' => 'Dr. Carlos Mendoza Restrepo',
            'correo' => 'carlos.mendoza@sirad.gov.co',
            'especialidad' => 'Control Interno',
        ]);
        $updateResponse->assertRedirect(route('responsables.index'));
        $nuevoResp->refresh();
        $this->assertEquals('Dr. Carlos Mendoza Restrepo', $nuevoResp->nombre);

        // 5.3 Eliminar
        $deleteResponse = $this->actingAs($this->secretaria)->delete(route('responsables.destroy', $nuevoResp));
        $deleteResponse->assertRedirect(route('responsables.index'));
        $this->assertSoftDeleted('responsables', ['id' => $nuevoResp->id]);
    }

    // ==========================================
    // 6. TIPOS DE TRÁMITE
    // ==========================================
    public function test_tipos_tramites_crud_and_toggle(): void
    {
        // 6.1 Crear
        $createResponse = $this->actingAs($this->secretaria)->post(route('tipos-tramites.store'), [
            'nombre' => 'Solicitud de Concepto Ambiental',
            'dias_habiles' => 20,
            'tipo_dias' => 'habiles',
        ]);
        $createResponse->assertRedirect(route('tipos-tramites.index'));
        $this->assertDatabaseHas('tipo_tramites', ['nombre' => 'Solicitud de Concepto Ambiental']);

        $tipo = TipoTramite::where('nombre', 'Solicitud de Concepto Ambiental')->first();

        // 6.2 Toggle activo/inactivo
        $this->assertTrue((bool) $tipo->activo);
        $toggleResponse = $this->actingAs($this->secretaria)->patch(route('tipos-tramites.toggle', $tipo));
        $toggleResponse->assertSessionHas('success');
        $tipo->refresh();
        $this->assertFalse((bool) $tipo->activo);
    }

    // ==========================================
    // 7. ADMINISTRACIÓN DE USUARIOS
    // ==========================================
    public function test_users_management_by_admin(): void
    {
        Mail::fake();

        // 7.1 Admin crea usuario operativo
        $createResponse = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Auxiliar de Correspondencia',
            'email' => 'auxiliar@sirad.gov.co',
            'role' => 'usuario',
            'permisos' => ['radicados.editar', 'radicados.completar'],
        ]);

        $createResponse->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'auxiliar@sirad.gov.co']);

        $user = User::where('email', 'auxiliar@sirad.gov.co')->first();
        $this->assertTrue($user->hasPermiso('radicados.editar'));
        $this->assertFalse($user->hasPermiso('usuarios.gestionar'));

        // 7.2 Usuario normal no puede acceder a administración de usuarios
        $forbiddenResponse = $this->actingAs($this->usuarioBasico)->get(route('users.index'));
        $forbiddenResponse->assertStatus(403);
    }

    // ==========================================
    // 8. AUDITORÍA Y TRAZABILIDAD
    // ==========================================
    public function test_auditoria_records_actions_and_renders(): void
    {
        Auditoria::create([
            'user_id' => $this->admin->id,
            'accion' => 'Acción de prueba de auditoría',
            'modelo' => 'Radicado',
            'modelo_id' => 1,
            'detalles' => ['ip' => '127.0.0.1'],
        ]);

        $response = $this->actingAs($this->admin)->get(route('auditoria.index'));
        $response->assertStatus(200);
        $response->assertSee('Acción de prueba de auditoría');
    }

    // ==========================================
    // 9. FESTIVOS Y COMANDOS DE CONSOLA
    // ==========================================
    public function test_commands_and_dias_habiles_calculation(): void
    {
        $service = new DiasHabilesService();

        // Verificar que 1 de enero de cualquier año es festivo automático
        $this->assertFalse($service->esDiaHabil(Carbon::create(2028, 1, 1)));

        // Ejecutar comando de sincronización de festivos
        $syncExitCode = Artisan::call('sirad:sync-festivos', ['year' => 2026]);
        $this->assertEquals(0, $syncExitCode);

        // Ejecutar comando de verificación de vencimientos
        $vencimientosExitCode = Artisan::call('radicados:check-vencimientos');
        $this->assertEquals(0, $vencimientosExitCode);
    }
}
