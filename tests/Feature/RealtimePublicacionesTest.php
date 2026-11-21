<?php

namespace Tests\Feature;

use App\Events\PublicacionActualizada;
use App\Events\PublicacionPublicada;
use App\Events\PublicacionRetirada;
use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimePublicacionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // En phpunit el broadcaster es "null"; aquí probamos con el canal real.
        config(['broadcasting.default' => 'reverb']);
        Event::fake([PublicacionPublicada::class, PublicacionActualizada::class, PublicacionRetirada::class]);
    }

    private function datosOferta(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Asistente administrativo',
            'descripcion' => 'Apoyo en oficina y atención al público.',
            'requisitos' => 'Secundaria completa.',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Tiempo completo',
            'salario' => 1200,
            'fecha_limite' => now()->addDays(10)->toDateString(),
        ], $extra);
    }

    /** La vista pública muestra la nueva sección solo con publicaciones visibles. */
    public function test_portada_muestra_ultimas_publicaciones_aprobadas_y_oculta_borradores(): void
    {
        $empleador = User::factory()->empleador()->create();

        Oferta::create($this->datosOferta([
            'titulo' => 'Oferta pública visible',
            'user_id' => $empleador->id,
            'estado' => Oferta::ESTADO_APROBADO,
        ]));
        Oferta::create($this->datosOferta([
            'titulo' => 'Oferta pendiente oculta',
            'user_id' => $empleador->id,
            'estado' => Oferta::ESTADO_PENDIENTE,
        ]));
        Oportunidad::create([
            'titulo' => 'Beca publicada visible',
            'tipo' => 'beca',
            'descripcion' => 'Beca de estudios.',
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ]);
        Oportunidad::create([
            'titulo' => 'Beca en borrador oculta',
            'tipo' => 'beca',
            'descripcion' => 'Aún no visible.',
            'estado' => Oportunidad::ESTADO_BORRADOR,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Últimas oportunidades laborales y académicas')
            ->assertSee('Oferta pública visible')
            ->assertSee('Beca publicada visible')
            ->assertDontSee('Oferta pendiente oculta')
            ->assertDontSee('Beca en borrador oculta');
    }

    /** La más reciente aparece primero en la portada. */
    public function test_portada_ordena_de_la_mas_reciente_a_la_mas_antigua(): void
    {
        $antigua = new Oportunidad([
            'titulo' => 'Publicación antigua',
            'tipo' => 'beca',
            'descripcion' => 'Registrada primero.',
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ]);
        $antigua->created_at = now()->subHours(3);
        $antigua->save();

        Oportunidad::create([
            'titulo' => 'Publicación reciente',
            'tipo' => 'convocatoria',
            'descripcion' => 'Registrada después.',
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'Publicación antigua'),
            strpos($html, 'Publicación reciente'),
            'La publicación más reciente debe aparecer primero en la portada.'
        );
    }

    /** Un visitante puede consultar las oportunidades publicadas sin iniciar sesión. */
    public function test_visitante_accede_a_oportunidades_publicas(): void
    {
        Oportunidad::create([
            'titulo' => 'Convocatoria abierta',
            'tipo' => 'convocatoria',
            'descripcion' => 'Convocatoria pública.',
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ]);

        $this->get(route('oportunidades.index'))
            ->assertOk()
            ->assertSee('Convocatoria abierta');

        $this->get(route('oportunidades.show', Oportunidad::first()))->assertOk();
    }

    /** Una oferta pasa a la portada sólo después de ser aprobada. */
    public function test_aprobar_oferta_emite_evento_publico_con_payload_seguro(): void
    {
        $admin = User::factory()->admin()->create();
        $empleador = User::factory()->empleador()->create(['empresa' => 'Constructora Andina']);
        $oferta = Oferta::create($this->datosOferta([
            'user_id' => $empleador->id,
            'estado' => Oferta::ESTADO_PENDIENTE,
        ]));

        Event::assertNotDispatched(PublicacionPublicada::class);

        $this->actingAs($admin)
            ->post(route('admin.ofertas.aprobar', $oferta))
            ->assertRedirect();

        Event::assertDispatched(PublicacionPublicada::class, function (PublicacionPublicada $evento) use ($oferta, $empleador) {
            $publicacion = $evento->publicacion;

            return $publicacion['key'] === 'oferta:'.$oferta->id
                && $publicacion['categoria'] === 'empleo'
                && $publicacion['institucion'] === 'Constructora Andina'
                && $publicacion['estado'] === Oferta::ESTADO_APROBADO
                // Nunca se exponen datos privados del empleador.
                && ! str_contains(json_encode($publicacion, JSON_UNESCAPED_UNICODE), $empleador->email)
                && ! array_key_exists('email', $publicacion);
        });
    }

    /** Publicar una beca emite el evento y los borradores no se transmiten. */
    public function test_crear_beca_publicada_emite_evento_y_borrador_no(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.oportunidades.store'), [
            'titulo' => 'Beca de posgrado',
            'tipo' => 'beca',
            'descripcion' => 'Beca completa para egresados.',
            'institucion' => 'Universidad Nacional',
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ])->assertRedirect();

        Event::assertDispatched(PublicacionPublicada::class, function (PublicacionPublicada $evento) {
            return $evento->publicacion['categoria'] === 'beca'
                && $evento->publicacion['titulo'] === 'Beca de posgrado';
        });

        $this->actingAs($admin)->post(route('admin.oportunidades.store'), [
            'titulo' => 'Beca no publicada',
            'tipo' => 'beca',
            'descripcion' => 'Se guarda como borrador.',
            'estado' => Oportunidad::ESTADO_BORRADOR,
        ])->assertRedirect();

        Event::assertNotDispatched(PublicacionPublicada::class, function (PublicacionPublicada $evento) {
            return $evento->publicacion['titulo'] === 'Beca no publicada';
        });
    }

    /** Editar una publicación visible emite actualización y despublicarla la retira. */
    public function test_editar_y_despublicar_oportunidad_emite_eventos_correctos(): void
    {
        $admin = User::factory()->admin()->create();
        $oportunidad = Oportunidad::create([
            'titulo' => 'Examen de admisión',
            'tipo' => 'examen',
            'descripcion' => 'Examen programado.',
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ]);

        Event::fake([PublicacionActualizada::class, PublicacionRetirada::class]);

        $this->actingAs($admin)->put(route('admin.oportunidades.update', $oportunidad), [
            'titulo' => 'Examen de admisión reprogramado',
            'tipo' => 'examen',
            'descripcion' => 'Nueva fecha de examen.',
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ])->assertRedirect();

        Event::assertDispatched(PublicacionActualizada::class, function (PublicacionActualizada $evento) use ($oportunidad) {
            return $evento->publicacion['key'] === 'oportunidad:'.$oportunidad->id
                && $evento->publicacion['titulo'] === 'Examen de admisión reprogramado';
        });

        $this->actingAs($admin)->put(route('admin.oportunidades.update', $oportunidad), [
            'titulo' => 'Examen de admisión reprogramado',
            'tipo' => 'examen',
            'descripcion' => 'Vuelve a borrador.',
            'estado' => Oportunidad::ESTADO_BORRADOR,
        ])->assertRedirect();

        Event::assertDispatched(PublicacionRetirada::class, function (PublicacionRetirada $evento) use ($oportunidad) {
            return $evento->key === 'oportunidad:'.$oportunidad->id;
        });
    }

    /** Eliminar una oferta aprobada la retira de la vista pública. */
    public function test_eliminar_oferta_aprobada_emite_evento_de_retiro(): void
    {
        $admin = User::factory()->admin()->create();
        $empleador = User::factory()->empleador()->create();
        $oferta = Oferta::create($this->datosOferta([
            'user_id' => $empleador->id,
            'estado' => Oferta::ESTADO_APROBADO,
        ]));

        Event::fake([PublicacionRetirada::class]);

        $this->actingAs($admin)
            ->delete(route('admin.ofertas.destroy', $oferta))
            ->assertRedirect();

        Event::assertDispatched(PublicacionRetirada::class, function (PublicacionRetirada $evento) use ($oferta) {
            return $evento->key === 'oferta:'.$oferta->id;
        });
    }
}
