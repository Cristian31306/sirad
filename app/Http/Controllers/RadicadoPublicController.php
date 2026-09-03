<?php

namespace App\Http\Controllers;

use App\Models\Radicado;
use App\Models\RadicadoAdjunto;
use App\Models\Responsable;
use App\Models\User;
use App\Notifications\RespuestaSubidaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class RadicadoPublicController extends Controller
{
    public function showRespuestaForm(Request $request, Radicado $radicado, Responsable $responsable)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        $radicado->load([
            'adjuntos.responsable',
            'tipoTramite',
            'responsables',
            'notas.responsable',
            'respuestaMarcadaPor',
        ]);

        // Solo si el radicado ya fue cerrado (completado) o anulado, mostrar vista de cierre
        if (in_array($radicado->estado, ['completado', 'anulado'])) {
            return view('public.radicado.respuesta_completada', compact('radicado', 'responsable'));
        }

        return view('public.radicado.respuesta', compact('radicado', 'responsable'));
    }

    public function storeRespuesta(Request $request, Radicado $radicado, Responsable $responsable)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        if (in_array($radicado->estado, ['completado', 'anulado'])) {
            return back()->with('error', 'Este radicado ya ha sido cerrado o anulado formalmente.');
        }

        $request->validate([
            'archivos_salida' => 'nullable|array|max:20',
            'archivos_salida.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,zip,rar,7z,jpg,jpeg,png',
            'nota' => 'nullable|string|max:3000',
            'estado_entrega' => 'nullable|in:avance,finalizar',
        ], [
            'archivos_salida.max' => 'No puedes subir más de 20 archivos a la vez.',
            'archivos_salida.*.max' => 'Cada archivo no puede superar los 10 MB.',
            'archivos_salida.*.mimes' => 'Formato no válido. Solo se permiten PDF, Word, Excel, Imágenes (JPG, PNG) o ZIP/RAR.',
            'nota.max' => 'La nota no puede superar los 3000 caracteres.',
        ]);

        $tieneArchivos = $request->hasFile('archivos_salida');
        $tieneNota = ! empty(trim($request->input('nota', '')));

        if (! $tieneArchivos && ! $tieneNota) {
            return back()->with('error', 'Debe adjuntar al menos un archivo o escribir una nota de avance.');
        }

        $nombresArchivos = [];
        $notaTexto = $tieneNota ? trim($request->input('nota')) : null;
        $estadoEntrega = $request->input('estado_entrega', 'finalizar');
        $esFinalizar = $estadoEntrega === 'finalizar';

        DB::transaction(function () use ($radicado, $responsable, $request, $tieneArchivos, $tieneNota, $notaTexto, $esFinalizar, &$nombresArchivos) {
            // 1. Guardar archivos si vienen
            if ($tieneArchivos) {
                foreach ($request->file('archivos_salida') as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('radicados/salidas', 'local');
                        $nombreOriginal = $file->getClientOriginalName();
                        $nombresArchivos[] = $nombreOriginal;

                        $radicado->adjuntos()->create([
                            'tipo' => 'salida',
                            'path' => $path,
                            'nombre_original' => $nombreOriginal,
                            'responsable_id' => $responsable->id,
                        ]);
                    }
                }
            }

            // 2. Guardar nota en bitácora si viene
            if ($tieneNota) {
                $radicado->notas()->create([
                    'responsable_id' => $responsable->id,
                    'autor_nombre' => $responsable->nombre,
                    'contenido' => $notaTexto,
                ]);
            }

            // 3. Actualizar estado_respuesta
            if ($esFinalizar) {
                $radicado->update([
                    'estado_respuesta' => 'lista_para_revision',
                    'respuesta_marcada_por' => $responsable->id,
                    'fecha_respuesta_marcada' => now(),
                ]);
            } else {
                if ($radicado->estado_respuesta === 'sin_respuesta') {
                    $radicado->update([
                        'estado_respuesta' => 'en_tramite',
                    ]);
                }
            }
        });

        // 4. Notificar a usuarios de SIRAD ÚNICAMENTE si se marcó como respuesta finalizada
        if ($esFinalizar) {
            $usuarios = User::where('role', 'usuario')->get();
            if ($usuarios->isEmpty()) {
                $usuarios = User::where('role', 'admin')->get();
            }

            if ($usuarios->isNotEmpty()) {
                Notification::send($usuarios, new RespuestaSubidaNotification($radicado, $responsable, $nombresArchivos, $notaTexto));
            }

            $mensaje = '¡Respuesta registrada con éxito! Ha sido marcada como LISTA PARA REVISIÓN y el personal de correspondencia ya fue notificado para proceder con el cierre del radicado.';
        } else {
            $mensaje = 'Avance guardado exitosamente en la bitácora del radicado. El equipo y la entidad ya pueden visualizar los archivos y notas preliminares.';
        }

        return redirect($request->fullUrl())->with('success', $mensaje);
    }

    public function downloadAdjunto(Request $request, Radicado $radicado, Responsable $responsable, RadicadoAdjunto $adjunto)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        if ($adjunto->radicado_id !== $radicado->id) {
            abort(404, 'Archivo no encontrado en este radicado.');
        }

        if (! Storage::disk('local')->exists($adjunto->path)) {
            abort(404, 'El archivo no existe físicamente en el servidor.');
        }

        return Storage::disk('local')->download($adjunto->path, $adjunto->nombre_original ?: basename($adjunto->path));
    }

    public function verAdjunto(Request $request, Radicado $radicado, Responsable $responsable, RadicadoAdjunto $adjunto)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        if ($adjunto->radicado_id !== $radicado->id) {
            abort(404, 'Archivo no encontrado en este radicado.');
        }

        if (! Storage::disk('local')->exists($adjunto->path)) {
            abort(404, 'El archivo no existe físicamente en el servidor.');
        }

        $fullPath = Storage::disk('local')->path($adjunto->path);
        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($adjunto->path).'"',
        ]);
    }

    public function descargarTodos(Request $request, Radicado $radicado, Responsable $responsable, ?string $tipo = null)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        $query = $radicado->adjuntos();
        if ($tipo && in_array($tipo, ['entrada', 'salida'])) {
            $query->where('tipo', $tipo);
        }

        $adjuntos = $query->get();

        if ($adjuntos->isEmpty()) {
            return back()->with('error', 'No hay archivos disponibles para descargar.');
        }

        $zipFileName = 'radicado_'.$radicado->numero_radicado.'_'.($tipo ?? 'adjuntos').'_'.time().'.zip';
        $zipDirectory = storage_path('app/temp');
        if (! file_exists($zipDirectory)) {
            mkdir($zipDirectory, 0755, true);
        }
        $zipPath = $zipDirectory.'/'.$zipFileName;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'No se pudo generar el archivo ZIP comprimido.');
        }

        $nombresUsados = [];
        foreach ($adjuntos as $adjunto) {
            if (Storage::disk('local')->exists($adjunto->path)) {
                $nombreArchivo = $adjunto->nombre_original ?: basename($adjunto->path);

                if (in_array($nombreArchivo, $nombresUsados)) {
                    $ext = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
                    $nameOnly = pathinfo($nombreArchivo, PATHINFO_FILENAME);
                    $nombreArchivo = $nameOnly.'_'.uniqid().'.'.$ext;
                }
                $nombresUsados[] = $nombreArchivo;

                $zip->addFile(Storage::disk('local')->path($adjunto->path), $nombreArchivo);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
