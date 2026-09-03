<?php

namespace Tests\Feature;

use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\TipoTramite;
use App\Models\User;
use App\Notifications\CorreoRebotadoNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BrevoWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_brevo_webhook_marks_bounce_and_notifies_usuario(): void
    {
        Notification::fake();

        $usuarioOperativo = User::factory()->create([
            'email' => 'operativo@sirad.gov.co',
            'role' => 'usuario',
        ]);

        $tipoTramite = TipoTramite::create([
            'nombre' => 'Petición',
            'dias_habiles' => 10,
            'tipo_dias' => 'habiles',
        ]);

        $responsable = Responsable::create([
            'nombre' => 'Carlos Mendoza',
            'correo' => 'carlos.mendoza.falso@noexiste.com',
        ]);

        $radicado = Radicado::create([
            'numero_radicado' => 'RAD-2026-BOUNCE',
            'fecha_radicacion' => Carbon::today()->toDateString(),
            'remitente' => 'Ciudadano',
            'asunto' => 'Prueba de rebote',
            'tipo_tramite_id' => $tipoTramite->id,
            'medio' => 'Correo',
            'prioridad' => 'Media',
            'fecha_limite' => Carbon::today()->addDays(10)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $radicado->responsables()->attach($responsable->id);

        $payload = [
            'event' => 'hard_bounce',
            'email' => 'carlos.mendoza.falso@noexiste.com',
            'subject' => 'Nueva Radicación Asignada: RAD-2026-BOUNCE',
            'id' => 12345,
            'date' => Carbon::now()->toIso8601String(),
        ];

        $response = $this->postJson(route('webhook.brevo'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $radicado->refresh();
        $pivot = $radicado->responsables()->where('responsable_id', $responsable->id)->first()->pivot;
        $this->assertTrue((bool) $pivot->hubo_rebote);
        $this->assertNotNull($pivot->fecha_rebote);

        Notification::assertSentTo($usuarioOperativo, CorreoRebotadoNotification::class);
    }
}
