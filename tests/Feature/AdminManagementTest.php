<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Models\Publicidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    /** RF06 */
    public function test_admin_aprueba_y_rechaza_ofertas(): void
    {
        $admin = User::factory()->admin()->create();
        $empleador = User::factory()->empleador()->create();
        $oferta = Oferta::create([
            'user_id' => $empleador->id,
            'titulo' => 'Oferta por moderar',
            'descripcion' => 'Descripción',
            'requisitos' => 'Requisitos',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Temporal',
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $this->actingAs($admin)->post(route('admin.ofertas.aprobar', $oferta));
        $this->assertDatabaseHas('ofertas', ['id' => $oferta->id, 'estado' => 'aprobado']);

        $this->actingAs($admin)->post(route('admin.ofertas.rechazar', $oferta));
        $this->assertDatabaseHas('ofertas', ['id' => $oferta->id, 'estado' => 'rechazado']);
    }

    /** RF06 */
    public function test_admin_edita_y_elimina_oferta(): void
    {
        $admin = User::factory()->admin()->create();
        $empleador = User::factory()->empleador()->create();
        $oferta = Oferta::create([
            'user_id' => $empleador->id,
            'titulo' => 'Oferta editable',
            'descripcion' => 'Descripción',
            'requisitos' => 'Requisitos',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Temporal',
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'estado' => 'pendiente',
        ]);

        $this->actingAs($admin)->put(route('admin.ofertas.update', $oferta), [
            'titulo' => 'Oferta editada por admin',
            'descripcion' => 'Descripción',
            'requisitos' => 'Requisitos',
            'lugar' => 'Puquio',
            'tipo_trabajo' => 'Temporal',
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'estado' => 'aprobado',
        ]);
        $this->assertDatabaseHas('ofertas', ['id' => $oferta->id, 'titulo' => 'Oferta editada por admin', 'estado' => 'aprobado']);

        $this->actingAs($admin)->delete(route('admin.ofertas.destroy', $oferta));
        $this->assertDatabaseMissing('ofertas', ['id' => $oferta->id]);
    }

    /** RF13 */
    public function test_admin_lista_usuarios_identificando_rol(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->empleador()->create(['nombres' => 'Empleador Uno', 'apellidos' => 'Prueba']);

        $this->actingAs($admin)->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee('Empleador Uno Prueba')
            ->assertSee('Empleador');
    }

    /** RF13 */
    public function test_admin_cambia_rol_de_un_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $usuario = User::factory()->empleador()->create();

        $this->actingAs($admin)->put(route('admin.usuarios.update', $usuario), [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'role' => 'postulante',
        ]);

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'role' => 'postulante']);
    }

    /** RF13 */
    public function test_admin_elimina_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $usuario = User::factory()->postulante()->create();

        $this->actingAs($admin)->delete(route('admin.usuarios.destroy', $usuario));

        $this->assertDatabaseMissing('users', ['id' => $usuario->id]);
    }

    /** RF19 */
    public function test_dashboard_admin_muestra_metricas_reales(): void
    {
        $admin = User::factory()->admin()->create();
        $empleador = User::factory()->empleador()->create();
        User::factory()->postulante()->create();
        Oferta::create([
            'user_id' => $empleador->id,
            'titulo' => 'Oferta dashboard',
            'descripcion' => 'Descripción',
            'requisitos' => 'Requisitos',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Temporal',
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'estado' => 'aprobado',
        ]);
        Oportunidad::create([
            'titulo' => 'Beca dashboard',
            'tipo' => 'beca',
            'descripcion' => 'Descripción',
            'estado' => 'publicado',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panel del Administrador')
            ->assertSee('Oferta dashboard');

        // Las cantidades provienen realmente de la base de datos.
        $this->assertSame(3, User::count());
        $this->assertSame(1, Oferta::count());
        $this->assertSame(1, Oportunidad::count());

        $response->assertViewHas('metricas', function (array $metricas) {
            return $metricas['usuarios'] === 3
                && $metricas['ofertas'] === 1
                && $metricas['oportunidades'] === 1
                && $metricas['postulaciones'] === 0;
        });
    }

    /** RF14 */
    public function test_admin_crea_edita_y_elimina_oportunidad(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.oportunidades.store'), [
            'titulo' => 'Curso de electricidad',
            'tipo' => 'curso',
            'descripcion' => 'Curso básico de electricidad.',
            'institucion' => 'Instituto Demo',
            'fecha' => now()->addDays(20)->toDateString(),
            'lugar' => 'Coracora',
            'enlace' => 'https://example.com/curso',
            'estado' => 'publicado',
        ]);

        $oportunidad = Oportunidad::where('titulo', 'Curso de electricidad')->first();
        $this->assertNotNull($oportunidad);

        $this->actingAs($admin)->put(route('admin.oportunidades.update', $oportunidad), [
            'titulo' => 'Curso de electricidad avanzado',
            'tipo' => 'capacitacion',
            'descripcion' => 'Curso avanzado.',
            'estado' => 'borrador',
        ]);
        $this->assertDatabaseHas('oportunidades', ['id' => $oportunidad->id, 'titulo' => 'Curso de electricidad avanzado', 'tipo' => 'capacitacion']);

        $this->actingAs($admin)->delete(route('admin.oportunidades.destroy', $oportunidad));
        $this->assertDatabaseMissing('oportunidades', ['id' => $oportunidad->id]);
    }

    /** RF14: validación de tipo */
    public function test_oportunidad_valida_tipo(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.oportunidades.store'), [
            'titulo' => 'Tipo inválido',
            'tipo' => 'inventado',
            'descripcion' => 'Descripción',
            'estado' => 'publicado',
        ]);

        $response->assertSessionHasErrors('tipo');
    }

    /** RF17 */
    public function test_admin_gestiona_ciclo_de_publicidad(): void
    {
        $admin = User::factory()->admin()->create();
        $empleador = User::factory()->empleador()->create();
        $publicidad = Publicidad::create([
            'user_id' => $empleador->id,
            'titulo' => 'Anuncio demo',
            'descripcion' => 'Descripción del anuncio.',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($admin)->post(route('admin.publicidades.aprobar', $publicidad));
        $this->assertDatabaseHas('publicidades', ['id' => $publicidad->id, 'estado' => 'aprobado']);

        $this->actingAs($admin)->post(route('admin.publicidades.finalizar', $publicidad));
        $this->assertDatabaseHas('publicidades', ['id' => $publicidad->id, 'estado' => 'finalizado']);

        $this->actingAs($admin)->post(route('admin.publicidades.rechazar', $publicidad));
        $this->assertDatabaseHas('publicidades', ['id' => $publicidad->id, 'estado' => 'rechazado']);
    }
}
