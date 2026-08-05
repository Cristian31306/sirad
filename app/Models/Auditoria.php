<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'accion',
        'modelo',
        'modelo_id',
        'detalles',
    ];

    protected $casts = [
        'detalles' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
