<?php

namespace Tests\Feature\Api;

use App\Models\Oportunidad;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ApiOportunidadTest extends ApiTestCase
{
    public function test_listado_publico_solo_muestra_publicadas(): void
    {
        $publicada = $this->crearOportunidad(['titulo' => 'Beca publicada']);
        $borrador = $this->crearOportunidad([
            'titulo' => 'Beca en borrador',
            'estado' => Oportunidad::ESTADO_BORRADOR,
        ]);

        $this->getJson('/api/oportunidades')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $publicada->id)
            ->assertJsonPath('data.0.estado', Oportunidad::ESTADO_PUBLICADO)
            ->assertJsonMissing(['titulo' => $borrador->titulo]);
    }

    public function test_filtros_por_tipo_texto_y_recientes(): void
    {
        $this->crearOportunidad(['titulo' => 'Beca integral', 'tipo' => 'beca']);
        $this->crearOportunidad([
            'titulo' => 'Convocatoria municipal',
            'tipo' => 'convocatoria',
            'institucion' => 'Municipalidad de Coracora',
        ]);

        $this->getJson('/api/oportunidades?tipo=beca')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Beca integral');

        $this->getJson('/api/oportunidades?categoria=convocatoria')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Convocatoria municipal');

        $this->getJson('/api/oportunidades?q=Municipalidad')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/oportunidades/recientes')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_detalle_de_borrador_es_404_para_visitantes_y_visible_para_admin(): void
    {
        $oportunidad = $this->crearOportunidad([
            'estado' => Oportunidad::ESTADO_BORRADOR,
        ]);

        $this->getJson('/api/oportunidades/'.$oportunidad->id)
            ->assertNotFound();

        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson('/api/oportunidades/'.$oportunidad->id)
            ->assertOk()
            ->assertJsonPath('data.estado', Oportunidad::ESTADO_BORRADOR);
    }

    public function test_administrador_crea_actualiza_y_elimina_oportunidades(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $creada = $this->postJson('/api/oportunidades', [
            'titulo' => 'Examen de admisión',
            'tipo' => 'examen',
            'descripcion' => 'Examen para el instituto tecnológico.',
            'institucion' => 'IEST Parinacochas',
            'fecha' => now()->addDays(20)->toDateString(),
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ])->assertCreated()
            ->assertJsonPath('data.titulo', 'Examen de admisión')
            ->assertJsonPath('data.estado', Oportunidad::ESTADO_PUBLICADO);

        $id = $creada->json('data.id');

        $this->putJson('/api/oportunidades/'.$id, [
            'titulo' => 'Examen de admisión 2027',
            'tipo' => 'examen',
            'descripcion' => 'Examen para el instituto tecnológico.',
            'estado' => Oportunidad::ESTADO_BORRADOR,
        ])->assertOk()
            ->assertJsonPath('data.titulo', 'Examen de admisión 2027')
            ->assertJsonPath('data.estado', Oportunidad::ESTADO_BORRADOR);

        $this->deleteJson('/api/oportunidades/'.$id)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('oportunidades', ['id' => $id]);
    }

    public function test_solo_el_administrador_gestiona_oportunidades(): void
    {
        Sanctum::actingAs(User::factory()->empleador()->create());

        $this->postJson('/api/oportunidades', [
            'titulo' => 'Intento',
            'tipo' => 'curso',
            'descripcion' => 'No autorizado.',
        ])->assertForbidden();
    }

    public function test_tipo_invalido_responde_422(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/oportunidades', [
            'titulo' => 'Tipo incorrecto',
            'tipo' => 'misterioso',
            'descripcion' => 'No existe ese tipo.',
        ])->assertStatus(422)
            ->assertJsonStructure(['errors' => ['tipo']]);
    }

    public function test_visitante_no_puede_crear_oportunidades(): void
    {
        $this->postJson('/api/oportunidades', [
            'titulo' => 'Sin token',
            'tipo' => 'curso',
            'descripcion' => 'No autenticado.',
        ])->assertUnauthorized();
    }
}
