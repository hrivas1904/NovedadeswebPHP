<?php

namespace Tests\Feature;

use App\Http\Controllers\RRHH\PersonalController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PayrollRoleSelectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge();
        Schema::create('categ_empleados', function (Blueprint $table) {
            $table->integer('ID_CATEG')->primary();
        });
        Schema::create('rol_empleados', function (Blueprint $table) {
            $table->integer('ID_ROL')->primary();
            $table->integer('ID_CATEG');
            $table->string('NOMBRE');
        });
        DB::table('categ_empleados')->insert([['ID_CATEG' => 1], ['ID_CATEG' => 26]]);
        DB::table('rol_empleados')->insert([
            ['ID_ROL' => 7, 'ID_CATEG' => 1, 'NOMBRE' => 'Telefonista'],
            ['ID_ROL' => 8, 'ID_CATEG' => 1, 'NOMBRE' => 'Facturacion'],
        ]);
    }

    public function test_new_payroll_category_keeps_existing_roles_available(): void
    {
        $response = (new PersonalController)->listarRolesXCategoria(26);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            ['id_rol' => 8, 'nombre' => 'Facturacion'],
            ['id_rol' => 7, 'nombre' => 'Telefonista'],
        ], $response->getData(true));
        $this->assertSame(1, DB::table('rol_empleados')->where('ID_ROL', 7)->value('ID_CATEG'));
    }

    public function test_unknown_category_returns_no_roles(): void
    {
        $this->assertSame([], (new PersonalController)->listarRolesXCategoria(999)->getData(true));
    }
}
