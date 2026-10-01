<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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
        Gate::define('organigrama.ver', fn (User $user): bool => $user->estado === 'ACTIVO');
        Gate::define('organigrama.editar', fn (User $user): bool => Gate::forUser($user)->allows('edd.administrar'));

        Gate::define('edd.acceder', fn (User $user): bool => $user->estado === 'ACTIVO');

        Gate::define('edd.administrar', fn (User $user): bool => $user->estado === 'ACTIVO' && $user->rol === 'Administrador/a'
        );

        // La función de evaluador surge también de una asignación explícita, sin
        // cambiar el rol global ni conceder acceso a la configuración de RRHH.
        Gate::define('edd.evaluar', fn (User $user): bool => $user->estado === 'ACTIVO'
            && (in_array($user->rol, ['Administrador/a', 'Coordinador/a', 'Coordinador/a L2'], true)
                || (Schema::hasTable('edd_asignaciones') && DB::table('edd_asignaciones as s')
                    ->join('edd_participantes as p', 'p.id', '=', 's.participante_id')
                    ->where('s.evaluador_user_id', $user->id)->where('s.current_slot', 1)->where('p.incluido', true)->exists()))
        );
    }
}
