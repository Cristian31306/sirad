<?php

namespace App\Http\Controllers;

use App\Models\Responsable;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ResponsableController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('admin');
        
        $query = Responsable::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%")
                  ->orWhere('especialidad', 'like', "%{$search}%");
        }
        
        $sort = $request->get('sort', 'nombre');
        $direction = $request->get('direction', 'asc');
        
        $query->orderBy($sort, $direction);
        
        $perPage = $request->get('per_page', 10);
        $responsables = $query->paginate($perPage)->withQueryString();
        $especialidades = Responsable::whereNotNull('especialidad')->distinct()->pluck('especialidad');
        return view('responsables.index', compact('responsables', 'especialidades', 'sort', 'direction'));
    }

    public function create()
    {
        Gate::authorize('admin');
        $especialidades = Responsable::whereNotNull('especialidad')->distinct()->pluck('especialidad');
        return view('responsables.create', compact('especialidades'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');
        $request->validate([
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|unique:responsables,correo',
            'especialidad' => 'nullable|string|max:255',
        ]);

        $responsable = Responsable::create($request->all());

        Auditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'Creó un responsable',
            'modelo' => 'Responsable',
            'modelo_id' => $responsable->id,
            'detalles' => $responsable->toArray(),
        ]);

        return redirect()->route('responsables.index')->with('success', 'Responsable agregado correctamente.');
    }

    public function edit(Responsable $responsable)
    {
        Gate::authorize('admin');
        $especialidades = Responsable::whereNotNull('especialidad')->distinct()->pluck('especialidad');
        return view('responsables.edit', compact('responsable', 'especialidades'));
    }

    public function update(Request $request, Responsable $responsable)
    {
        Gate::authorize('admin');
        $request->validate([
            'nombre' => 'required|string|max:255',
            'correo' => 'required|email|unique:responsables,correo,'.$responsable->id,
            'especialidad' => 'nullable|string|max:255',
        ]);

        $responsable->update($request->all());

        Auditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'Actualizó un responsable',
            'modelo' => 'Responsable',
            'modelo_id' => $responsable->id,
            'detalles' => $responsable->toArray(),
        ]);

        return redirect()->route('responsables.index')->with('success', 'Responsable actualizado correctamente.');
    }

    public function destroy(Responsable $responsable)
    {
        Gate::authorize('admin');
        
        $responsableArray = $responsable->toArray();
        $responsableId = $responsable->id;
        
        $responsable->delete();
        
        Auditoria::create([
            'user_id' => auth()->id(),
            'accion' => 'Eliminó un responsable',
            'modelo' => 'Responsable',
            'modelo_id' => $responsableId,
            'detalles' => $responsableArray,
        ]);
        
        return redirect()->route('responsables.index')->with('success', 'Responsable eliminado correctamente.');
    }
}
