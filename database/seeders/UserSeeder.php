<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Usuarios de prueba: un administrador, un empleador y un postulante.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@emplealocal.test'],
            [
                'name' => 'Administrador General',
                'password' => 'password123',
                'role' => User::ROLE_ADMIN,
                'nombres' => 'Administrador',
                'apellidos' => 'General',
                'telefono' => '999111222',
                'distrito' => 'Coracora',
            ]
        );

        User::updateOrCreate(
            ['email' => 'empleador@emplealocal.test'],
            [
                'name' => 'Constructora Parinacochas',
                'password' => 'password123',
                'role' => User::ROLE_EMPLEADOR,
                'nombres' => 'Julio',
                'apellidos' => 'Quispe Ramos',
                'telefono' => '988222333',
                'distrito' => 'Coracora',
                'empresa' => 'Constructora Parinacochas S.A.C.',
                'descripcion' => 'Empresa dedicada a la construcción civil en la provincia de Parinacochas.',
            ]
        );

        User::updateOrCreate(
            ['email' => 'postulante@emplealocal.test'],
            [
                'name' => 'María Huamán',
                'password' => 'password123',
                'role' => User::ROLE_POSTULANTE,
                'nombres' => 'María',
                'apellidos' => 'Huamán Campos',
                'dni' => '45678912',
                'telefono' => '977333444',
                'distrito' => 'Puquio',
                'profesion' => 'Técnica en Administración',
                'experiencia' => '3 años de experiencia en atención al cliente y gestión documentaria.',
                'descripcion' => 'Profesional responsable, con disponibilidad para trabajar en la zona.',
            ]
        );

        // Las cuentas de demostración usan correos @emplealocal.test que no
        // reciben mensajes reales: se marcan como verificadas para que el
        // docente pueda probar todos los roles sin depender del correo.
        User::whereIn('email', [
            'admin@emplealocal.test',
            'empleador@emplealocal.test',
            'postulante@emplealocal.test',
        ])->whereNull('email_verified_at')->get()->each(
            fn (User $user) => $user->forceFill(['email_verified_at' => now()])->save()
        );
    }
}
