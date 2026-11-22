<?php

namespace Tests\Feature\Api;

use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Utilidades compartidas por las pruebas de la API REST.
 */
abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Datos válidos para crear una oferta.
     *
     * @return array<string, mixed>
     */
    protected function datosOferta(array $sobrescribir = []): array
    {
        return array_merge([
            'titulo' => 'Desarrollador web',
            'descripcion' => 'Desarrollo de aplicaciones con Laravel.',
            'requisitos' => 'Experiencia de un año en PHP.',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Tiempo completo',
            'salario' => 2500,
            'fecha_limite' => now()->addDays(15)->toDateString(),
        ], $sobrescribir);
    }

    /**
     * Crea una oferta directamente en la base de datos.
     */
    protected function crearOferta(User $empleador, array $sobrescribir = []): Oferta
    {
        return Oferta::create(array_merge($this->datosOferta([
            'user_id' => $empleador->id,
            'estado' => Oferta::ESTADO_APROBADO,
        ]), $sobrescribir));
    }

    /**
     * Crea una oportunidad publicada.
     */
    protected function crearOportunidad(array $sobrescribir = []): Oportunidad
    {
        return Oportunidad::create(array_merge([
            'user_id' => User::factory()->admin()->create()->id,
            'titulo' => 'Beca de estudios técnicos',
            'tipo' => 'beca',
            'descripcion' => 'Beca completa para estudiar en Coracora.',
            'institucion' => 'Instituto Parinacochas',
            'fecha' => now()->addMonth()->toDateString(),
            'lugar' => 'Coracora',
            'estado' => Oportunidad::ESTADO_PUBLICADO,
        ], $sobrescribir));
    }

    /**
     * Crea una postulación.
     */
    protected function crearPostulacion(Oferta $oferta, User $postulante, array $sobrescribir = []): Postulacion
    {
        return Postulacion::create(array_merge([
            'oferta_id' => $oferta->id,
            'user_id' => $postulante->id,
            'estado' => Postulacion::ESTADO_PENDIENTE,
        ], $sobrescribir));
    }
}
