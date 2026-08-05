<?php

namespace App\Http\Controllers;

use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\TipoTramite;
use App\Models\Auditoria;
use App\Services\DiasHabilesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class RadicadoController extends Controller
{
    protected $diasHabilesService;

    public function __construct(DiasHabilesService $diasHabilesService)
    {
        $this->diasHabilesService = $diasHabilesService;
    }

    private function buildIndexQuery(Request $request)
    {
        $query = Radicado::with('responsable', 'tipoTramite');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_radicado', 'like', "%{$search}%")
                  ->orWhere('remitente', 'like', "%{$search}%")
                  ->orWhere('empresa', 'like', "%{$search}%")
                  ->orWhere('asunto', 'like', "%{$search}%");
            });
        }

        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fecha_inicio') && $request->fecha_inicio != '') {
            $query->whereDate('fecha_radicacion', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin != '') {
            $query->whereDate('fecha_radicacion', '<=', $request->fecha_fin);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->buildIndexQuery($request);
        
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        // Custom sorting for related model
        if ($sort == 'responsable') {
            $query->join('responsables', 'radicados.responsable_id', '=', 'responsables.id')
                  ->orderBy('responsables.nombre', $direction)
                  ->select('radicados.*'); // ensure we only select radicados columns
        } else {
            $query->orderBy($sort, $direction);
        }

        $perPage = $request->get('per_page', 10);
        $radicados = $query->paginate($perPage)->withQueryString();
        return view('radicados.index', compact('radicados', 'sort', 'direction'));
    }

    public function export(Request $request)
    {
        $query = $this->buildIndexQuery($request);
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        if ($sort == 'responsable') {
            $query->join('responsables', 'radicados.responsable_id', '=', 'responsables.id')
                  ->orderBy('responsables.nombre', $direction)
                  ->select('radicados.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $radicados = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=radicados_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($radicados) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Numero Radicado', 'Fecha Radicacion', 'Remitente', 'Asunto', 'Medio', 'Responsable', 'Estado', 'Fecha Limite'], ';');

            foreach ($radicados as $r) {
                fputcsv($file, [
                    $r->numero_radicado,
                    $r->fecha_radicacion,
                    $r->remitente,
                    $r->asunto,
                    $r->medio,
                    $r->responsable ? $r->responsable->nombre : 'N/A',
                    strtoupper($r->estado),
                    $r->fecha_limite
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $responsables = Responsable::all();
        $tiposTramites = TipoTramite::all();
        return view('radicados.create', compact('responsables', 'tiposTramites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_radicado' => 'required|string|max:255|unique:radicados,numero_radicado',
            'fecha_radicacion' => 'required|date|before_or_equal:today',
            'hora_recepcion' => 'required|date_format:H:i',
            'remitente' => 'required|string|max:255',
            'empresa' => 'nullable|string|max:255',
            'tipo_tramite_id' => 'required|exists:tipo_tramites,id',
            'asunto' => 'required|string',
            'medio' => 'required|string|max:255',
            'prioridad' => 'required|in:Alta,Media,Baja',
            'observaciones' => 'nullable|string',
            'responsable_id' => 'required|exists:responsables,id',
        ]);

        $tipoTramite = TipoTramite::findOrFail($request->tipo_tramite_id);
        
        $fechaRadicacion = Carbon::parse($request->fecha_radicacion);
        $fechaLimite = $this->diasHabilesService->calcularFechaLimite($fechaRadicacion, $tipoTramite->dias_habiles);

        $radicado = Radicado::create([
            'numero_radicado' => $request->numero_radicado,
            'fecha_radicacion' => $fechaRadicacion->toDateString(),
            'hora_recepcion' => $request->hora_recepcion,
            'remitente' => $request->remitente,
            'empresa' => $request->empresa,
            'asunto' => $request->asunto,
            'tipo_tramite_id' => $request->tipo_tramite_id,
            'medio' => $request->medio,
            'prioridad' => $request->prioridad,
            'observaciones' => $request->observaciones,
            'fecha_limite' => $fechaLimite->toDateString(),
            'responsable_id' => $request->responsable_id,
            'estado' => 'pendiente',
        ]);

        Auditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'Creó un radicado',
            'modelo' => 'Radicado',
            'modelo_id' => $radicado->id,
            'detalles' => $radicado->toArray(),
        ]);

        if ($radicado->responsable) {
            // Note: Update mail later to new Mailable or edit existing NuevaRadicacionMail
            try {
                Mail::to($radicado->responsable->correo)->queue(new \App\Mail\NuevaRadicacionMail($radicado));
            } catch (\Exception $e) { \Log::error("Mail Error: " . $e->getMessage()); }
        }

        return redirect()->route('radicados.index')->with('success', 'Radicado creado correctamente. Asignado a: ' . ($radicado->responsable->nombre ?? 'N/A'));
    }

    public function update(Request $request, Radicado $radicado)
    {
        Gate::authorize('radicados.editar');

        $request->validate([
            'numero_radicado' => 'required|string|max:255|unique:radicados,numero_radicado,' . $radicado->id,
            'fecha_radicacion' => 'required|date',
            'hora_recepcion' => 'required|date_format:H:i',
            'remitente' => 'required|string|max:255',
            'empresa' => 'nullable|string|max:255',
            'tipo_tramite_id' => 'required|exists:tipo_tramites,id',
            'medio' => 'required|string|max:255',
            'prioridad' => 'required|in:Alta,Media,Baja',
            'asunto' => 'required|string',
            'observaciones' => 'nullable|string',
            'responsable_id' => 'required|exists:responsables,id',
        ]);

        $tipoTramite = TipoTramite::find($request->tipo_tramite_id);
        $fechaLimite = $this->diasHabilesService->calcularFechaLimite(Carbon::parse($request->fecha_radicacion), $tipoTramite->dias_habiles);

        $radicado->update([
            'numero_radicado' => $request->numero_radicado,
            'fecha_radicacion' => Carbon::parse($request->fecha_radicacion)->toDateString(),
            'hora_recepcion' => $request->hora_recepcion,
            'remitente' => $request->remitente,
            'empresa' => $request->empresa,
            'tipo_tramite_id' => $request->tipo_tramite_id,
            'medio' => $request->medio,
            'prioridad' => $request->prioridad,
            'asunto' => $request->asunto,
            'observaciones' => $request->observaciones,
            'fecha_limite' => $fechaLimite->toDateString(),
            'responsable_id' => $request->responsable_id,
        ]);

        Auditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'Editó un radicado',
            'modelo' => 'Radicado',
            'modelo_id' => $radicado->id,
            'detalles' => $request->only(['numero_radicado', 'fecha_radicacion', 'hora_recepcion', 'remitente', 'empresa', 'tipo_tramite_id', 'medio', 'prioridad', 'asunto', 'observaciones', 'responsable_id']),
        ]);

        return redirect()->route('radicados.show', $radicado)->with('success', 'Radicado actualizado correctamente.');
    }

    public function edit(Radicado $radicado)
    {
        $radicado->load('responsable', 'anulador', 'tipoTramite');
        return view('radicados.show', compact('radicado'));
    }

    public function show(Radicado $radicado)
    {
        $radicado->load('responsable', 'anulador', 'tipoTramite');
        $tiposTramites = TipoTramite::all();
        $responsables = Responsable::all();
        return view('radicados.show', compact('radicado', 'tiposTramites', 'responsables'));
    }

    public function updateCierre(Request $request, Radicado $radicado)
    {
        $radicado->update([
            'fecha_salida' => Carbon::now()->toDateString(),
            'estado' => 'completado',
        ]);

        Auditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'Completó un radicado',
            'modelo' => 'Radicado',
            'modelo_id' => $radicado->id,
            'detalles' => ['fecha_salida' => Carbon::now()->toDateString()],
        ]);

        return redirect()->route('radicados.show', $radicado)->with('success', 'Trámite cerrado correctamente.');
    }

    public function anular(Request $request, Radicado $radicado)
    {
        Gate::authorize('admin');

        $request->validate([
            'motivo_anulacion' => 'required|string|max:255',
        ]);

        $radicado->update([
            'estado' => 'anulado',
            'motivo_anulacion' => $request->motivo_anulacion,
            'anulado_por' => auth()->id(),
        ]);

        Auditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'Anuló un radicado',
            'modelo' => 'Radicado',
            'modelo_id' => $radicado->id,
            'detalles' => ['motivo_anulacion' => $request->motivo_anulacion],
        ]);

        return redirect()->route('radicados.index')->with('success', 'Radicado anulado correctamente.');
    }
}
