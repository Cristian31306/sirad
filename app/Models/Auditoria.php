<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Auditoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'accion',
        'modelo',
        'modelo_id',
        'detalles',
        'ip_address',
        'user_agent',
        'firma_hash',
    ];

    protected $casts = [
        'detalles' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($auditoria) {
            // Auto-fill IP and User Agent if missing (e.g. from web requests)
            if (empty($auditoria->ip_address)) {
                $auditoria->ip_address = request()->ip();
            }
            if (empty($auditoria->user_agent)) {
                $auditoria->user_agent = request()->userAgent();
            }

            // Calculate integrity hash
            $auditoria->firma_hash = self::calcularFirma($auditoria);
        });
    }

    public static function calcularFirma($auditoria)
    {
        $data = [
            'user_id' => $auditoria->user_id,
            'accion' => $auditoria->accion,
            'modelo' => $auditoria->modelo,
            'modelo_id' => $auditoria->modelo_id,
            'detalles' => json_encode($auditoria->detalles),
            'ip_address' => $auditoria->ip_address,
            'user_agent' => $auditoria->user_agent,
            // Not including timestamps because they might not be set exactly at creating stage
        ];

        return hash_hmac('sha256', json_encode($data), config('app.key'));
    }

    public function isFirmaValida()
    {
        if (empty($this->firma_hash)) {
            return false; // Old records won't have a hash, handle accordingly in UI
        }
        return hash_equals($this->firma_hash, self::calcularFirma($this));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
