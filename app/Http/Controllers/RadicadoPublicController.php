<?php

namespace App\Http\Controllers;

use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\User;
use App\Notifications\RespuestaSubidaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class RadicadoPublicController extends Controller
{
    public function showRespuestaForm(Request $request, Radicado $radicado, Responsable $responsable)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Enlace no válido o ha expirado.');
        }

        // Si ya tiene archivo de salida, mostrar mensaje de que ya fue respondido
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
            return back()->with('error', 'Este radicado ya cuenta con una respuesta.');
        }

        $request->validate([
            'archivo_salida' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,zip,jpg,jpeg,png',
        ], [
            'archivo_salida.required' => 'Debe adjuntar un archivo de respuesta.',
            'archivo_salida.mimes' => 'Formato no válido. Solo se permiten PDF, Office, Imágenes o ZIP.',
            'archivo_salida.max' => 'El archivo no debe pesar más de 10MB.',
        ]);

        if ($request->hasFile('archivo_salida') && $request->file('archivo_salida')->isValid()) {
            $path = $request->file('archivo_salida')->store('radicados/salidas', 'local');
            $nombre = $request->file('archivo_salida')->getClientOriginalName();

            $radicado->adjuntos()->create([
                'tipo' => 'salida',
                'path' => $path,
                'nombre_original' => $nombre,
            ]);



            // Notificar a todos los usuarios
            $usuarios = User::where('role', 'usuario')->get();
            if ($usuarios->isNotEmpty()) {
                Notification::send($usuarios, new RespuestaSubidaNotification($radicado, $responsable));
            }

            return view('public.radicado.respuesta_completada', compact('radicado', 'responsable'))->with('success', 'Archivo subido correctamente. El radicado ha sido actualizado.');
        }

        return back()->with('error', 'Ocurrió un error al subir el archivo.');
    }
}
