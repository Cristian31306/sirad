<?php

namespace App\Http\Controllers;

use App\Mail\NuevaRadicacionMail;
use App\Models\Auditoria;
use App\Models\Radicado;
use App\Models\Responsable;
use App\Models\SolicitudEdicion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class SolicitudEdicionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('solicitudes.gestionar');

        $query = SolicitudEdicion::with(['user', 'radicado']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('radicado', function ($q) use ($search) {
                $q->where('numero_radicado', 'like', "%{$search}%");
            });
        }

        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $perPage = $request->get('per_page', 10);
        $solicitudes = $query->paginate($perPage)->withQueryString();
        $responsables = Responsable::all()->keyBy('id');

        return view('solicitudes.index', compact('solicitudes', 'sort', 'direction', 'responsables'));
    }

    public function store(Request $request, Radicado $radicado)
    {
        // Any logged in user can request an edit
        $request->validate([
            'empresa' => 'nullable|string|max:255',
            'asunto' => 'required|string',
            'medio' => 'required|string|max:255',
            'prioridad' => 'required|in:Alta,Media,Baja',
            'observaciones' => 'nullable|string',
            'responsables' => 'required|array|min:1',
            'responsables.*' => 'exists:responsables,id',
            'estado' => 'required|in:pendiente,completado,alerta,vencido,anulado',
        ]);

        $solicitud = SolicitudEdicion::create([
            'radicado_id' => $radicado->id,
            'user_id' => $request->user()->id,
            'datos_propuestos' => $request->only(['empresa', 'asunto', 'medio', 'prioridad', 'observaciones', 'responsables', 'estado']),
            'estado' => 'pendiente',
        ]);

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Creó solicitud de edición',
            'modelo' => 'SolicitudEdicion',
            'modelo_id' => $solicitud->id,
            'detalles' => ['radicado_id' => $radicado->id],
        ]);

        return redirect()->route('radicados.show', $radicado)->with('success', 'Solicitud de edición enviada. El administrador debe aprobarla para que se aplique.');
    }

    public function update(Request $request, SolicitudEdicion $solicitud)
    {
        Gate::authorize('solicitudes.gestionar');

        if ($request->action === 'aprobar') {
            $solicitud->update(['estado' => 'aprobada']);

            // Actualizar campos regulares
            $datosToUpdate = collect($solicitud->datos_propuestos)->except('responsables')->toArray();
            $solicitud->radicado->update($datosToUpdate);

            // Sincronizar responsables si vienen en los datos propuestos
            if (isset($solicitud->datos_propuestos['responsables'])) {
                $oldResponsablesIds = $solicitud->radicado->responsables->pluck('id')->toArray();
                $solicitud->radicado->responsables()->sync($solicitud->datos_propuestos['responsables']);

                $nuevosResponsablesIds = array_diff($solicitud->datos_propuestos['responsables'], $oldResponsablesIds);
                if (! empty($nuevosResponsablesIds)) {
                    $nuevosResponsables = Responsable::whereIn('id', $nuevosResponsablesIds)->get();
                    foreach ($nuevosResponsables as $resp) {
                        Mail::to($resp->correo)->queue(new NuevaRadicacionMail($solicitud->radicado, $resp));
                    }
                }
            }

            Auditoria::create([
                'user_id' => Auth::id(),
                'accion' => 'Aprobó solicitud de edición',
                'modelo' => 'SolicitudEdicion',
                'modelo_id' => $solicitud->id,
                'detalles' => ['radicado_id' => $solicitud->radicado_id],
            ]);

            return back()->with('success', 'Solicitud de edición aprobada y cambios aplicados al radicado.');
        } else {
            $solicitud->update(['estado' => 'rechazada']);

            Auditoria::create([
                'user_id' => Auth::id(),
                'accion' => 'Rechazó solicitud de edición',
                'modelo' => 'SolicitudEdicion',
                'modelo_id' => $solicitud->id,
                'detalles' => ['radicado_id' => $solicitud->radicado_id],
            ]);

            return back()->with('success', 'Solicitud de edición rechazada.');
        }
    }
}
