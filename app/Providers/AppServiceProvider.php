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
        // Comprobamos que el usuario tenga el rol adecuado o sea el dueño del recurso
        Gate::define('Editar', function ($user) {
            return $user->hasPermission('Editar');
        });

        Gate::define('Leer', function ($user) {
            return $user->hasPermission('Leer');
        });

        Gate::define('Crear', function ($user) {
            return $user->hasPermission('Crear');
        });

        Gate::define('Eliminar', function ($user) {
            return $user->hasPermission('Eliminar');
        });
    }
}
