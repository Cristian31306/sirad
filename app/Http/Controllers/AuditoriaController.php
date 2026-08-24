<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('auditoria.ver');

        $query = Auditoria::with('user');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('accion', 'like', "%{$search}%")
                ->orWhere('modelo', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $perPage = $request->get('per_page', 20);
        $auditorias = $query->paginate($perPage)->withQueryString();

        return view('auditoria.index', compact('auditorias', 'sort', 'direction'));
    }
}
