<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadicadoNota extends Model
{
    protected $fillable = [
        'radicado_id',
        'responsable_id',
        'user_id',
        'autor_nombre',
        'contenido',
    ];

    public function radicado()
    {
        return $this->belongsTo(Radicado::class);
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
