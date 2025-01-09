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
        Gate::define('Editar', function ($user) {
            // Comprobamos que el usuario tenga el rol adecuado o sea el dueño del recurso
            return $user->hasPermission('Editar');
        });

        Gate::define('Lectura', function ($user) {
            // Comprobamos que el usuario tenga el rol adecuado o sea el dueño del recurso
            return $user->hasPermission('Lectura');
        });

        Gate::define('Crear', function ($user) {
            // Comprobamos que el usuario tenga el rol adecuado o sea el dueño del recurso
            return $user->hasPermission('Crear');
        });
    }
}
