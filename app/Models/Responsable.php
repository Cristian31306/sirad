<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responsable extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = ['nombre', 'correo', 'especialidad'];

    public function radicados()
    {
        return $this->belongsToMany(Radicado::class, 'radicado_responsable');
    }
}
