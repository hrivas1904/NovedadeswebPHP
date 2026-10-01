<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrganigramaFixture
{
    public static function crear(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('username');
            $t->string('rol');
            $t->string('estado');
        });
        foreach (['areas' => 'ID_AREA', 'rol_empleados' => 'ID_ROL'] as $table => $pk) {
            Schema::create($table, function (Blueprint $t) use ($pk) {
                $t->integer($pk)->primary();
                $t->string('NOMBRE');
            });
        }
        Schema::create('empleados', function (Blueprint $t) {
            $t->integer('LEGAJO')->primary();
            $t->string('COLABORADOR');
            $t->string('ESTADO');
            $t->integer('ID_ROL');
            $t->integer('ID_AREA');
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Administración de prueba', 'username' => 'org-admin', 'rol' => 'Administrador/a', 'estado' => 'ACTIVO'],
            ['id' => 2, 'name' => 'Lectura de prueba', 'username' => 'org-lector', 'rol' => 'Colaborador/a', 'estado' => 'ACTIVO'],
            ['id' => 3, 'name' => 'Baja de prueba', 'username' => 'org-baja', 'rol' => 'Administrador/a', 'estado' => 'BAJA'],
        ]);
        DB::table('areas')->insert(['ID_AREA' => 1, 'NOMBRE' => 'Área de prueba']);
        DB::table('rol_empleados')->insert([
            ['ID_ROL' => 1, 'NOMBRE' => 'Director Médico'], ['ID_ROL' => 2, 'NOMBRE' => 'Cadete'], ['ID_ROL' => 3, 'NOMBRE' => 'Enfermero'],
        ]);
        DB::table('empleados')->insert([
            ['LEGAJO' => 100, 'COLABORADOR' => 'Martin Baldi', 'ESTADO' => 'ACTIVO', 'ID_ROL' => 1, 'ID_AREA' => 1],
            ['LEGAJO' => 101, 'COLABORADOR' => 'Luis Mendez', 'ESTADO' => 'ACTIVO', 'ID_ROL' => 3, 'ID_AREA' => 1],
            ['LEGAJO' => 102, 'COLABORADOR' => 'Luis Mendez', 'ESTADO' => 'ACTIVO', 'ID_ROL' => 3, 'ID_AREA' => 1],
            ['LEGAJO' => 103, 'COLABORADOR' => 'Juan Soni', 'ESTADO' => 'BAJA', 'ID_ROL' => 2, 'ID_AREA' => 1],
        ]);
        (require database_path('migrations/2026_10_01_100000_create_organigrama_tables.php'))->up();
    }

    public static function origen(): array
    {
        return json_decode(file_get_contents(base_path('organigrama/02_DATOS/organigrama-base.json')), true, flags: JSON_THROW_ON_ERROR);
    }
}
