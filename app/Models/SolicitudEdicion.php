<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudEdicion extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_edicion';

    protected $fillable = [
        'radicado_id',
        'user_id',
        'datos_propuestos',
        'estado',
    ];

    protected $casts = [
        'datos_propuestos' => 'array',
    ];

    public function radicado()
    {
        return $this->belongsTo(Radicado::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
