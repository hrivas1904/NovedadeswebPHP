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
        Gate::define('edd.acceder', fn (User $user): bool => $user->estado === 'ACTIVO');

        Gate::define('edd.administrar', fn (User $user): bool => $user->estado === 'ACTIVO' && $user->rol === 'Administrador/a'
        );

        // Acceso a la estructura. Las futuras consultas exigirán además una asignación vigente.
        Gate::define('edd.evaluar', fn (User $user): bool => $user->estado === 'ACTIVO'
            && in_array($user->rol, ['Administrador/a', 'Coordinador/a', 'Coordinador/a L2'], true)
        );
    }
}
