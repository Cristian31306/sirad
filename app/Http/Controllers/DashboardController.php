<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Radicado;
use App\Models\Auditoria;
use App\Models\Responsable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();

        $radicados = Radicado::with('funcionario', 'responsable')
            ->where('estado', '!=', 'anulado')
            ->orderBy('fecha_limite', 'asc')
            ->get();
            
        $totales = [
            'pendientes' => $radicados->where('estado', 'pendiente')->count(),
            'alertas' => $radicados->filter(function($radicado) use ($hoy) {
                if (!in_array($radicado->estado, ['pendiente', 'alerta'])) return false;
                $limite = Carbon::parse($radicado->fecha_limite);
                return $limite->greaterThanOrEqualTo($hoy) && $hoy->diffInDays($limite, false) <= 5;
            })->count(),
            'vencidos' => $radicados->filter(function($radicado) use ($hoy) {
                if (in_array($radicado->estado, ['completado', 'anulado'])) return false;
                return Carbon::parse($radicado->fecha_limite)->lessThan($hoy);
            })->count(),
            'completados' => $radicados->where('estado', 'completado')->count(),
        ];

        // Nuevos hoy (creados hoy)
        $nuevosHoy = Radicado::whereDate('created_at', $hoy)->count();
        $vencenHoy = $radicados->where('fecha_limite', $hoy->toDateString())->whereIn('estado', ['pendiente', 'alerta'])->count();

        // Actividad reciente
        $actividadReciente = Auditoria::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Próximos vencimientos (excluyendo completados y anulados)
        $proximosVencimientos = Radicado::with('responsable')
            ->whereNotIn('estado', ['completado', 'anulado'])
            ->orderBy('fecha_limite', 'asc')
            ->take(5)
            ->get();

        // Carga por responsable
        $cargaResponsables = Responsable::withCount(['radicados' => function ($query) {
            $query->whereNotIn('estado', ['completado', 'anulado']);
        }])
        ->orderByDesc('radicados_count')
        ->take(5)
        ->get();

        return view('dashboard', compact(
            'radicados', 
            'totales', 
            'nuevosHoy', 
            'vencenHoy', 
            'actividadReciente', 
            'proximosVencimientos', 
            'cargaResponsables'
        ));
    }
}
