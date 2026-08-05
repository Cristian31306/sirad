<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Festivo extends Model
{
    protected $fillable = ['fecha', 'descripcion'];

    protected $casts = [
        'fecha' => 'date',
    ];
}
