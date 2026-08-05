<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responsable extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'correo', 'especialidad'];

    public function radicados()
    {
        return $this->hasMany(Radicado::class);
    }
}
