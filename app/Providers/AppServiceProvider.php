<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        // Registrar dinámicamente un Gate por cada permiso definido en el modelo User
        foreach (\App\Models\User::PERMISOS as $grupo => $permisos) {
            foreach ($permisos as $codigo => $etiqueta) {
                \Illuminate\Support\Facades\Gate::define($codigo, function ($user) use ($codigo) {
                    return $user->hasPermiso($codigo);
                });
            }
        }
    }
}
