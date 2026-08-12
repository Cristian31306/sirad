<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Festivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FestivoController extends Controller
{
    public function index()
    {
        $festivos = Festivo::orderBy('fecha', 'asc')->get();

        return view('festivos.index', compact('festivos'));
    }

    public function create()
    {
        return view('festivos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date|unique:festivos,fecha',
            'descripcion' => 'required|string|max:255',
        ]);

        $festivo = Festivo::create($request->all());

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Creó un festivo',
            'modelo' => 'Festivo',
            'modelo_id' => $festivo->id,
            'detalles' => $festivo->toArray(),
        ]);

        return redirect()->route('festivos.index')->with('success', 'Festivo agregado correctamente.');
    }

    public function edit(Festivo $festivo)
    {
        return view('festivos.edit', compact('festivo'));
    }

    public function update(Request $request, Festivo $festivo)
    {
        $request->validate([
            'fecha' => 'required|date|unique:festivos,fecha,'.$festivo->id,
            'descripcion' => 'required|string|max:255',
        ]);

        $festivo->update($request->all());

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Editó un festivo',
            'modelo' => 'Festivo',
            'modelo_id' => $festivo->id,
            'detalles' => $festivo->toArray(),
        ]);

        return redirect()->route('festivos.index')->with('success', 'Festivo actualizado correctamente.');
    }

    public function destroy(Festivo $festivo)
    {
        $detalles = $festivo->toArray();
        $festivo->delete();

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Eliminó un festivo',
            'modelo' => 'Festivo',
            'modelo_id' => $festivo->id,
            'detalles' => $detalles,
        ]);

        return redirect()->route('festivos.index')->with('success', 'Festivo eliminado correctamente.');
    }
}
