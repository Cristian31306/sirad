<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Radicado;
use App\Models\TipoTramite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TipoTramiteController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('admin');

        $query = TipoTramite::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%");
        }

        $sort = $request->get('sort', 'nombre');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        $perPage = $request->get('per_page', 10);
        $tipos = $query->paginate($perPage)->withQueryString();

        return view('tipos_tramites.index', compact('tipos', 'sort', 'direction'));
    }

    public function create()
    {
        Gate::authorize('admin');

        return view('tipos_tramites.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipo_tramites,nombre',
            'dias_habiles' => 'required|integer|min:1|max:365',
        ]);

        $tipoTramite = TipoTramite::create($request->all());

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Creó un tipo de trámite',
            'modelo' => 'TipoTramite',
            'modelo_id' => $tipoTramite->id,
            'detalles' => $tipoTramite->toArray(),
        ]);

        return redirect()->route('tipos-tramites.index')->with('success', 'Tipo de Trámite creado correctamente.');
    }

    public function edit(TipoTramite $tipoTramite)
    {
        Gate::authorize('admin');

        return view('tipos_tramites.edit', compact('tipoTramite'));
    }

    public function update(Request $request, TipoTramite $tipoTramite)
    {
        Gate::authorize('admin');

        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipo_tramites,nombre,'.$tipoTramite->id,
            'dias_habiles' => 'required|integer|min:1|max:365',
        ]);

        $tipoTramite->update($request->all());

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Editó un tipo de trámite',
            'modelo' => 'TipoTramite',
            'modelo_id' => $tipoTramite->id,
            'detalles' => $tipoTramite->toArray(),
        ]);

        return redirect()->route('tipos-tramites.index')->with('success', 'Tipo de Trámite actualizado correctamente.');
    }

    public function destroy(TipoTramite $tipoTramite)
    {
        Gate::authorize('admin');

        // Check if there are related radicados
        if (Radicado::where('tipo_tramite_id', $tipoTramite->id)->exists()) {
            return redirect()->route('tipos-tramites.index')->with('error', 'No se puede eliminar el Tipo de Trámite porque tiene radicados asociados.');
        }

        $detalles = $tipoTramite->toArray();
        $tipoTramite->delete();

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Eliminó un tipo de trámite',
            'modelo' => 'TipoTramite',
            'modelo_id' => $tipoTramite->id,
            'detalles' => $detalles,
        ]);

        return redirect()->route('tipos-tramites.index')->with('success', 'Tipo de Trámite eliminado correctamente.');
    }

    public function toggle(TipoTramite $tipoTramite)
    {
        Gate::authorize('admin');

        $tipoTramite->activo = ! $tipoTramite->activo;
        $tipoTramite->save();

        $mensaje = $tipoTramite->activo ? 'activado' : 'suspendido';

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Cambió estado de tipo de trámite a '.$mensaje,
            'modelo' => 'TipoTramite',
            'modelo_id' => $tipoTramite->id,
            'detalles' => ['activo' => $tipoTramite->activo],
        ]);

        return redirect()->route('tipos-tramites.index')->with('success', "Tipo de Trámite {$mensaje} correctamente.");
    }
}
