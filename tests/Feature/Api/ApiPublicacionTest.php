<?php

namespace Tests\Feature\Api;

use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ApiPublicacionTest extends ApiTestCase
{
    public function test_feed_combina_ofertas_aprobadas_y_oportunidades_publicadas(): void
    {
        $empleador = User::factory()->empleador()->create();
        $this->crearOferta($empleador, ['titulo' => 'Empleo visible']);
        $this->crearOferta($empleador, [
            'titulo' => 'Empleo pendiente',
            'estado' => Oferta::ESTADO_PENDIENTE,
        ]);
        $this->crearOportunidad(['titulo' => 'Beca visible']);
        $this->crearOportunidad([
            'titulo' => 'Beca borrador',
            'estado' => Oportunidad::ESTADO_BORRADOR,
        ]);

        $response = $this->getJson('/api/publicaciones')->assertOk();

        $response->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [['key', 'tipo_publicacion', 'categoria', 'titulo', 'url']],
                'meta' => ['total'],
            ]);

        $titulos = collect($response->json('data'))->pluck('titulo')->all();

        $this->assertContains('Empleo visible', $titulos);
        $this->assertContains('Beca visible', $titulos);
        $this->assertNotContains('Empleo pendiente', $titulos);
        $this->assertNotContains('Beca borrador', $titulos);
    }

    public function test_filtro_por_categoria_en_el_feed(): void
    {
        $empleador = User::factory()->empleador()->create();
        $this->crearOferta($empleador);
        $this->crearOportunidad(['titulo' => 'Beca filtrada', 'tipo' => 'beca']);

        $this->getJson('/api/publicaciones?categoria=beca')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Beca filtrada');

        $this->getJson('/api/publicaciones?categoria=empleo')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.categoria', 'empleo');
    }

    public function test_detalle_publico_de_una_publicacion(): void
    {
        $empleador = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($empleador);
        $oportunidad = $this->crearOportunidad();

        $this->getJson('/api/publicaciones/oferta/'.$oferta->id)
            ->assertOk()
            ->assertJsonPath('data.key', 'oferta:'.$oferta->id)
            ->assertJsonPath('data.tipo_publicacion', 'oferta');

        $this->getJson('/api/publicaciones/oportunidad/'.$oportunidad->id)
            ->assertOk()
            ->assertJsonPath('data.tipo_publicacion', 'oportunidad');

        $this->getJson('/api/publicaciones/otro/1')->assertNotFound();
        $this->getJson('/api/publicaciones/oferta/999999')->assertNotFound();
    }

    public function test_empleador_crea_una_oferta_mediante_publicaciones(): void
    {
        Sanctum::actingAs(User::factory()->empleador()->create());

        $this->postJson('/api/publicaciones', $this->datosOferta())
            ->assertCreated()
            ->assertJsonPath('data.estado', Oferta::ESTADO_PENDIENTE);
    }

    public function test_administrador_crea_una_oportunidad_mediante_publicaciones(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/publicaciones', [
            'tipo_publicacion' => 'oportunidad',
            'titulo' => 'Convocatoria de prácticas',
            'tipo' => 'practica',
            'descripcion' => 'Prácticas remuneradas para estudiantes.',
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ])->assertCreated()
            ->assertJsonPath('data.titulo', 'Convocatoria de prácticas');
    }

    public function test_empleador_no_puede_crear_oportunidades_ni_postulante_ofertas(): void
    {
        Sanctum::actingAs(User::factory()->empleador()->create());

        $this->postJson('/api/publicaciones', [
            'tipo_publicacion' => 'oportunidad',
            'titulo' => 'No permitida',
            'tipo' => 'beca',
            'descripcion' => 'Sin permiso.',
        ])->assertForbidden();

        Sanctum::actingAs(User::factory()->postulante()->create());

        $this->postJson('/api/publicaciones', $this->datosOferta())
            ->assertForbidden();
    }

    public function test_actualizar_y_eliminar_publicaciones_respeta_la_propiedad(): void
    {
        $duenio = User::factory()->empleador()->create();
        $otro = User::factory()->empleador()->create();
        $oferta = $this->crearOferta($duenio, ['estado' => Oferta::ESTADO_APROBADO]);

        Sanctum::actingAs($otro);
        $this->putJson('/api/publicaciones/oferta/'.$oferta->id, $this->datosOferta())
            ->assertForbidden();
        $this->deleteJson('/api/publicaciones/oferta/'.$oferta->id)
            ->assertForbidden();

        Sanctum::actingAs($duenio);
        $this->putJson('/api/publicaciones/oferta/'.$oferta->id, $this->datosOferta([
            'titulo' => 'Título actualizado',
        ]))->assertOk()
            ->assertJsonPath('data.estado', Oferta::ESTADO_PENDIENTE);

        $this->deleteJson('/api/publicaciones/oferta/'.$oferta->id)
            ->assertOk();

        $this->assertDatabaseMissing('ofertas', ['id' => $oferta->id]);
    }

    public function test_tipo_de_publicacion_invalido_responde_422(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/publicaciones', ['tipo_publicacion' => 'noticia'])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['tipo_publicacion']]);
    }
}
