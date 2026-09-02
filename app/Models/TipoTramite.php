<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoTramite extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = ['nombre', 'dias_habiles', 'tipo_dias', 'activo'];
}
