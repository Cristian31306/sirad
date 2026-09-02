<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadicadoAdjunto extends Model
{
    protected $fillable = ['radicado_id', 'tipo', 'path', 'nombre_original'];

    public function radicado()
    {
        return $this->belongsTo(Radicado::class);
    }
}
