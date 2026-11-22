<?php

namespace Tests\Feature\Api;

use App\Models\Oferta;
use App\Models\Postulacion;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ApiPostulacionTest extends ApiTestCase
{
    public function test_postulante_se_postula_a_una_oferta_aprobada(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador);
        $postulante = User::factory()->postulante()->create();

        Sanctum::actingAs($postulante);

        $this->postJson('/api/ofertas/'.$oferta->id.'/postulaciones', [
            'mensaje' => 'Tengo experiencia en el puesto.',
        ])->assertCreated()
            ->assertJsonPath('data.estado', Postulacion::ESTADO_PENDIENTE)
            ->assertJsonPath('data.oferta.id', $oferta->id);

        $this->assertDatabaseHas('postulaciones', [
            'oferta_id' => $oferta->id,
            'user_id' => $postulante->id,
            'estado' => Postulacion::ESTADO_PENDIENTE,
        ]);
    }

    public function test_no_se_permiten_postulaciones_duplicadas(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador);
        $postulante = User::factory()->postulante()->create();

        Sanctum::actingAs($postulante);

        $this->postJson('/api/ofertas/'.$oferta->id.'/postulaciones')->assertCreated();

        $this->postJson('/api/ofertas/'.$oferta->id.'/postulaciones')
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['oferta_id']]);

        $this->assertSame(1, Postulacion::where('oferta_id', $oferta->id)
            ->where('user_id', $postulante->id)
            ->count());
    }

    public function test_no_se_puede_postular_a_una_oferta_no_aprobada(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador, ['estado' => Oferta::ESTADO_PENDIENTE]);

        Sanctum::actingAs(User::factory()->postulante()->create());

        $this->postJson('/api/ofertas/'.$oferta->id.'/postulaciones')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Sólo puedes postular a ofertas aprobadas y publicadas.');

        $this->assertDatabaseCount('postulaciones', 0);
    }

    public function test_solo_el_rol_postulante_puede_postularse(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador);

        Sanctum::actingAs($empleador);

        $this->postJson('/api/ofertas/'.$oferta->id.'/postulaciones')
            ->assertForbidden();
    }

    public function test_postular_exige_correo_verificado(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador);

        Sanctum::actingAs(User::factory()->postulante()->unverified()->create());

        $this->postJson('/api/ofertas/'.$oferta->id.'/postulaciones')
            ->assertForbidden();
    }

    public function test_el_postulante_solo_ve_sus_propias_postulaciones(): void
    {
        $empleador = User::factory()->empleador()->create();
        $ofertaUno = $this->crearOferta($empleador, ['titulo' => 'Puesto uno']);
        $ofertaDos = $this->crearOferta($empleador, ['titulo' => 'Puesto dos']);
        $postulante = User::factory()->postulante()->create();
        $otro = User::factory()->postulante()->create();

        $this->crearPostulacion($ofertaUno, $postulante);
        $this->crearPostulacion($ofertaDos, $otro);

        Sanctum::actingAs($postulante);

        $this->getJson('/api/postulaciones')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.oferta.titulo', 'Puesto uno');
    }

    public function test_el_empleador_consulta_postulantes_de_sus_ofertas(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador);
        $postulante = User::factory()->postulante()->create([
            'nombres' => 'Lucía',
            'apellidos' => 'Rojas',
        ]);
        $this->crearPostulacion($oferta, $postulante);

        Sanctum::actingAs($empleador);

        $this->getJson('/api/ofertas/'.$oferta->id.'/postulantes')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.postulante.nombre', 'Lucía Rojas');

        $this->getJson('/api/postulaciones')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_otro_empleador_no_puede_ver_postulantes_ajenos(): void
    {
        $duenio = User::factory()->empleador()->create();
        $otro = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($duenio);
        $this->crearPostulacion($oferta, User::factory()->postulante()->create());

        Sanctum::actingAs($otro);

        $this->getJson('/api/ofertas/'.$oferta->id.'/postulantes')
            ->assertForbidden();

        $this->getJson('/api/postulaciones')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_el_empleador_actualiza_el_estado_de_una_postulacion(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador);
        $postulacion = $this->crearPostulacion($oferta, User::factory()->postulante()->create());

        Sanctum::actingAs($empleador);

        $this->patchJson('/api/postulaciones/'.$postulacion->id.'/estado', [
            'estado' => Postulacion::ESTADO_ACEPTADA,
        ])->assertOk()
            ->assertJsonPath('data.estado', Postulacion::ESTADO_ACEPTADA);

        $this->assertDatabaseHas('postulaciones', [
            'id' => $postulacion->id,
            'estado' => Postulacion::ESTADO_ACEPTADA,
        ]);
    }

    public function test_otro_empleador_o_el_postulante_no_pueden_cambiar_el_estado(): void
    {
        $duenio = User::factory()->empleador()->create();
        $otro = User::factory()->empleador()->create();
        $postulante = User::factory()->postulante()->create();
        $oferta = $this->crearOferta($duenio);
        $postulacion = $this->crearPostulacion($oferta, $postulante);

        Sanctum::actingAs($otro);
        $this->patchJson('/api/postulaciones/'.$postulacion->id.'/estado', ['estado' => 'aceptada'])
            ->assertForbidden();

        Sanctum::actingAs($postulante);
        $this->patchJson('/api/postulaciones/'.$postulacion->id.'/estado', ['estado' => 'aceptada'])
            ->assertForbidden();

        $this->assertDatabaseHas('postulaciones', [
            'id' => $postulacion->id,
            'estado' => Postulacion::ESTADO_PENDIENTE,
        ]);
    }

    public function test_estado_invalido_responde_422(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador);
        $postulacion = $this->crearPostulacion($oferta, User::factory()->postulante()->create());

        Sanctum::actingAs($empleador);

        $this->patchJson('/api/postulaciones/'.$postulacion->id.'/estado', ['estado' => 'contratada'])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['estado']]);
    }

    public function test_consultar_una_postulacion_ajena_responde_403(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador);
        $postulacion = $this->crearPostulacion($oferta, User::factory()->postulante()->create());

        Sanctum::actingAs(User::factory()->postulante()->create());

        $this->getJson('/api/postulaciones/'.$postulacion->id)
            ->assertForbidden();
    }
}
