<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class EddStructureTest extends TestCase
{
    private const ADMIN_PAGES = [
        'resumen', 'configuracion', 'configuracion.poblacion',
        'configuracion.evaluadores', 'configuracion.instrumento', 'reportes',
    ];

    private function usuario(string $rol, string $estado = 'ACTIVO', ?int $legajo = 90001): User
    {
        $user = new User(['name' => 'Persona de prueba', 'username' => 'edd-test', 'rol' => $rol]);
        $user->id = 90001;
        $user->estado = $estado;
        $user->legajo = $legajo;

        return $user;
    }

    public function test_every_edd_page_requires_authentication(): void
    {
        foreach (array_merge(self::ADMIN_PAGES, ['index', 'equipo', 'autoevaluacion', 'evaluacion.modelo']) as $page) {
            $this->get(route('rrhh.edd.'.$page))->assertRedirect(route('login'));
        }
    }

    public function test_rrhh_can_render_all_sections_without_institutional_tables(): void
    {
        $this->actingAs($this->usuario('Administrador/a'));
        $this->get(route('rrhh.edd.index'))->assertRedirect(route('rrhh.edd.resumen'));

        foreach (array_merge(self::ADMIN_PAGES, ['equipo', 'autoevaluacion', 'evaluacion.modelo']) as $page) {
            $this->get(route('rrhh.edd.'.$page))
                ->assertOk()
                ->assertSee('Diseño inicial')
                ->assertSee('Escala de 1 a 4');
        }
    }

    public function test_coordinators_land_on_their_team_and_cannot_access_rrhh_by_url(): void
    {
        foreach (['Coordinador/a', 'Coordinador/a L2'] as $rol) {
            $this->actingAs($this->usuario($rol));
            $this->get(route('rrhh.edd.index'))->assertRedirect(route('rrhh.edd.equipo'));
            $this->get(route('rrhh.edd.equipo'))
                ->assertOk()
                ->assertSee('Evaluación de desempeño')
                ->assertSee('Todavía no hay evaluaciones asignadas.')
                ->assertDontSee(route('rrhh.edd.configuracion'), false)
                ->assertDontSee(route('rrhh.edd.reportes'), false);
            $this->get(route('rrhh.edd.evaluacion.modelo'))->assertOk();
            $this->get(route('rrhh.edd.autoevaluacion'))->assertOk();

            foreach (self::ADMIN_PAGES as $page) {
                $this->get(route('rrhh.edd.'.$page))->assertForbidden();
            }
        }
    }

    public function test_collaborators_and_other_profiles_only_access_self_evaluation(): void
    {
        foreach (['Colaborador/a', 'Supervisor/a Calidad', 'Perfil no configurado'] as $rol) {
            $this->actingAs($this->usuario($rol));
            $this->get(route('rrhh.edd.index'))->assertRedirect(route('rrhh.edd.autoevaluacion'));
            $this->get(route('rrhh.edd.autoevaluacion'))
                ->assertOk()
                ->assertSee('Mi autoevaluación')
                ->assertDontSee(route('rrhh.edd.equipo'), false)
                ->assertDontSee(route('rrhh.edd.configuracion'), false);

            foreach (array_merge(self::ADMIN_PAGES, ['equipo', 'evaluacion.modelo']) as $page) {
                $this->get(route('rrhh.edd.'.$page))->assertForbidden();
            }
        }
    }

    public function test_inactive_account_is_denied_even_with_an_administrator_role(): void
    {
        $this->actingAs($this->usuario('Administrador/a', 'BAJA'));
        foreach (array_merge(self::ADMIN_PAGES, ['index', 'equipo', 'autoevaluacion', 'evaluacion.modelo']) as $page) {
            $this->get(route('rrhh.edd.'.$page))->assertForbidden();
        }
    }

    public function test_missing_legajo_is_reported_without_asking_for_another_person(): void
    {
        $this->actingAs($this->usuario('Colaborador/a', legajo: null));
        $this->get(route('rrhh.edd.autoevaluacion'))
            ->assertOk()
            ->assertSee('Tu cuenta todavía no tiene un legajo vinculado.')
            ->assertDontSee('data-edd-buscar', false);
    }

    public function test_prototype_does_not_accept_writes(): void
    {
        $this->actingAs($this->usuario('Administrador/a'));
        foreach (['configuracion', 'autoevaluacion', 'evaluacion.modelo'] as $page) {
            $this->postJson(route('rrhh.edd.'.$page), ['estado' => 'cerrada', 'puntaje' => 4])->assertStatus(405);
        }
    }
}
