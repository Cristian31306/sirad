<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        \App\Models\Radicado::observe(\App\Observers\RadicadoObserver::class);

        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        // Registrar dinámicamente un Gate por cada permiso definido en el modelo User
        foreach (User::PERMISOS as $grupo => $permisos) {
            foreach ($permisos as $codigo => $etiqueta) {
                Gate::define($codigo, function ($user) use ($codigo) {
                    return $user->hasPermiso($codigo);
                });
            }
        }

        // Permiso exclusivo: solo el superadministrador durancristian31306@gmail.com puede borrar radicados
        Gate::define('radicados.borrar', function (User $user) {
            return strtolower(trim($user->email)) === 'durancristian31306@gmail.com';
        });
    }
}
