<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FestivoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RadicadoController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\SolicitudEdicionController;
use App\Http\Controllers\TipoTramiteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'force_password'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class)->except(['show'])->middleware('can:usuarios.gestionar');

    Route::get('radicados/export', [RadicadoController::class, 'export'])->name('radicados.export');
    Route::delete('radicados/{radicado}', [RadicadoController::class, 'destroy'])->name('radicados.destroy')->middleware('can:radicados.borrar');
    Route::resource('radicados', RadicadoController::class)->except(['destroy']);
    Route::patch('radicados/{radicado}/anular', [RadicadoController::class, 'anular'])->name('radicados.anular');
    Route::patch('radicados/{radicado}/cierre', [RadicadoController::class, 'updateCierre'])->name('radicados.cierre');
    Route::patch('radicados/{radicado}/responsables/{responsable}/correo', [RadicadoController::class, 'updateResponsableCorreo'])->name('radicados.responsables.correo.update');
    Route::post('radicados/{radicado}/responsables/{responsable}/reenviar', [RadicadoController::class, 'reenviarNotificacion'])->name('radicados.responsables.reenviar');
    Route::post('radicados/{radicado}/notas', [RadicadoController::class, 'storeNota'])->name('radicados.notas.store');
    Route::get('adjuntos/{adjunto}/descargar', [RadicadoController::class, 'downloadArchivo'])->name('radicados.archivo.descargar');
    Route::get('adjuntos/{adjunto}/ver', [RadicadoController::class, 'verArchivo'])->name('radicados.archivo.ver');
    Route::get('radicados/{radicado}/adjuntos/descargar-todos/{tipo?}', [RadicadoController::class, 'descargarTodos'])->name('radicados.adjuntos.descargar-todos');

    Route::resource('responsables', ResponsableController::class)->except(['show'])->middleware('can:responsables.gestionar');

    Route::resource('tipos-tramites', TipoTramiteController::class)->except(['show'])->parameters([
        'tipos-tramites' => 'tipoTramite',
    ])->middleware('can:tipos_tramites.gestionar');
    Route::patch('tipos-tramites/{tipoTramite}/toggle', [TipoTramiteController::class, 'toggle'])->name('tipos-tramites.toggle')->middleware('can:tipos_tramites.gestionar');

    Route::get('/auditoria/exportar', [AuditoriaController::class, 'export'])->name('auditoria.export')->middleware('can:auditoria.ver');
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index')->middleware('can:auditoria.ver');

    Route::get('/solicitudes', [SolicitudEdicionController::class, 'index'])->name('solicitudes.index')->middleware('can:solicitudes.gestionar');
    Route::post('/radicados/{radicado}/solicitud', [SolicitudEdicionController::class, 'store'])->name('solicitudes.store');
    Route::patch('/solicitudes/{solicitud}', [SolicitudEdicionController::class, 'update'])->name('solicitudes.update')->middleware('can:solicitudes.gestionar');

    Route::resource('festivos', FestivoController::class)->except(['show'])->middleware('role:admin');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/force-password-change', [ForcePasswordChangeController::class, 'show'])->name('password.force.change');
    Route::post('/force-password-change', [ForcePasswordChangeController::class, 'update'])->name('password.force.update');
});

require __DIR__.'/auth.php';

// Rutas Públicas (Firmadas) para Responsables
Route::middleware('signed')->group(function () {
    Route::get('radicados/{radicado}/responsable/{responsable}/respuesta', [\App\Http\Controllers\RadicadoPublicController::class, 'showRespuestaForm'])->name('radicados.public.respuesta');
    Route::post('radicados/{radicado}/responsable/{responsable}/respuesta', [\App\Http\Controllers\RadicadoPublicController::class, 'storeRespuesta'])->name('radicados.public.respuesta.store');
    Route::get('radicados/{radicado}/responsable/{responsable}/adjuntos/{adjunto}/descargar', [\App\Http\Controllers\RadicadoPublicController::class, 'downloadAdjunto'])->name('radicados.public.adjuntos.descargar');
    Route::get('radicados/{radicado}/responsable/{responsable}/adjuntos/{adjunto}/ver', [\App\Http\Controllers\RadicadoPublicController::class, 'verAdjunto'])->name('radicados.public.adjuntos.ver');
    Route::get('radicados/{radicado}/responsable/{responsable}/adjuntos/descargar-todos/{tipo?}', [\App\Http\Controllers\RadicadoPublicController::class, 'descargarTodos'])->name('radicados.public.adjuntos.descargar-todos');
});

// Webhook Brevo (sin CSRF)
Route::post('webhook/brevo', [\App\Http\Controllers\Webhook\BrevoWebhookController::class, 'handleWebhook'])->name('webhook.brevo');
