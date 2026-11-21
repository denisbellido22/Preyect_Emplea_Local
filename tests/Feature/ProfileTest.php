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

    /** RF03: el administrador edita su perfil y conserva sus permisos */
    public function test_administrador_edita_su_perfil_y_conserva_sus_permisos(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put('/perfil', [
            'name' => 'Administrador Actualizado',
            'nombres' => 'Ana',
            'apellidos' => 'Torres',
            'email' => $admin->email,
            'telefono' => '999888777',
            'distrito' => 'Coracora',
            'empresa' => 'Municipalidad Provincial de Parinacochas',
        ]);

        $response->assertRedirect(route('perfil.show'));
        $admin->refresh();

        $this->assertSame('Administrador Actualizado', $admin->name);
        $this->assertTrue($admin->isAdmin());
        // Sigue accediendo al panel administrativo con la misma sesión.
        $this->actingAs($admin)->get('/admin/usuarios')->assertOk();
    }

    /** RF03: correo y DNI duplicados son rechazados */
    public function test_rechaza_correo_y_dni_duplicados(): void
    {
        $otro = User::factory()->postulante()->create(['email' => 'ocupado@example.com', 'dni' => '11111111']);
        $user = User::factory()->postulante()->create(['dni' => '22222222']);

        $this->actingAs($user)
            ->from(route('perfil.edit'))
            ->put('/perfil', [
                'name' => $user->name,
                'nombres' => 'María',
                'apellidos' => 'Huamán',
                'email' => 'ocupado@example.com',
                'dni' => '11111111',
                'profesion' => 'Contadora',
            ])
            ->assertSessionHasErrors(['email', 'dni']);

        $this->assertSame('ocupado@example.com', $otro->fresh()->email);
        $this->assertSame('11111111', $otro->fresh()->dni);
        $this->assertNotSame('ocupado@example.com', $user->fresh()->email);
    }

    /** RF03: el formulario no permite cambiar el rol ni el identificador */
    public function test_no_puede_cambiar_su_rol_ni_su_identificador(): void
    {
        $user = User::factory()->postulante()->create();
        $idOriginal = $user->id;

        $this->actingAs($user)->put('/perfil', [
            'id' => 9999,
            'role' => 'admin',
            'name' => 'Nombre Actualizado',
            'nombres' => 'María',
            'apellidos' => 'Huamán',
            'email' => $user->email,
            'dni' => '12345678',
            'profesion' => 'Contadora',
        ])->assertRedirect(route('perfil.show'));

        $user->refresh();

        $this->assertSame($idOriginal, $user->id);
        $this->assertSame('postulante', $user->role);
        $this->assertSame('Nombre Actualizado', $user->name);
        $this->assertDatabaseMissing('users', ['id' => 9999]);
        // Sin permisos de administrador tras la edición.
        $this->actingAs($user)->get('/admin/usuarios')->assertForbidden();
    }

    /** RF03/RF20: nadie edita el perfil de otra cuenta manipulando la petición */
    public function test_no_puede_editar_el_perfil_de_otro_usuario(): void
    {
        $atacante = User::factory()->postulante()->create();
        $victima = User::factory()->empleador()->create([
            'name' => 'Empresa Víctima',
            'email' => 'victima@example.com',
            'empresa' => 'Víctima S.A.C.',
        ]);

        $this->actingAs($atacante)->put('/perfil', [
            'id' => $victima->id,
            'user_id' => $victima->id,
            'role' => 'admin',
            'name' => 'Cuenta Modificada',
            'nombres' => 'Atacante',
            'apellidos' => 'Malicioso',
            'email' => $atacante->email,
            'dni' => '99999999',
            'profesion' => 'Contador',
        ])->assertRedirect(route('perfil.show'));

        $victima->refresh();

        // La otra cuenta queda intacta.
        $this->assertSame('Empresa Víctima', $victima->name);
        $this->assertSame('victima@example.com', $victima->email);
        $this->assertSame('Víctima S.A.C.', $victima->empresa);
        $this->assertTrue($victima->isEmpleador());

        // El cambio se aplicó solo a la cuenta autenticada.
        $this->assertSame('Cuenta Modificada', $atacante->fresh()->name);
        $this->assertSame('postulante', $atacante->fresh()->role);
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
