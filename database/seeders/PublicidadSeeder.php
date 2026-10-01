<?php

namespace Database\Seeders;

use App\Models\Publicidad;
use App\Models\User;
use Database\Seeders\Concerns\CreatesPlaceholderImages;
use Illuminate\Database\Seeder;

class PublicidadSeeder extends Seeder
{
    use CreatesPlaceholderImages;

    /**
     * Solicitudes de publicidad de prueba en distintos estados.
     */
    public function run(): void
    {
        $empleador = User::where('email', 'empleador@emplealocal.test')->first();

        if (! $empleador) {
            return;
        }

        $publicidades = [
            [
                'titulo' => 'Promoción de materiales de construcción',
                'descripcion' => 'Descuentos especiales en cemento y fierro durante todo el mes.',
                'fecha_inicio' => now()->subDays(3)->toDateString(),
                'fecha_fin' => now()->addDays(27)->toDateString(),
                'estado' => Publicidad::ESTADO_APROBADO,
                'color' => [34, 197, 94],
            ],
            [
                'titulo' => 'Servicios de alquiler de maquinaria',
                'descripcion' => 'Alquiler de maquinaria pesada con operario incluido.',
                'fecha_inicio' => now()->addDays(1)->toDateString(),
                'fecha_fin' => now()->addDays(31)->toDateString(),
                'estado' => Publicidad::ESTADO_PENDIENTE,
                'color' => [249, 115, 22],
            ],
        ];

        foreach ($publicidades as $i => $data) {
            $color = $data['color'];
            unset($data['color']);

            $data['imagen'] = $this->crearPng('publicidades', 'seed_publicidad_'.$i.'.png', 400, 240, $color);
            $data['user_id'] = $empleador->id;

            Publicidad::updateOrCreate(
                ['titulo' => $data['titulo'], 'user_id' => $empleador->id],
                $data
            );
        }
    }
}
