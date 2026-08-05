<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Radicado extends Model
{
    protected $fillable = [
        'numero_radicado',
        'fecha_radicacion',
        'remitente',
        'asunto',
        'tipo_tramite_id',
        'fecha_limite',
        'funcionario_id',
        'estado',
        'fecha_salida',
        'estado',
        'motivo_anulacion',
        'anulado_por',
        'empresa',
        'medio',
        'prioridad',
        'observaciones',
        'hora_recepcion',
        'responsable_id',
    ];

    protected $casts = [
        'fecha_radicacion' => 'date',
        'fecha_limite' => 'date',
        'fecha_salida' => 'date',
    ];

    public function funcionario()
    {
        return $this->belongsTo(User::class, 'funcionario_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class, 'responsable_id');
    }

    public function anulador()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function tipoTramite()
    {
        return $this->belongsTo(TipoTramite::class, 'tipo_tramite_id');
    }
}
