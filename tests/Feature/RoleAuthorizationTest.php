<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** RF20: invitado es redirigido al login */
    public function test_invitado_no_accede_al_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    /** RF20 */
    public function test_postulante_no_accede_al_panel_admin(): void
    {
        $postulante = User::factory()->postulante()->create();

        $this->actingAs($postulante)->get('/admin/usuarios')->assertForbidden();
        $this->actingAs($postulante)->get('/admin/ofertas')->assertForbidden();
        $this->actingAs($postulante)->get('/admin/oportunidades')->assertForbidden();
    }

    /** RF20 */
    public function test_empleador_no_accede_al_panel_admin(): void
    {
        $empleador = User::factory()->empleador()->create();

        $this->actingAs($empleador)->get('/admin/usuarios')->assertForbidden();
        $this->actingAs($empleador)->get('/admin/publicidades')->assertForbidden();
    }

    /** RF20 */
    public function test_postulante_no_accede_a_rutas_de_empleador(): void
    {
        $postulante = User::factory()->postulante()->create();

        $this->actingAs($postulante)->get('/empleador/ofertas')->assertForbidden();
        $this->actingAs($postulante)->get('/empleador/ofertas/crear')->assertForbidden();
        $this->actingAs($postulante)->get('/empleador/postulantes')->assertForbidden();
    }

    /** RF20 */
    public function test_empleador_no_accede_a_postulacion_de_postulante(): void
    {
        $empleador = User::factory()->empleador()->create();

        $this->actingAs($empleador)->get('/mis-postulaciones')->assertForbidden();
    }

    /** RF20 */
    public function test_admin_accede_a_todo(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/usuarios')->assertOk();
        $this->actingAs($admin)->get('/admin/ofertas')->assertOk();
        $this->actingAs($admin)->get('/admin/oportunidades')->assertOk();
        $this->actingAs($admin)->get('/admin/publicidades')->assertOk();
        $this->actingAs($admin)->get('/dashboard')->assertOk();
    }

    /** RF20: el empleador no puede actualizar postulaciones de ofertas ajenas */
    public function test_empleador_no_actualiza_postulacion_ajena(): void
    {
        $otro = User::factory()->empleador()->create();
        $empleador = User::factory()->empleador()->create();
        $oferta = Oferta::create([
            'user_id' => $otro->id,
            'titulo' => 'Oferta ajena',
            'descripcion' => 'Descripción',
            'requisitos' => 'Requisitos',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Temporal',
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'estado' => 'aprobado',
        ]);
        $postulante = User::factory()->postulante()->create();
        $postulacion = Postulacion::create(['oferta_id' => $oferta->id, 'user_id' => $postulante->id, 'estado' => 'pendiente']);

        $this->actingAs($empleador)
            ->put(route('empleador.postulaciones.estado', $postulacion), ['estado' => 'aceptada'])
            ->assertForbidden();

        $this->assertDatabaseHas('postulaciones', ['id' => $postulacion->id, 'estado' => 'pendiente']);
    }
}
