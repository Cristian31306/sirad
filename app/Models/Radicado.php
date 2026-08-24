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
        'motivo_anulacion',
        'anulado_por',
        'empresa',
        'medio',
        'prioridad',
        'observaciones',
        'archivo_entrada_path',
        'archivo_entrada_nombre',
        'archivo_salida_path',
        'archivo_salida_nombre',
    ];

    protected $casts = [
        'fecha_radicacion' => 'date',
        'fecha_limite' => 'date',
        'fecha_salida' => 'date',
    ];

    public function hasArchivoEntrada(): bool
    {
        return ! empty($this->archivo_entrada_path);
    }

    public function hasArchivoSalida(): bool
    {
        return ! empty($this->archivo_salida_path);
    }

    public function funcionario()
    {
        return $this->belongsTo(User::class, 'funcionario_id');
    }

    public function responsables()
    {
        return $this->belongsToMany(Responsable::class, 'radicado_responsable');
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
