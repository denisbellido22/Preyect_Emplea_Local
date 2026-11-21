<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    /** Contraseña con la que la factory crea a los usuarios. */
    private const PASSWORD_ACTUAL = 'password';

    private const PASSWORD_NUEVA = 'NuevaClave2026';

    /**
     * Payload del formulario de cambio de contraseña.
     */
    private function payload(string $actual, string $nueva, ?string $confirmacion = null): array
    {
        return [
            'current_password' => $actual,
            'password' => $nueva,
            'password_confirmation' => $confirmacion ?? $nueva,
        ];
    }

    /** RF03: el formulario está disponible para admin, empleador y postulante */
    public function test_el_formulario_esta_disponible_para_todos_los_roles(): void
    {
        $usuarios = [
            User::factory()->admin()->create(),
            User::factory()->empleador()->create(),
            User::factory()->postulante()->create(),
        ];

        foreach ($usuarios as $usuario) {
            $this->actingAs($usuario)
                ->get(route('perfil.password.edit'))
                ->assertOk()
                ->assertSee('Cambiar contraseña')
                ->assertSee('name="current_password"', false)
                ->assertSee('name="password"', false)
                ->assertSee('name="password_confirmation"', false);
        }
    }

    /** RF03: cada rol cambia su contraseña e inicia sesión con la nueva */
    public function test_cada_rol_cambia_su_contrasena_e_inicia_sesion_con_la_nueva(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_EMPLEADOR, User::ROLE_POSTULANTE] as $rol) {
            $usuario = User::factory()->create(['role' => $rol]);

            $this->actingAs($usuario)
                ->put(route('perfil.password.update'), $this->payload(self::PASSWORD_ACTUAL, self::PASSWORD_NUEVA))
                ->assertRedirect(route('perfil.show'))
                ->assertSessionHas('success');

            $usuario->refresh();

            // Se guarda con hash y conserva el rol original.
            $this->assertSame($rol, $usuario->role);
            $this->assertNotSame(self::PASSWORD_NUEVA, $usuario->password);
            $this->assertTrue(Hash::check(self::PASSWORD_NUEVA, $usuario->password));

            // La contraseña anterior deja de ser válida para iniciar sesión.
            $this->post(route('logout'));
            $this->post(route('login'), ['email' => $usuario->email, 'password' => self::PASSWORD_ACTUAL])
                ->assertSessionHasErrors('email');
            $this->assertGuest();

            // La nueva contraseña sí permite iniciar sesión.
            $this->post(route('login'), ['email' => $usuario->email, 'password' => self::PASSWORD_NUEVA])
                ->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($usuario);

            $this->post(route('logout'));
        }
    }

    /** RF03: una contraseña actual incorrecta es rechazada */
    public function test_contrasena_actual_incorrecta_es_rechazada(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->from(route('perfil.password.edit'))
            ->put(route('perfil.password.update'), $this->payload('clave-equivocada', self::PASSWORD_NUEVA))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check(self::PASSWORD_ACTUAL, $usuario->fresh()->password));
    }

    /** RF03: la confirmación debe coincidir con la nueva contraseña */
    public function test_confirmacion_diferente_es_rechazada(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->from(route('perfil.password.edit'))
            ->put(route('perfil.password.update'), $this->payload(self::PASSWORD_ACTUAL, self::PASSWORD_NUEVA, 'OtraClave2026'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check(self::PASSWORD_ACTUAL, $usuario->fresh()->password));
    }

    /** RF03: la nueva contraseña exige un mínimo de 8 caracteres */
    public function test_contrasena_corta_es_rechazada(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->from(route('perfil.password.edit'))
            ->put(route('perfil.password.update'), $this->payload(self::PASSWORD_ACTUAL, 'Clave12'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check(self::PASSWORD_ACTUAL, $usuario->fresh()->password));
    }

    /** RF03: el cambio no altera el rol ni los datos de la cuenta */
    public function test_el_cambio_no_modifica_el_rol_ni_los_datos_de_la_cuenta(): void
    {
        $usuario = User::factory()->postulante()->create([
            'nombres' => 'María',
            'apellidos' => 'Huamán',
            'dni' => '12345678',
            'telefono' => '999777666',
            'distrito' => 'Coracora',
            'profesion' => 'Contadora',
            'experiencia' => '5 años de experiencia.',
        ]);

        $campos = ['name', 'email', 'role', 'nombres', 'apellidos', 'dni', 'telefono', 'distrito', 'profesion', 'experiencia', 'empresa'];
        $antes = $usuario->only($campos);

        $this->actingAs($usuario)
            ->put(route('perfil.password.update'), $this->payload(self::PASSWORD_ACTUAL, self::PASSWORD_NUEVA))
            ->assertRedirect(route('perfil.show'));

        $usuario->refresh();

        $this->assertSame($antes, $usuario->only($campos));
        $this->assertTrue($usuario->isPostulante());
    }

    /** RF20: un invitado no accede al formulario ni ejecuta el cambio */
    public function test_invitado_no_accede_al_formulario_ni_puede_cambiar_la_contrasena(): void
    {
        $usuario = User::factory()->create();

        $this->get(route('perfil.password.edit'))->assertRedirect(route('login'));

        $this->put(route('perfil.password.update'), $this->payload(self::PASSWORD_ACTUAL, self::PASSWORD_NUEVA))
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check(self::PASSWORD_ACTUAL, $usuario->fresh()->password));
    }

    /** RF03: nadie puede cambiar la contraseña de otra cuenta manipulando la petición */
    public function test_ningun_usuario_puede_cambiar_la_contrasena_de_otra_cuenta(): void
    {
        $atacante = User::factory()->postulante()->create();
        $victima = User::factory()->empleador()->create();

        $this->actingAs($atacante)
            ->put(route('perfil.password.update'), [
                'id' => $victima->id,
                'user_id' => $victima->id,
                'email' => $victima->email,
                ...$this->payload(self::PASSWORD_ACTUAL, self::PASSWORD_NUEVA),
            ])
            ->assertRedirect(route('perfil.show'));

        // Sólo cambia la contraseña del usuario autenticado.
        $this->assertTrue(Hash::check(self::PASSWORD_NUEVA, $atacante->fresh()->password));
        $this->assertTrue(Hash::check(self::PASSWORD_ACTUAL, $victima->fresh()->password));
        $this->assertTrue($victima->fresh()->isEmpleador());
    }
}
