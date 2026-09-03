<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadicadoAdjunto extends Model
{
    protected $fillable = ['radicado_id', 'responsable_id', 'tipo', 'path', 'nombre_original'];

    public function radicado()
    {
        return $this->belongsTo(Radicado::class);
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class)->withTrashed();
    }
}
