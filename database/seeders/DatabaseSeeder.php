<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Ejecuta todos los seeders en orden.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            OfertaSeeder::class,
            OportunidadSeeder::class,
            PublicidadSeeder::class,
        ]);
    }
}
