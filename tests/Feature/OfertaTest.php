<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfertaTest extends TestCase
{
    use RefreshDatabase;

    private function datosOferta(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Albañil de obra',
            'descripcion' => 'Trabajos de albañilería en obra civil.',
            'requisitos' => 'Experiencia mínima de 1 año.',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Tiempo completo',
            'salario' => 1200,
            'fecha_limite' => now()->addDays(10)->toDateString(),
        ], $extra);
    }

    /** RF04 */
    public function test_empleador_crea_oferta_con_estado_pendiente(): void
    {
        $empleador = User::factory()->empleador()->create();

        $response = $this->actingAs($empleador)->post(route('empleador.ofertas.store'), $this->datosOferta());

        $response->assertRedirect(route('empleador.ofertas.index'));
        $this->assertDatabaseHas('ofertas', [
            'titulo' => 'Albañil de obra',
            'user_id' => $empleador->id,
            'estado' => 'pendiente',
        ]);
    }

    /** RF04: validación del lado del servidor */
    public function test_crear_oferta_requiere_campos_obligatorios(): void
    {
        $empleador = User::factory()->empleador()->create();

        $response = $this->actingAs($empleador)->post(route('empleador.ofertas.store'), []);

        $response->assertSessionHasErrors(['titulo', 'descripcion', 'requisitos', 'lugar', 'tipo_trabajo', 'fecha_limite']);
    }

    /** RF05 */
    public function test_empleador_ve_sus_ofertas(): void
    {
        $empleador = User::factory()->empleador()->create();
        Oferta::create($this->datosOferta(['user_id' => $empleador->id, 'estado' => 'pendiente']));

        $this->actingAs($empleador)->get(route('empleador.ofertas.index'))
            ->assertOk()
            ->assertSee('Albañil de obra');
    }

    /** RF05 */
    public function test_empleador_edita_su_oferta_y_vuelve_a_pendiente(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = Oferta::create($this->datosOferta(['user_id' => $empleador->id, 'estado' => 'aprobado']));

        $response = $this->actingAs($empleador)->put(route('empleador.ofertas.update', $oferta), $this->datosOferta([
            'titulo' => 'Albañil actualizado',
        ]));

        $response->assertRedirect(route('empleador.ofertas.index'));
        $this->assertDatabaseHas('ofertas', [
            'id' => $oferta->id,
            'titulo' => 'Albañil actualizado',
            'estado' => 'pendiente',
        ]);
    }

    /** RF05: no puede modificar ofertas de otro empleador */
    public function test_empleador_no_puede_editar_oferta_ajena(): void
    {
        $otro = User::factory()->empleador()->create();
        $empleador = User::factory()->empleador()->create();
        $oferta = Oferta::create($this->datosOferta(['user_id' => $otro->id, 'estado' => 'aprobado']));

        $this->actingAs($empleador)->get(route('empleador.ofertas.edit', $oferta))->assertForbidden();
        $this->actingAs($empleador)->put(route('empleador.ofertas.update', $oferta), $this->datosOferta())->assertForbidden();
    }

    /** RF05 */
    public function test_empleador_elimina_su_oferta(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = Oferta::create($this->datosOferta(['user_id' => $empleador->id, 'estado' => 'pendiente']));

        $response = $this->actingAs($empleador)->delete(route('empleador.ofertas.destroy', $oferta));

        $response->assertRedirect(route('empleador.ofertas.index'));
        $this->assertDatabaseMissing('ofertas', ['id' => $oferta->id]);
    }

    /** RF07 */
    public function test_busqueda_por_titulo(): void
    {
        $empleador = User::factory()->empleador()->create();
        Oferta::create($this->datosOferta(['titulo' => 'Carpintero especializado', 'user_id' => $empleador->id, 'estado' => 'aprobado']));
        Oferta::create($this->datosOferta(['titulo' => 'Vendedor de tienda', 'user_id' => $empleador->id, 'estado' => 'aprobado']));

        $response = $this->get('/ofertas?q=Carpintero');
        $response->assertOk()->assertSee('Carpintero especializado')->assertDontSee('Vendedor de tienda');
    }

    /** RF07 */
    public function test_busqueda_por_lugar(): void
    {
        $empleador = User::factory()->empleador()->create();
        Oferta::create($this->datosOferta(['titulo' => 'Oferta uno', 'lugar' => 'Puyusca', 'user_id' => $empleador->id, 'estado' => 'aprobado']));
        Oferta::create($this->datosOferta(['titulo' => 'Oferta dos', 'lugar' => 'Coracora', 'user_id' => $empleador->id, 'estado' => 'aprobado']));

        $this->get('/ofertas?q=Puyusca')->assertOk()->assertSee('Oferta uno')->assertDontSee('Oferta dos');
    }

    /** RF07 */
    public function test_busqueda_por_tipo_de_trabajo(): void
    {
        $empleador = User::factory()->empleador()->create();
        Oferta::create($this->datosOferta(['titulo' => 'Oferta temporal', 'tipo_trabajo' => 'Temporal', 'user_id' => $empleador->id, 'estado' => 'aprobado']));
        Oferta::create($this->datosOferta(['titulo' => 'Oferta completa', 'tipo_trabajo' => 'Tiempo completo', 'user_id' => $empleador->id, 'estado' => 'aprobado']));

        $this->get('/ofertas?q=Temporal')->assertOk()->assertSee('Oferta temporal')->assertDontSee('Oferta completa');
    }

    /** RF07: sólo se muestran ofertas aprobadas */
    public function test_no_lista_ofertas_no_aprobadas(): void
    {
        $empleador = User::factory()->empleador()->create();
        Oferta::create($this->datosOferta(['titulo' => 'Oferta pendiente', 'user_id' => $empleador->id, 'estado' => 'pendiente']));

        $this->get('/ofertas')->assertOk()->assertDontSee('Oferta pendiente');
    }

    /** RF08 */
    public function test_detalle_muestra_campos_de_la_oferta(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = Oferta::create($this->datosOferta(['user_id' => $empleador->id, 'estado' => 'aprobado']));

        $this->get(route('ofertas.show', $oferta))
            ->assertOk()
            ->assertSee('Albañil de obra')
            ->assertSee('Coracora')
            ->assertSee('Tiempo completo')
            ->assertSee('1,200');
    }
}
