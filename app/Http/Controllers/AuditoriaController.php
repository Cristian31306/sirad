<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('auditoria.ver');

        $query = $this->buildFilterQuery($request);

        // Stats for KPIs
        $todayCount = Auditoria::whereDate('created_at', today())->count();
        $totalCount = Auditoria::count();
        $uniqueUsersCount = Auditoria::distinct('user_id')->count('user_id');

        // Data for dropdowns
        $modelos = Auditoria::select('modelo')->distinct()->whereNotNull('modelo')->pluck('modelo');
        $usuarios = User::whereHas('auditorias')->orderBy('name')->get();

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $perPage = $request->get('per_page', 20);
        $auditorias = $query->paginate($perPage)->withQueryString();

        return view('auditoria.index', compact('auditorias', 'sort', 'direction', 'todayCount', 'totalCount', 'uniqueUsersCount', 'modelos', 'usuarios'));
    }

    public function export(Request $request)
    {
        Gate::authorize('auditoria.ver');

        $query = $this->buildFilterQuery($request);
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        $auditorias = $query->orderBy($sort, $direction)->get();

        $filename = "auditoria_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Timestamp', 'Usuario', 'Acción', 'Entidad', 'Entidad_ID', 'IP', 'User_Agent', 'Firma_Valida', 'Detalles');

        $callback = function() use($auditorias, $columns) {
            $file = fopen('php://output', 'w');
            
            // Output UTF-8 BOM for proper Excel rendering
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($auditorias as $audit) {
                $row['Timestamp'] = $audit->created_at->format('Y-m-d H:i:s');
                $row['Usuario'] = optional($audit->user)->name ?? 'System Process';
                $row['Acción'] = $audit->accion;
                $row['Entidad'] = $audit->modelo;
                $row['Entidad_ID'] = $audit->modelo_id;
                $row['IP'] = $audit->ip_address;
                $row['User_Agent'] = $audit->user_agent;
                $row['Firma_Valida'] = $audit->firma_hash ? ($audit->isFirmaValida() ? 'Sí' : 'Manipulado') : 'Sin firma';
                $row['Detalles'] = json_encode($audit->detalles, JSON_UNESCAPED_UNICODE);

                fputcsv($file, array($row['Timestamp'], $row['Usuario'], $row['Acción'], $row['Entidad'], $row['Entidad_ID'], $row['IP'], $row['User_Agent'], $row['Firma_Valida'], $row['Detalles']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildFilterQuery(Request $request)
    {
        $query = Auditoria::with('user');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('accion', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('fecha_inicio') && $request->fecha_inicio != '') {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin != '') {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('modelo') && $request->modelo != '') {
            $query->where('modelo', $request->modelo);
        }

        if ($request->has('accion_tipo') && $request->accion_tipo != '') {
            $accion = $request->accion_tipo;
            if ($accion === 'creacion') {
                $query->where(function($q){
                    $q->where('accion', 'like', '%creó%')->orWhere('accion', 'like', '%crear%')->orWhere('accion', 'like', '%creado%');
                });
            } elseif ($accion === 'actualizacion') {
                $query->where(function($q){
                    $q->where('accion', 'like', '%actualiz%')->orWhere('accion', 'like', '%edit%')->orWhere('accion', 'like', '%cambi%');
                });
            } elseif ($accion === 'eliminacion') {
                $query->where(function($q){
                    $q->where('accion', 'like', '%elimin%')->orWhere('accion', 'like', '%borr%');
                });
            }
        }

        return $query;
    }
}
