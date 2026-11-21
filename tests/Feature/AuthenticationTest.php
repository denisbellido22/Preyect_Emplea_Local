<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** RF01 */
    public function test_registro_de_empleador(): void
    {
        $response = $this->post('/register', [
            'name' => 'Empresa Demo',
            'email' => 'empleador.demo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'empleador',
            'telefono' => '999888777',
        ]);

        // El registro deja la cuenta pendiente de verificación (RF21).
        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('success');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'empleador.demo@example.com',
            'role' => 'empleador',
            'email_verified_at' => null,
        ]);
    }

    /** RF01 */
    public function test_registro_de_postulante(): void
    {
        $response = $this->post('/register', [
            'name' => 'Ana Torres',
            'email' => 'postulante.demo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'postulante',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $response->assertSessionHas('success');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'postulante.demo@example.com',
            'role' => 'postulante',
            'email_verified_at' => null,
        ]);
    }

    /** RF01: el administrador NO puede registrarse desde el formulario público */
    public function test_no_se_puede_registrar_como_administrador(): void
    {
        $response = $this->post('/register', [
            'name' => 'Intruso Admin',
            'email' => 'intruso@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
    }

    /** RF01: el rol es obligatorio */
    public function test_registro_requiere_rol(): void
    {
        $response = $this->post('/register', [
            'name' => 'Sin Rol',
            'email' => 'sinrol@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    /** RF02 */
    public function test_login_con_credenciales_reales(): void
    {
        $user = User::factory()->empleador()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /** RF02 */
    public function test_login_falla_con_credenciales_incorrectas(): void
    {
        User::factory()->create(['email' => 'login2@example.com']);

        $response = $this->from('/login')->post('/login', [
            'email' => 'login2@example.com',
            'password' => 'incorrecta',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** RF02 */
    public function test_logout_cierra_la_sesion(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
