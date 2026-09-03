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
        'estado_respuesta',
        'respuesta_marcada_por',
        'fecha_respuesta_marcada',
    ];

    protected $casts = [
        'fecha_radicacion' => 'date',
        'fecha_limite' => 'date',
        'fecha_salida' => 'date',
        'fecha_respuesta_marcada' => 'datetime',
    ];

    public function hasArchivoEntrada(): bool
    {
        return $this->adjuntos()->where('tipo', 'entrada')->exists();
    }

    public function hasArchivoSalida(): bool
    {
        return $this->adjuntos()->where('tipo', 'salida')->exists();
    }

    public function funcionario()
    {
        return $this->belongsTo(User::class, 'funcionario_id');
    }

    public function responsables()
    {
        return $this->belongsToMany(Responsable::class, 'radicado_responsable')
                    ->withPivot('hubo_rebote', 'fecha_rebote');
    }

    public function anulador()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function tipoTramite()
    {
        return $this->belongsTo(TipoTramite::class, 'tipo_tramite_id');
    }

    public function adjuntos()
    {
        return $this->hasMany(RadicadoAdjunto::class, 'radicado_id');
    }

    public function solicitudesEdicion()
    {
        return $this->hasMany(SolicitudEdicion::class, 'radicado_id');
    }

    public function notas()
    {
        return $this->hasMany(RadicadoNota::class, 'radicado_id')->latest();
    }

    public function respuestaMarcadaPor()
    {
        return $this->belongsTo(Responsable::class, 'respuesta_marcada_por');
    }
}
