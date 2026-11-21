<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Models\Postulacion;
use App\Models\Publicidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    /** Contraseña con la que la factory crea a los usuarios. */
    private const PASSWORD_ACTUAL = 'password';

    /**
     * Payload del formulario de eliminación de cuenta.
     */
    private function payload(array $extra = []): array
    {
        return array_merge([
            'current_password' => self::PASSWORD_ACTUAL,
            'confirmar_eliminacion' => '1',
        ], $extra);
    }

    private function ofertaDe(User $empleador): Oferta
    {
        return Oferta::create([
            'user_id' => $empleador->id,
            'titulo' => 'Oferta de prueba',
            'descripcion' => 'Descripción',
            'requisitos' => 'Requisitos',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Temporal',
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'estado' => 'aprobado',
        ]);
    }

    /** RF03: sin contraseña actual no se elimina la cuenta */
    public function test_no_elimina_la_cuenta_sin_contrasena_actual(): void
    {
        $user = User::factory()->postulante()->create();

        $this->actingAs($user)
            ->from(route('perfil.show'))
            ->delete(route('perfil.destroy'))
            ->assertSessionHasErrors(['current_password', 'confirmar_eliminacion']);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertAuthenticatedAs($user);
    }

    /** RF03: sin la confirmación explícita tampoco se elimina */
    public function test_no_elimina_la_cuenta_sin_confirmacion_explicita(): void
    {
        $user = User::factory()->postulante()->create();

        $this->actingAs($user)
            ->from(route('perfil.show'))
            ->delete(route('perfil.destroy'), ['current_password' => self::PASSWORD_ACTUAL])
            ->assertSessionHasErrors('confirmar_eliminacion');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /** RF03: con una contraseña incorrecta no se elimina */
    public function test_no_elimina_la_cuenta_con_contrasena_incorrecta(): void
    {
        $user = User::factory()->postulante()->create();

        $this->actingAs($user)
            ->from(route('perfil.show'))
            ->delete(route('perfil.destroy'), $this->payload(['current_password' => 'equivocada']))
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /** RF03: el postulante elimina su cuenta junto con sus postulaciones */
    public function test_postulante_elimina_su_cuenta_y_sus_postulaciones(): void
    {
        $empleador = User::factory()->empleador()->create();
        $postulante = User::factory()->postulante()->create();
        $oferta = $this->ofertaDe($empleador);
        $postulacion = Postulacion::create([
            'oferta_id' => $oferta->id,
            'user_id' => $postulante->id,
            'estado' => 'pendiente',
        ]);

        $this->actingAs($postulante)
            ->delete(route('perfil.destroy'), $this->payload())
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $postulante->id]);
        $this->assertDatabaseMissing('postulaciones', ['id' => $postulacion->id]);

        // Los datos de la otra cuenta siguen intactos.
        $this->assertDatabaseHas('users', ['id' => $empleador->id]);
        $this->assertDatabaseHas('ofertas', ['id' => $oferta->id]);

        // La cuenta eliminada ya no puede seguir usando su sesión.
        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    /** RF03: el empleador elimina su cuenta con sus ofertas y publicidades */
    public function test_empleador_elimina_su_cuenta_con_sus_ofertas_y_publicidades(): void
    {
        $empleador = User::factory()->empleador()->create();
        $postulante = User::factory()->postulante()->create(['empresa' => null]);

        $oferta = $this->ofertaDe($empleador);
        $postulacion = Postulacion::create([
            'oferta_id' => $oferta->id,
            'user_id' => $postulante->id,
            'estado' => 'pendiente',
        ]);
        $publicidad = Publicidad::create([
            'user_id' => $empleador->id,
            'titulo' => 'Publicidad del empleador',
            'descripcion' => 'Descripción',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($empleador)
            ->delete(route('perfil.destroy'), $this->payload())
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('users', ['id' => $empleador->id]);
        $this->assertDatabaseMissing('ofertas', ['id' => $oferta->id]);
        $this->assertDatabaseMissing('publicidades', ['id' => $publicidad->id]);
        // Las postulaciones cuelgan de la oferta eliminada (cascade del esquema).
        $this->assertDatabaseMissing('postulaciones', ['id' => $postulacion->id]);

        // El postulante conserva su cuenta.
        $this->assertDatabaseHas('users', ['id' => $postulante->id]);
    }

    /** RF03/RF20: el administrador no puede eliminar su propia cuenta */
    public function test_administrador_no_puede_eliminar_su_cuenta(): void
    {
        $admin = User::factory()->admin()->create();
        $otroAdmin = User::factory()->admin()->create();
        $oportunidad = Oportunidad::create([
            'user_id' => $admin->id,
            'titulo' => 'Charla de orientación laboral',
            'tipo' => 'charla',
            'descripcion' => 'Descripción',
            'estado' => 'publicado',
        ]);

        $this->actingAs($admin)
            ->from(route('perfil.show'))
            ->delete(route('perfil.destroy'), $this->payload())
            ->assertRedirect(route('perfil.show'))
            ->assertSessionHas('error');

        // La cuenta y su contenido se mantienen intactos.
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('oportunidades', ['id' => $oportunidad->id, 'user_id' => $admin->id]);
        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('users', ['id' => $otroAdmin->id]);
    }

    /** RF03/RF20: el último administrador no puede eliminar su cuenta */
    public function test_el_ultimo_administrador_no_puede_eliminar_su_cuenta(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->postulante()->create();

        $this->actingAs($admin)
            ->from(route('perfil.show'))
            ->delete(route('perfil.destroy'), $this->payload())
            ->assertRedirect(route('perfil.show'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertAuthenticatedAs($admin);
        $this->actingAs($admin)->get('/admin/usuarios')->assertOk();
    }

    /** RF20: un invitado no puede eliminar cuentas */
    public function test_invitado_no_puede_eliminar_cuentas(): void
    {
        $user = User::factory()->postulante()->create();

        $this->delete(route('perfil.destroy'), $this->payload())->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /** RF03: una petición manipulada no elimina la cuenta de otra persona */
    public function test_no_puede_eliminar_la_cuenta_de_otro_usuario(): void
    {
        $atacante = User::factory()->postulante()->create();
        $victima = User::factory()->empleador()->create();

        $this->actingAs($atacante)
            ->delete(route('perfil.destroy'), $this->payload([
                'id' => $victima->id,
                'user_id' => $victima->id,
                'email' => $victima->email,
            ]))
            ->assertRedirect(route('home'));

        // Se elimina solo la cuenta autenticada; la víctima permanece.
        $this->assertDatabaseMissing('users', ['id' => $atacante->id]);
        $this->assertDatabaseHas('users', ['id' => $victima->id]);
        $this->assertTrue($victima->fresh()->isEmpleador());
    }
}
