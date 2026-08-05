<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'permisos'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permisos' => 'array',
        ];
    }

    const PERMISOS = [
        'Módulo de Radicados' => [
            'radicados.editar' => [
                'label' => 'Editar Radicados',
                'description' => 'Modificar la información de radicados existentes.',
            ],
            'radicados.anular' => [
                'label' => 'Anular Radicados',
                'description' => 'Cambiar el estado de un radicado a anulado.',
            ],
            'radicados.completar' => [
                'label' => 'Completar Radicados',
                'description' => 'Marcar radicados como completados (Acceso por defecto).',
                'default' => true,
            ],
        ],
        'Módulo de Administración' => [
            'usuarios.gestionar' => [
                'label' => 'Gestionar Usuarios',
                'description' => 'Crear, editar y eliminar usuarios del sistema.',
            ],
            'responsables.gestionar' => [
                'label' => 'Gestionar Responsables',
                'description' => 'Administrar las dependencias responsables.',
            ],
            'tipos_tramites.gestionar' => [
                'label' => 'Gestionar Tipos de Trámite',
                'description' => 'Configurar los tipos de trámites y tiempos.',
            ],
            'solicitudes.gestionar' => [
                'label' => 'Gestionar Solicitudes',
                'description' => 'Aprobar o rechazar solicitudes de edición.',
            ],
            'auditoria.ver' => [
                'label' => 'Ver Auditoría',
                'description' => 'Acceder al registro de acciones del sistema.',
            ],
        ]
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isUsuario()
    {
        return $this->role === 'usuario';
    }

    public function hasPermiso($permiso)
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($permiso === 'radicados.completar') {
            return true;
        }

        $permisos = $this->permisos ?? [];
        return in_array($permiso, $permisos);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordEs($token));
    }
}
