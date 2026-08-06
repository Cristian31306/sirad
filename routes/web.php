<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RadicadoController;
use App\Http\Controllers\FestivoController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\SolicitudEdicionController;
use App\Http\Controllers\TipoTramiteController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'force_password'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class)->except(['show'])->middleware('can:usuarios.gestionar');

    Route::get('radicados/export', [RadicadoController::class, 'export'])->name('radicados.export');
    Route::resource('radicados', RadicadoController::class);
    Route::patch('radicados/{radicado}/anular', [RadicadoController::class, 'anular'])->name('radicados.anular');
    Route::patch('radicados/{radicado}/cierre', [RadicadoController::class, 'updateCierre'])->name('radicados.cierre');

    Route::resource('responsables', ResponsableController::class)->except(['show'])->middleware('can:responsables.gestionar');
    
    Route::resource('tipos-tramites', TipoTramiteController::class)->except(['show'])->parameters([
        'tipos-tramites' => 'tipoTramite'
    ])->middleware('can:tipos_tramites.gestionar');
    
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
    Route::get('/force-password-change', [App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])->name('password.force.change');
    Route::post('/force-password-change', [App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])->name('password.force.update');
});

require __DIR__.'/auth.php';
