<?php

namespace Tests\Feature;

use App\Models\Publicidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicidadEmpleadorTest extends TestCase
{
    use RefreshDatabase;

    /** RF16 */
    public function test_empleador_registra_solicitud_pendiente(): void
    {
        $empleador = User::factory()->empleador()->create();

        $response = $this->actingAs($empleador)->post(route('empleador.publicidades.store'), [
            'titulo' => 'Promoción de verano',
            'descripcion' => 'Descuentos de temporada.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(15)->toDateString(),
            'imagen' => $this->pngSubible('promo.png'),
        ]);

        $response->assertRedirect(route('empleador.publicidades.index'));

        $publicidad = Publicidad::where('titulo', 'Promoción de verano')->first();
        $this->assertNotNull($publicidad);
        $this->assertEquals('pendiente', $publicidad->estado);
        $this->assertEquals($empleador->id, $publicidad->user_id);

        @unlink(public_path($publicidad->imagen));
    }

    /** RF16 */
    public function test_empleador_ve_sus_solicitudes(): void
    {
        $empleador = User::factory()->empleador()->create();
        Publicidad::create([
            'user_id' => $empleador->id,
            'titulo' => 'Mi anuncio',
            'descripcion' => 'Descripción',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($empleador)->get(route('empleador.publicidades.index'))
            ->assertOk()
            ->assertSee('Mi anuncio');
    }
}
