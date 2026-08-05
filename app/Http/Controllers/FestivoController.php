<?php

namespace App\Http\Controllers;

use App\Models\Festivo;
use Illuminate\Http\Request;

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

        Festivo::create($request->all());

        return redirect()->route('festivos.index')->with('success', 'Festivo agregado correctamente.');
    }

    public function edit(Festivo $festivo)
    {
        return view('festivos.edit', compact('festivo'));
    }

    public function update(Request $request, Festivo $festivo)
    {
        $request->validate([
            'fecha' => 'required|date|unique:festivos,fecha,' . $festivo->id,
            'descripcion' => 'required|string|max:255',
        ]);

        $festivo->update($request->all());

        return redirect()->route('festivos.index')->with('success', 'Festivo actualizado correctamente.');
    }

    public function destroy(Festivo $festivo)
    {
        $festivo->delete();

        return redirect()->route('festivos.index')->with('success', 'Festivo eliminado correctamente.');
    }
}
