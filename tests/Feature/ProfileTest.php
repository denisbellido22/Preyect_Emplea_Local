<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** RF03 */
    public function test_postulante_puede_ver_y_editar_su_perfil(): void
    {
        $user = User::factory()->postulante()->create();

        $this->actingAs($user)->get('/perfil')->assertOk();

        $response = $this->actingAs($user)->put('/perfil', [
            'name' => 'María Actualizada',
            'nombres' => 'María',
            'apellidos' => 'Huamán',
            'email' => $user->email,
            'dni' => '12345678',
            'telefono' => '999777666',
            'distrito' => 'Coracora',
            'profesion' => 'Contadora',
            'experiencia' => '5 años de experiencia.',
            'descripcion' => 'Perfil actualizado.',
        ]);

        $response->assertRedirect(route('perfil.show'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nombres' => 'María',
            'dni' => '12345678',
            'profesion' => 'Contadora',
        ]);
    }

    /** RF03: el DNI es obligatorio para el postulante */
    public function test_postulante_requiere_dni(): void
    {
        $user = User::factory()->postulante()->create();

        $response = $this->actingAs($user)->put('/perfil', [
            'name' => 'María',
            'nombres' => 'María',
            'apellidos' => 'Huamán',
            'email' => $user->email,
            'profesion' => 'Contadora',
        ]);

        $response->assertSessionHasErrors('dni');
    }

    /** RF03 */
    public function test_empleador_puede_editar_su_perfil(): void
    {
        $user = User::factory()->empleador()->create();

        $response = $this->actingAs($user)->put('/perfil', [
            'name' => 'Constructora Demo',
            'nombres' => 'Carlos',
            'apellidos' => 'Pérez',
            'email' => $user->email,
            'empresa' => 'Constructora Demo S.A.C.',
            'telefono' => '988111222',
            'distrito' => 'Puquio',
        ]);

        $response->assertRedirect(route('perfil.show'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'empresa' => 'Constructora Demo S.A.C.',
        ]);
    }
}
