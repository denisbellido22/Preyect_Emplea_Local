<?php

namespace Tests\Feature;

use App\Models\Oportunidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OportunidadConsultaTest extends TestCase
{
    use RefreshDatabase;

    /** RF15 */
    public function test_usuario_consulta_oportunidades_publicadas(): void
    {
        $user = User::factory()->postulante()->create();

        Oportunidad::create([
            'titulo' => 'Beca publicada',
            'tipo' => 'beca',
            'descripcion' => 'Beca disponible.',
            'estado' => 'publicado',
        ]);
        Oportunidad::create([
            'titulo' => 'Curso en borrador',
            'tipo' => 'curso',
            'descripcion' => 'Aún no visible.',
            'estado' => 'borrador',
        ]);

        $this->actingAs($user)->get(route('oportunidades.index'))
            ->assertOk()
            ->assertSee('Beca publicada')
            ->assertDontSee('Curso en borrador');
    }

    /** RF15 */
    public function test_usuario_ve_detalle_de_oportunidad(): void
    {
        $user = User::factory()->postulante()->create();
        $oportunidad = Oportunidad::create([
            'titulo' => 'Charlas de empleabilidad',
            'tipo' => 'charla',
            'descripcion' => 'Charla informativa.',
            'institucion' => 'Municipalidad',
            'estado' => 'publicado',
        ]);

        $this->actingAs($user)->get(route('oportunidades.show', $oportunidad))
            ->assertOk()
            ->assertSee('Charlas de empleabilidad')
            ->assertSee('Municipalidad');
    }

    /** RF15: un borrador no es visible por su URL */
    public function test_no_se_puede_ver_borrador(): void
    {
        $user = User::factory()->postulante()->create();
        $oportunidad = Oportunidad::create([
            'titulo' => 'Borrador privado',
            'tipo' => 'evento',
            'descripcion' => 'No visible.',
            'estado' => 'borrador',
        ]);

        $this->actingAs($user)->get(route('oportunidades.show', $oportunidad))->assertNotFound();
    }
}
