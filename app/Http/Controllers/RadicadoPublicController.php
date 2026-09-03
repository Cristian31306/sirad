<?php

namespace App\Http\Controllers;

use App\Models\Radicado;
use App\Models\RadicadoAdjunto;
use App\Models\Responsable;
use App\Models\User;
use App\Notifications\RespuestaSubidaNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class RadicadoPublicController extends Controller
{
    public function showRespuestaForm(Request $request, Radicado $radicado, Responsable $responsable)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        $radicado->load(['adjuntos', 'tipoTramite', 'responsables']);

        // Si ya tiene archivo de salida, mostrar vista de respuesta completada con los archivos
        if ($radicado->hasArchivoSalida()) {
            return view('public.radicado.respuesta_completada', compact('radicado', 'responsable'));
        }

        return view('public.radicado.respuesta', compact('radicado', 'responsable'));
    }

    public function storeRespuesta(Request $request, Radicado $radicado, Responsable $responsable)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        if ($radicado->hasArchivoSalida()) {
            return back()->with('error', 'Este radicado ya cuenta con una respuesta registrada.');
        }

        $request->validate([
            'archivos_salida' => 'required|array|min:1|max:20',
            'archivos_salida.*' => 'required|file|max:25600|mimes:pdf,doc,docx,xls,xlsx,zip,rar,7z,jpg,jpeg,png',
        ], [
            'archivos_salida.required' => 'Debe adjuntar al menos un archivo de respuesta.',
            'archivos_salida.array' => 'El formato de envío de archivos no es válido.',
            'archivos_salida.min' => 'Debe adjuntar al menos un archivo de respuesta.',
            'archivos_salida.max' => 'No puedes subir más de 20 archivos a la vez.',
            'archivos_salida.*.required' => 'Cada archivo seleccionado debe ser válido.',
            'archivos_salida.*.max' => 'Cada archivo no puede superar los 25 MB.',
            'archivos_salida.*.mimes' => 'Formato no válido. Solo se permiten PDF, Word, Excel, Imágenes (JPG, PNG) o ZIP/RAR.',
        ]);

        DB::transaction(function () use ($radicado, $request) {
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

        // Notificar a usuarios de SIRAD
        $usuarios = User::where('role', 'usuario')->get();
        if ($usuarios->isNotEmpty()) {
            Notification::send($usuarios, new RespuestaSubidaNotification($radicado, $responsable));
        }

        $radicado->load('adjuntos');

        return view('public.radicado.respuesta_completada', compact('radicado', 'responsable'))
            ->with('success', 'Documento(s) de respuesta subido(s) correctamente. El trámite ha sido completado.');
    }

    public function downloadAdjunto(Request $request, Radicado $radicado, Responsable $responsable, RadicadoAdjunto $adjunto)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        if ($adjunto->radicado_id !== $radicado->id) {
            abort(404, 'Archivo no encontrado en este radicado.');
        }

        if (!Storage::disk('local')->exists($adjunto->path)) {
            abort(404, 'El archivo no existe físicamente en el servidor.');
        }

        return Storage::disk('local')->download($adjunto->path, $adjunto->nombre_original ?: basename($adjunto->path));
    }

    public function verAdjunto(Request $request, Radicado $radicado, Responsable $responsable, RadicadoAdjunto $adjunto)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        if ($adjunto->radicado_id !== $radicado->id) {
            abort(404, 'Archivo no encontrado en este radicado.');
        }

        if (!Storage::disk('local')->exists($adjunto->path)) {
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
        if (!$request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        $query = $radicado->adjuntos();
        if ($tipo && in_array($tipo, ['entrada', 'salida'])) {
            $query->where('tipo', $tipo);
        }
        $adjuntos = $query->get();

        if ($adjuntos->isEmpty()) {
            return back()->with('error', 'No hay archivos para descargar.');
        }

        $zipFileName = 'radicado_' . str_replace(['/', '\\', ' '], '_', $radicado->numero_radicado) . ($tipo ? "_{$tipo}" : '') . '_adjuntos.zip';
        $zipTempPath = tempnam(sys_get_temp_dir(), 'zip_');

        $zip = new ZipArchive();
        if ($zip->open($zipTempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'No se pudo generar el archivo comprimido.');
        }

        $addedCount = 0;
        $usedNames = [];
        foreach ($adjuntos as $adjunto) {
            if ($adjunto->path && Storage::disk('local')->exists($adjunto->path)) {
                $fullPath = Storage::disk('local')->path($adjunto->path);
                $originalName = $adjunto->nombre_original ?: basename($adjunto->path);

                if (isset($usedNames[$originalName])) {
                    $usedNames[$originalName]++;
                    $pathInfo = pathinfo($originalName);
                    $nameInZip = $pathInfo['filename'] . " ({$usedNames[$originalName]})." . ($pathInfo['extension'] ?? '');
                } else {
                    $usedNames[$originalName] = 1;
                    $nameInZip = $originalName;
                }

                $prefix = $tipo ? '' : (ucfirst($adjunto->tipo) . '/');
                $zip->addFile($fullPath, $prefix . $nameInZip);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($zipTempPath);
            return back()->with('error', 'Los archivos solicitados no se encontraron físicamente en el servidor.');
        }

        return response()->download($zipTempPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
