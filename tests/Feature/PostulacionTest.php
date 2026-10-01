<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostulacionTest extends TestCase
{
    use RefreshDatabase;

    private function ofertaAprobada(User $empleador): Oferta
    {
        return Oferta::create([
            'user_id' => $empleador->id,
            'titulo' => 'Asistente administrativo',
            'descripcion' => 'Apoyo administrativo.',
            'requisitos' => 'Estudios técnicos.',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Medio tiempo',
            'salario' => 900,
            'fecha_limite' => now()->addDays(10)->toDateString(),
            'estado' => 'aprobado',
        ]);
    }

    /** RF09 */
    public function test_postulante_postula_a_oferta_aprobada(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->ofertaAprobada($empleador);
        $postulante = User::factory()->postulante()->create();

        $response = $this->actingAs($postulante)->post(route('postulaciones.store', $oferta), [
            'mensaje' => 'Estoy interesada en la oferta.',
        ]);

        $response->assertRedirect(route('postulaciones.index'));
        $this->assertDatabaseHas('postulaciones', [
            'oferta_id' => $oferta->id,
            'user_id' => $postulante->id,
            'estado' => 'pendiente',
        ]);
    }

    /** RF09: sólo a ofertas aprobadas */
    public function test_no_postula_a_oferta_no_aprobada(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->ofertaAprobada($empleador);
        $oferta->update(['estado' => 'pendiente']);
        $postulante = User::factory()->postulante()->create();

        $this->actingAs($postulante)->post(route('postulaciones.store', $oferta));

        $this->assertDatabaseMissing('postulaciones', ['oferta_id' => $oferta->id, 'user_id' => $postulante->id]);
    }

    /** RF10: validación a nivel de aplicación */
    public function test_no_permite_postulacion_duplicada(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->ofertaAprobada($empleador);
        $postulante = User::factory()->postulante()->create();

        $this->actingAs($postulante)->post(route('postulaciones.store', $oferta));
        $response = $this->actingAs($postulante)->post(route('postulaciones.store', $oferta));

        $response->assertSessionHas('error');
        $this->assertEquals(1, Postulacion::where('oferta_id', $oferta->id)->where('user_id', $postulante->id)->count());
    }

    /** RF10: restricción a nivel de base de datos */
    public function test_base_de_datos_rechaza_duplicado(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->ofertaAprobada($empleador);
        $postulante = User::factory()->postulante()->create();

        Postulacion::create(['oferta_id' => $oferta->id, 'user_id' => $postulante->id, 'estado' => 'pendiente']);

        $this->expectException(QueryException::class);
        Postulacion::create(['oferta_id' => $oferta->id, 'user_id' => $postulante->id, 'estado' => 'pendiente']);
    }

    /** RF11 */
    public function test_postulante_consulta_sus_postulaciones(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->ofertaAprobada($empleador);
        $postulante = User::factory()->postulante()->create();
        Postulacion::create(['oferta_id' => $oferta->id, 'user_id' => $postulante->id, 'estado' => 'revisada']);

        $this->actingAs($postulante)->get(route('postulaciones.index'))
            ->assertOk()
            ->assertSee('Asistente administrativo')
            ->assertSee('Revisada');
    }

    /** RF12: el empleador ve postulaciones de sus ofertas */
    public function test_empleador_ve_postulantes_de_sus_ofertas(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->ofertaAprobada($empleador);
        $postulante = User::factory()->postulante()->create(['nombres' => 'Lucía', 'apellidos' => 'Rojas']);
        Postulacion::create(['oferta_id' => $oferta->id, 'user_id' => $postulante->id, 'estado' => 'pendiente']);

        $this->actingAs($empleador)->get(route('empleador.postulantes.index'))
            ->assertOk()
            ->assertSee('Lucía Rojas')
            ->assertSee('Asistente administrativo');
    }

    /** RF12: el empleador no ve postulaciones de ofertas ajenas */
    public function test_empleador_no_ve_postulantes_de_ofertas_ajenas(): void
    {
        $otro = User::factory()->empleador()->create();
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->ofertaAprobada($otro);
        $postulante = User::factory()->postulante()->create(['nombres' => 'Pedro', 'apellidos' => 'Ajeno']);
        Postulacion::create(['oferta_id' => $oferta->id, 'user_id' => $postulante->id, 'estado' => 'pendiente']);

        $this->actingAs($empleador)->get(route('empleador.postulantes.index'))
            ->assertOk()
            ->assertDontSee('Pedro Ajeno');
    }

    /** RF12/RF11: el empleador actualiza el estado de sus postulaciones */
    public function test_empleador_actualiza_estado_de_postulacion(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->ofertaAprobada($empleador);
        $postulante = User::factory()->postulante()->create();
        $postulacion = Postulacion::create(['oferta_id' => $oferta->id, 'user_id' => $postulante->id, 'estado' => 'pendiente']);

        $this->actingAs($empleador)->put(route('empleador.postulaciones.estado', $postulacion), ['estado' => 'aceptada']);

        $this->assertDatabaseHas('postulaciones', ['id' => $postulacion->id, 'estado' => 'aceptada']);
    }
}
