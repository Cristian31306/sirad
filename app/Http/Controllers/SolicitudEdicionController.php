<?php

namespace App\Http\Controllers;

use App\Models\SolicitudEdicion;
use App\Models\Radicado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SolicitudEdicionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('solicitudes.gestionar');
        
        $query = SolicitudEdicion::with(['user', 'radicado']);
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('radicado', function($q) use ($search) {
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
        
        return view('solicitudes.index', compact('solicitudes', 'sort', 'direction'));
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
            'responsable_id' => 'required|exists:responsables,id',
        ]);

        SolicitudEdicion::create([
            'radicado_id' => $radicado->id,
            'user_id' => $request->user()->id,
            'datos_propuestos' => $request->only(['empresa', 'asunto', 'medio', 'prioridad', 'observaciones', 'responsable_id']),
            'estado' => 'pendiente',
        ]);

        return redirect()->route('radicados.show', $radicado)->with('success', 'Solicitud de edición enviada. El administrador debe aprobarla para que se aplique.');
    }

    public function update(Request $request, SolicitudEdicion $solicitud)
    {
        Gate::authorize('solicitudes.gestionar');
        
        if ($request->action === 'aprobar') {
            $solicitud->update(['estado' => 'aprobada']);
            $solicitud->radicado->update($solicitud->datos_propuestos);
            return back()->with('success', 'Solicitud de edición aprobada y cambios aplicados al radicado.');
        } else {
            $solicitud->update(['estado' => 'rechazada']);
            return back()->with('success', 'Solicitud de edición rechazada.');
        }
    }
}
