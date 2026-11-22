<?php

namespace Tests\Feature\Api;

use App\Models\Oferta;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ApiOfertaTest extends ApiTestCase
{
    public function test_listado_publico_solo_muestra_ofertas_aprobadas(): void
    {
        $empleador = User::factory()->empleador()->create();
        $aprobada = $this->crearOferta($empleador, ['titulo' => 'Oferta visible']);
        $pendiente = $this->crearOferta($empleador, [
            'titulo' => 'Oferta en revisión',
            'estado' => Oferta::ESTADO_PENDIENTE,
        ]);

        $this->getJson('/api/ofertas')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $aprobada->id)
            ->assertJsonPath('data.0.estado', Oferta::ESTADO_APROBADO)
            ->assertJsonMissing(['titulo' => $pendiente->titulo])
            ->assertJsonStructure(['meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    public function test_busqueda_por_titulo_ubicacion_y_categoria(): void
    {
        $empleador = User::factory()->empleador()->create();
        $this->crearOferta($empleador, ['titulo' => 'Desarrollador backend']);
        $this->crearOferta($empleador, [
            'titulo' => 'Cajera de tienda',
            'lugar' => 'Puquio',
            'tipo_trabajo' => 'Medio tiempo',
        ]);

        $this->getJson('/api/ofertas?titulo=Desarrollador')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Desarrollador backend');

        $this->getJson('/api/ofertas?ubicacion=Puquio')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Cajera de tienda');

        $this->getJson('/api/ofertas?categoria=Medio tiempo')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Cajera de tienda');

        $this->getJson('/api/ofertas?q=Cajera')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_recientes_solo_devuelve_aprobadas(): void
    {
        $empleador = User::factory()->empleador()->create();
        $this->crearOferta($empleador, ['titulo' => 'Reciente visible']);
        $this->crearOferta($empleador, [
            'titulo' => 'Reciente pendiente',
            'estado' => Oferta::ESTADO_PENDIENTE,
        ]);

        $this->getJson('/api/ofertas/recientes')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Reciente visible');
    }

    public function test_empleador_publica_oferta_y_queda_pendiente(): void
    {
        $empleador = User::factory()->empleador()->create();

        Sanctum::actingAs($empleador);

        $response = $this->postJson('/api/ofertas', $this->datosOferta());

        $response->assertCreated()
            ->assertJsonPath('data.estado', Oferta::ESTADO_PENDIENTE)
            ->assertJsonPath('data.empleador.id', $empleador->id);

        $this->assertDatabaseHas('ofertas', [
            'titulo' => 'Desarrollador web',
            'user_id' => $empleador->id,
            'estado' => Oferta::ESTADO_PENDIENTE,
        ]);
    }

    public function test_solo_el_rol_empleador_puede_publicar_ofertas(): void
    {
        Sanctum::actingAs(User::factory()->postulante()->create());

        $this->postJson('/api/ofertas', $this->datosOferta())
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_publicar_exige_correo_verificado(): void
    {
        Sanctum::actingAs(User::factory()->empleador()->unverified()->create());

        $this->postJson('/api/ofertas', $this->datosOferta())
            ->assertForbidden()
            ->assertJsonPath('message', 'Debes verificar tu correo electrónico antes de publicar ofertas.');
    }

    public function test_validaciones_responden_422_con_errores_por_campo(): void
    {
        Sanctum::actingAs(User::factory()->empleador()->create());

        $this->postJson('/api/ofertas', [
            'titulo' => '',
            'salario' => -5,
            'fecha_limite' => now()->subDay()->toDateString(),
        ])->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['titulo', 'descripcion', 'requisitos', 'lugar', 'tipo_trabajo', 'salario', 'fecha_limite'],
            ]);
    }

    public function test_oferta_pendiente_es_404_para_visitantes_y_visible_para_su_dueno(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador, ['estado' => Oferta::ESTADO_PENDIENTE]);

        $this->getJson('/api/ofertas/'.$oferta->id)
            ->assertNotFound()
            ->assertJsonPath('success', false);

        Sanctum::actingAs($empleador);

        $this->getJson('/api/ofertas/'.$oferta->id)
            ->assertOk()
            ->assertJsonPath('data.id', $oferta->id);
    }

    public function test_solo_el_dueno_actualiza_su_oferta_y_vuelve_a_pendiente(): void
    {
        $duenio = User::factory()->empleador()->create();
        $otro = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($duenio, ['estado' => Oferta::ESTADO_APROBADO]);

        Sanctum::actingAs($otro);
        $this->putJson('/api/ofertas/'.$oferta->id, $this->datosOferta())
            ->assertForbidden();

        Sanctum::actingAs($duenio);
        $this->putJson('/api/ofertas/'.$oferta->id, $this->datosOferta([
            'titulo' => 'Desarrollador web senior',
        ]))->assertOk()
            ->assertJsonPath('data.titulo', 'Desarrollador web senior')
            ->assertJsonPath('data.estado', Oferta::ESTADO_PENDIENTE);

        $this->assertDatabaseHas('ofertas', [
            'id' => $oferta->id,
            'titulo' => 'Desarrollador web senior',
            'estado' => Oferta::ESTADO_PENDIENTE,
        ]);
    }

    public function test_eliminar_oferta_solo_dueno_o_administrador(): void
    {
        $duenio = User::factory()->empleador()->create();
        $otro = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($duenio);

        Sanctum::actingAs($otro);
        $this->deleteJson('/api/ofertas/'.$oferta->id)->assertForbidden();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->deleteJson('/api/ofertas/'.$oferta->id)->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('ofertas', ['id' => $oferta->id]);
    }

    public function test_administrador_aprueba_una_oferta_y_aparece_en_el_listado_publico(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador, ['estado' => Oferta::ESTADO_PENDIENTE]);

        Sanctum::actingAs(User::factory()->admin()->create());

        $this->patchJson('/api/admin/ofertas/'.$oferta->id.'/estado', ['estado' => 'aprobado'])
            ->assertOk()
            ->assertJsonPath('data.estado', Oferta::ESTADO_APROBADO);

        $this->getJson('/api/ofertas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $oferta->id);
    }

    public function test_solo_un_administrador_puede_moderar_ofertas(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador, ['estado' => Oferta::ESTADO_PENDIENTE]);

        Sanctum::actingAs($empleador);

        $this->patchJson('/api/admin/ofertas/'.$oferta->id.'/estado', ['estado' => 'aprobado'])
            ->assertForbidden();
    }

    public function test_la_respuesta_no_expone_datos_personales_del_empleador(): void
    {
        $empleador = User::factory()->empleador()->create([
            'email' => 'privado@emplealocal.test',
            'telefono' => '999111222',
            'empresa' => 'Empresa Demo S.A.C.',
        ]);
        $this->crearOferta($empleador);

        $response = $this->getJson('/api/ofertas')->assertOk();

        $response->assertJsonPath('data.0.empleador.empresa', 'Empresa Demo S.A.C.');
        $response->assertJsonMissingPath('data.0.empleador.email');
        $response->assertJsonMissingPath('data.0.empleador.telefono');
        $response->assertJsonMissingPath('data.0.empleador.dni');
    }
}
