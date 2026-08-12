<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoTramite extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'dias_habiles', 'activo'];
}
