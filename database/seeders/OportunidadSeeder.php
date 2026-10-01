<?php

namespace Database\Seeders;

use App\Models\Oportunidad;
use App\Models\User;
use Database\Seeders\Concerns\CreatesPlaceholderImages;
use Illuminate\Database\Seeder;

class OportunidadSeeder extends Seeder
{
    use CreatesPlaceholderImages;

    /**
     * Oportunidades de prueba: becas, cursos, charlas y eventos.
     */
    public function run(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $oportunidades = [
            [
                'titulo' => 'Beca de estudios técnicos 2026',
                'tipo' => 'beca',
                'descripcion' => 'Beca completa para jóvenes de la provincia de Parinacochas que deseen estudiar una carrera técnica.',
                'institucion' => 'Instituto Tecnológico de Parinacochas',
                'fecha' => now()->addDays(40)->toDateString(),
                'lugar' => 'Coracora',
                'enlace' => 'https://example.com/beca',
                'estado' => Oportunidad::ESTADO_PUBLICADO,
                'color' => [15, 118, 110],
            ],
            [
                'titulo' => 'Curso gratuito de ofimática',
                'tipo' => 'curso',
                'descripcion' => 'Curso básico de Word, Excel y PowerPoint orientado al mundo laboral.',
                'institucion' => 'Municipalidad Provincial de Parinacochas',
                'fecha' => now()->addDays(10)->toDateString(),
                'lugar' => 'Puquio',
                'estado' => Oportunidad::ESTADO_PUBLICADO,
                'color' => [245, 158, 11],
            ],
            [
                'titulo' => 'Charla: cómo preparar tu CV',
                'tipo' => 'charla',
                'descripcion' => 'Charla informativa sobre cómo elaborar un currículum vitae efectivo y afrontar una entrevista.',
                'institucion' => 'Emplea Local',
                'fecha' => now()->addDays(5)->toDateString(),
                'lugar' => 'Coracora',
                'estado' => Oportunidad::ESTADO_PUBLICADO,
                'color' => [59, 130, 246],
            ],
            [
                'titulo' => 'Capacitación en emprendimiento',
                'tipo' => 'capacitacion',
                'descripcion' => 'Programa de capacitación para emprendedores locales (borrador, aún no publicado).',
                'institucion' => 'Gobierno Regional',
                'fecha' => now()->addDays(30)->toDateString(),
                'lugar' => 'Coracora',
                'estado' => Oportunidad::ESTADO_BORRADOR,
                'color' => [107, 114, 128],
            ],
        ];

        foreach ($oportunidades as $i => $data) {
            $color = $data['color'];
            unset($data['color']);

            $data['imagen'] = $this->crearPng('oportunidades', 'seed_oportunidad_'.$i.'.png', 400, 240, $color);
            $data['user_id'] = $admin?->id;

            Oportunidad::updateOrCreate(['titulo' => $data['titulo']], $data);
        }
    }
}
