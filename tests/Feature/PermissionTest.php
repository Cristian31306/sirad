<?php

namespace Tests\Feature;

use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\SolicitudEdicion;
use App\Models\TipoTramite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_access_to_all_modules(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'permisos' => [],
            'must_change_password' => false,
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('users.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('responsables.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('tipos-tramites.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('festivos.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('auditoria.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('solicitudes.index'))->assertStatus(200);
    }

    public function test_regular_user_without_permissions_is_restricted(): void
    {
        $user = User::factory()->create([
            'role' => 'usuario',
            'permisos' => [],
            'must_change_password' => false,
        ]);

        $this->actingAs($user)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('responsables.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('tipos-tramites.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('festivos.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('auditoria.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('solicitudes.index'))->assertStatus(403);
    }

    public function test_regular_user_with_specific_permission_can_access_module(): void
    {
        $userWithResponsables = User::factory()->create([
            'role' => 'usuario',
            'permisos' => ['responsables.gestionar'],
            'must_change_password' => false,
        ]);

        $this->actingAs($userWithResponsables)->get(route('responsables.index'))->assertStatus(200);
        $this->actingAs($userWithResponsables)->get(route('users.index'))->assertStatus(403);
    }

    public function test_solicitud_edicion_approval_workflow(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'permisos' => [],
            'must_change_password' => false,
        ]);

        $regularUser = User::factory()->create([
            'role' => 'usuario',
            'permisos' => [],
            'must_change_password' => false,
        ]);

        $tipo = TipoTramite::firstOrCreate(
            ['nombre' => 'Petición Solicitud'],
            ['dias_habiles' => 15, 'activo' => true]
        );

        $resp = Responsable::create([
            'nombre' => 'Funcionario',
            'correo' => 'funcionario@sirad.gov.co',
        ]);

        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-SOL-01',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Remitente Original',
            'tipo_tramite_id' => $tipo->id,
            'asunto' => 'Asunto antes de solicitud',
            'medio' => 'Físico',
            'prioridad' => 'Baja',
            'fecha_limite' => Carbon::today()->addDays(15)->toDateString(),
            'estado' => 'pendiente',
        ]);
        $radicado->responsables()->attach($resp);

        // 1. Usuario normal envía solicitud de edición
        $solicitudData = [
            'empresa' => 'Nueva Empresa',
            'asunto' => 'Asunto Corregido por Solicitud',
            'medio' => 'Portal Web',
            'prioridad' => 'Alta',
            'observaciones' => 'Favor aprobar cambio',
            'responsables' => [$resp->id],
        ];

        $response = $this->actingAs($regularUser)->post(route('solicitudes.store', $radicado), $solicitudData);
        $response->assertRedirect(route('radicados.show', $radicado));

        $this->assertDatabaseHas('solicitudes_edicion', [
            'radicado_id' => $radicado->id,
            'user_id' => $regularUser->id,
            'estado' => 'pendiente',
        ]);

        $solicitud = SolicitudEdicion::first();

        // 2. Admin aprueba la solicitud
        $approvalResponse = $this->actingAs($admin)->patch(route('solicitudes.update', $solicitud), [
            'action' => 'aprobar',
        ]);

        $approvalResponse->assertSessionHas('success');

        $solicitud->refresh();
        $radicado->refresh();

        $this->assertEquals('aprobada', $solicitud->estado);
        $this->assertEquals('Asunto Corregido por Solicitud', $radicado->asunto);
        $this->assertEquals('Alta', $radicado->prioridad);
    }
}
