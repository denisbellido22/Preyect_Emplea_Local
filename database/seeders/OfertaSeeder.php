<?php

namespace Database\Seeders;

use App\Models\Oferta;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfertaSeeder extends Seeder
{
    /**
     * Ofertas laborales de prueba (algunas aprobadas, otras pendientes).
     */
    public function run(): void
    {
        $empleador = User::where('email', 'empleador@emplealocal.test')->first();

        if (! $empleador) {
            return;
        }

        $ofertas = [
            [
                'titulo' => 'Albañil de obra',
                'descripcion' => 'Se requiere personal para trabajos de albañilería en obra civil en la ciudad de Coracora.',
                'requisitos' => 'Experiencia mínima de 1 año. Disponibilidad inmediata. Trabajo en equipo.',
                'lugar' => 'Coracora',
                'tipo_trabajo' => 'Tiempo completo',
                'salario' => 1200,
                'fecha_limite' => now()->addDays(20)->toDateString(),
                'estado' => Oferta::ESTADO_APROBADO,
            ],
            [
                'titulo' => 'Asistente administrativo',
                'descripcion' => 'Apoyo en la gestión documentaria, atención al cliente y control de archivos de la empresa.',
                'requisitos' => 'Estudios técnicos en administración. Manejo de Office. Buena redacción.',
                'lugar' => 'Puquio',
                'tipo_trabajo' => 'Medio tiempo',
                'salario' => 900,
                'fecha_limite' => now()->addDays(15)->toDateString(),
                'estado' => Oferta::ESTADO_APROBADO,
            ],
            [
                'titulo' => 'Operario de maquinaria pesada',
                'descripcion' => 'Operación de maquinaria pesada para movimiento de tierras en proyectos de infraestructura.',
                'requisitos' => 'Licencia de conducir vigente. Certificación de operación de maquinaria.',
                'lugar' => 'Parinacochas',
                'tipo_trabajo' => 'Temporal',
                'salario' => 2200,
                'fecha_limite' => now()->addDays(30)->toDateString(),
                'estado' => Oferta::ESTADO_PENDIENTE,
            ],
            [
                'titulo' => 'Vendedor de tienda',
                'descripcion' => 'Atención al público y ventas en tienda de materiales de construcción.',
                'requisitos' => 'Experiencia en ventas. Disponibilidad de horarios rotativos.',
                'lugar' => 'Coracora',
                'tipo_trabajo' => 'Por horas',
                'salario' => 800,
                'fecha_limite' => now()->addDays(10)->toDateString(),
                'estado' => Oferta::ESTADO_PENDIENTE,
            ],
        ];

        foreach ($ofertas as $oferta) {
            Oferta::updateOrCreate(
                ['titulo' => $oferta['titulo'], 'user_id' => $empleador->id],
                $oferta + ['user_id' => $empleador->id]
            );
        }
    }
}
