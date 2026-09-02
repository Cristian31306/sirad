<?php

namespace App\Http\Controllers;

use App\Mail\NuevaRadicacionMail;
use App\Models\Auditoria;
use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\TipoTramite;
use App\Services\DiasHabilesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $query = Radicado::with('responsables', 'tipoTramite');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_radicado', 'like', "%{$search}%")
                    ->orWhere('remitente', 'like', "%{$search}%")
                    ->orWhere('empresa', 'like', "%{$search}%")
                    ->orWhere('asunto', 'like', "%{$search}%");
            });
        }

        if ($request->has('estado') && !empty($request->estado)) {
            $estados = (array) $request->estado;
            $query->whereIn('estado', $estados);
        }

        if ($request->has('fecha_inicio') && $request->fecha_inicio != '') {
            $query->whereDate('fecha_radicacion', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin != '') {
            $query->whereDate('fecha_radicacion', '<=', $request->fecha_fin);
        }

        if ($request->has('prioridad') && !empty($request->prioridad)) {
            $prioridades = (array) $request->prioridad;
            $query->whereIn('prioridad', $prioridades);
        }

        if ($request->has('tipo_tramite_id') && !empty($request->tipo_tramite_id)) {
            $tipos = (array) $request->tipo_tramite_id;
            $query->whereIn('tipo_tramite_id', $tipos);
        }

        if ($request->has('responsable_id') && !empty($request->responsable_id)) {
            $responsables = (array) $request->responsable_id;
            $query->whereHas('responsables', function ($q) use ($responsables) {
                $q->whereIn('responsables.id', $responsables);
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->buildIndexQuery($request);

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $query->orderBy($sort, $direction);

        $perPage = $request->get('per_page', 10);
        $radicados = $query->paginate($perPage)->withQueryString();

        $responsables = \App\Models\Responsable::all();
        $tiposTramites = \App\Models\TipoTramite::where('activo', true)->get();

        return view('radicados.index', compact('radicados', 'sort', 'direction', 'responsables', 'tiposTramites'));
    }

    public function export(Request $request)
    {
        $query = $this->buildIndexQuery($request);
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $query->orderBy($sort, $direction);

        $radicados = $query->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=radicados_'.date('Ymd_His').'.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($radicados) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Numero Radicado', 'Fecha Radicacion', 'Remitente', 'Asunto', 'Medio', 'Responsable', 'Estado', 'Fecha Limite'], ';');

            foreach ($radicados as $r) {
                fputcsv($file, [
                    $r->numero_radicado,
                    $r->fecha_radicacion ? \Carbon\Carbon::parse($r->fecha_radicacion)->format('Y-m-d') : '',
                    $r->remitente,
                    $r->asunto,
                    $r->medio,
                    $r->responsables->pluck('nombre')->implode(', ') ?: 'N/A',
                    strtoupper($r->estado),
                    $r->fecha_limite ? \Carbon\Carbon::parse($r->fecha_limite)->format('Y-m-d') : '',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $responsables = Responsable::all();
        $tiposTramites = TipoTramite::where('activo', true)->get();

        return view('radicados.create', compact('responsables', 'tiposTramites'));
    }

    public function store(\App\Http\Requests\StoreRadicadoRequest $request)
    {
        $tipoTramite = TipoTramite::findOrFail($request->tipo_tramite_id);

        $fechaRadicacion = Carbon::parse($request->fecha_radicacion);
        if ($tipoTramite->tipo_dias === 'calendario') {
            $fechaLimite = $fechaRadicacion->copy()->addDays($tipoTramite->dias_habiles);
        } else {
            $fechaLimite = $this->diasHabilesService->calcularFechaLimite($fechaRadicacion, $tipoTramite->dias_habiles);
        }

        $radicado = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $fechaRadicacion, $fechaLimite) {
            $radicado = Radicado::create([
                'numero_radicado' => $request->numero_radicado,
                'fecha_radicacion' => $fechaRadicacion->toDateString(),
                'remitente' => $request->remitente,
                'empresa' => $request->empresa,
                'asunto' => $request->asunto,
                'tipo_tramite_id' => $request->tipo_tramite_id,
                'medio' => $request->medio,
                'prioridad' => $request->prioridad,
                'observaciones' => $request->observaciones,
                'fecha_limite' => $fechaLimite->toDateString(),
                'estado' => 'pendiente',
            ]);

            if ($request->hasFile('archivos_entrada')) {
                foreach ($request->file('archivos_entrada') as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('radicados/entradas', 'local');
                        $radicado->adjuntos()->create([
                            'tipo' => 'entrada',
                            'path' => $path,
                            'nombre_original' => $file->getClientOriginalName(),
                        ]);
                    }
                }
            }

            $radicado->responsables()->attach($request->responsables);

            if ($radicado->responsables->isNotEmpty()) {
                foreach ($radicado->responsables as $resp) {
                    Mail::to($resp->correo)->queue(new NuevaRadicacionMail($radicado, $resp));
                }
            }

            return $radicado;
        });

        $nombresResponsables = $radicado->responsables->pluck('nombre')->implode(', ') ?: 'N/A';

        return redirect()->route('radicados.index')->with('success', 'Radicado creado correctamente. Asignado a: '.$nombresResponsables);
    }

    public function update(\App\Http\Requests\UpdateRadicadoRequest $request, Radicado $radicado)
    {
        $tipoTramite = TipoTramite::find($request->tipo_tramite_id);
        if ($tipoTramite->tipo_dias === 'calendario') {
            $fechaLimite = Carbon::parse($request->fecha_radicacion)->addDays($tipoTramite->dias_habiles);
        } else {
            $fechaLimite = $this->diasHabilesService->calcularFechaLimite(Carbon::parse($request->fecha_radicacion), $tipoTramite->dias_habiles);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($radicado, $request, $fechaLimite) {
            $radicado->update([
                'numero_radicado' => $request->numero_radicado,
                'fecha_radicacion' => Carbon::parse($request->fecha_radicacion)->toDateString(),
                'remitente' => $request->remitente,
                'empresa' => $request->empresa,
                'tipo_tramite_id' => $request->tipo_tramite_id,
                'medio' => $request->medio,
                'prioridad' => $request->prioridad,
                'asunto' => $request->asunto,
                'observaciones' => $request->observaciones,
                'fecha_limite' => $fechaLimite->toDateString(),
            ]);

            $radicado->responsables()->sync($request->responsables);
        });

        return redirect()->route('radicados.show', $radicado)->with('success', 'Radicado actualizado correctamente.');
    }

    public function edit(Radicado $radicado)
    {
        $radicado->load('responsables', 'anulador', 'tipoTramite');

        return view('radicados.show', compact('radicado'));
    }

    public function show(Radicado $radicado)
    {
        $radicado->load('responsables', 'anulador', 'tipoTramite', 'adjuntos');
        $tiposTramites = TipoTramite::where('activo', true)->get();
        $responsables = Responsable::all();

        return view('radicados.show', compact('radicado', 'tiposTramites', 'responsables'));
    }

    public function updateCierre(Request $request, Radicado $radicado)
    {
        Gate::authorize('radicados.completar');

        $request->validate([
            'archivos_salida' => 'nullable|array|max:20',
            'archivos_salida.*' => 'nullable|file|max:25600|mimes:pdf,doc,docx,xls,xlsx,zip,rar,7z,jpg,jpeg,png',
        ], [
            'archivos_salida.max' => 'No puedes subir más de 20 archivos al mismo tiempo.',
            'archivos_salida.*.max' => 'Cada archivo no puede superar los 25 MB.',
            'archivos_salida.*.mimes' => 'Solo se permiten archivos en formato PDF, Word, Excel, Imágenes (JPG, PNG) o Comprimidos (ZIP, RAR, 7Z).',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($radicado, $request) {
            $radicado->update([
                'estado' => 'completado',
                'fecha_salida' => Carbon::today()->toDateString(),
            ]);

            if ($request->hasFile('archivos_salida')) {
                foreach ($request->file('archivos_salida') as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('radicados/salidas', 'local');
                        $radicado->adjuntos()->create([
                            'tipo' => 'salida',
                            'path' => $path,
                            'nombre_original' => $file->getClientOriginalName(),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('radicados.show', $radicado)->with('success', 'Trámite cerrado correctamente.');
    }

    public function descargarTodos(Radicado $radicado, ?string $tipo = null)
    {
        $query = $radicado->adjuntos();
        if ($tipo && in_array($tipo, ['entrada', 'salida'])) {
            $query->where('tipo', $tipo);
        }
        $adjuntos = $query->get();

        if ($adjuntos->isEmpty()) {
            return back()->with('error', 'No hay archivos para descargar.');
        }

        if ($adjuntos->count() === 1) {
            return $this->downloadArchivo($adjuntos->first());
        }

        $cleanRadicadoNum = preg_replace('/[^A-Za-z0-9_\-]/', '_', $radicado->numero_radicado);
        $tipoSuffix = $tipo ? "_{$tipo}" : '_todos';
        $zipFileName = "{$cleanRadicadoNum}{$tipoSuffix}.zip";
        $tempZipPath = tempnam(sys_get_temp_dir(), 'sirad_zip_');

        $zip = new \ZipArchive();
        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'No se pudo generar el archivo comprimido.');
        }

        $usedNames = [];
        foreach ($adjuntos as $adjunto) {
            if ($adjunto->path && \Illuminate\Support\Facades\Storage::disk('local')->exists($adjunto->path)) {
                $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($adjunto->path);
                $originalName = $adjunto->nombre_original ?: basename($adjunto->path);

                $entryName = $originalName;
                $counter = 1;
                while (in_array($entryName, $usedNames)) {
                    $info = pathinfo($originalName);
                    $namePart = $info['filename'];
                    $extPart = isset($info['extension']) ? '.' . $info['extension'] : '';
                    $entryName = "{$namePart} ({$counter}){$extPart}";
                    $counter++;
                }
                $usedNames[] = $entryName;

                $zip->addFile($fullPath, $entryName);
            }
        }

        $zip->close();

        return response()->download($tempZipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function downloadArchivo(\App\Models\RadicadoAdjunto $adjunto)
    {
        if (! $adjunto->path || ! \Illuminate\Support\Facades\Storage::disk('local')->exists($adjunto->path)) {
            abort(404, 'El archivo solicitado no existe.');
        }

        $nombreDescarga = $adjunto->nombre_original ?: basename($adjunto->path);

        return \Illuminate\Support\Facades\Storage::disk('local')->download($adjunto->path, $nombreDescarga);
    }

    public function verArchivo(\App\Models\RadicadoAdjunto $adjunto)
    {
        if (! $adjunto->path || ! \Illuminate\Support\Facades\Storage::disk('local')->exists($adjunto->path)) {
            abort(404, 'El archivo solicitado no existe.');
        }

        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($adjunto->path);
        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($adjunto->path).'"',
        ]);
    }

    public function anular(Request $request, Radicado $radicado)
    {
        Gate::authorize('radicados.anular');

        $request->validate([
            'motivo_anulacion' => 'required|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($radicado, $request) {
            $radicado->update([
                'estado' => 'anulado',
                'motivo_anulacion' => $request->motivo_anulacion,
                'anulado_por' => Auth::id(),
            ]);
        });

        return redirect()->route('radicados.index')->with('success', 'Radicado anulado correctamente.');
    }
}
