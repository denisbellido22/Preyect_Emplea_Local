<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ApiAuthenticationTest extends ApiTestCase
{
    public function test_login_devuelve_token_y_perfil_sin_datos_sensibles(): void
    {
        $user = User::factory()->empleador()->create([
            'email' => 'empleador.api@emplealocal.test',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'empleador.api@emplealocal.test',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.role', User::ROLE_EMPLEADOR)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'token_type', 'user' => ['id', 'name', 'email', 'role']],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertSame('Bearer', $response->json('data.token_type'));
        $this->assertArrayNotHasKey('password', $response->json('data.user'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data.user'));
    }

    public function test_login_con_credenciales_invalidas_responde_401(): void
    {
        User::factory()->create(['email' => 'real@emplealocal.test']);

        $this->postJson('/api/auth/login', [
            'email' => 'real@emplealocal.test',
            'password' => 'incorrecta',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Las credenciales no coinciden con nuestros registros.');
    }

    public function test_el_token_permite_acceder_a_rutas_protegidas_y_logout_lo_revoca(): void
    {
        $user = User::factory()->postulante()->create([
            'email' => 'postulante.api@emplealocal.test',
            'password' => 'secret123',
        ]);

        $token = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->json('data.token');

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        // El token revocado ya no permite autenticarse. En una petición real
        // cada solicitud crea un guard nuevo; en pruebas hay que descartar el
        // guard cacheado por la aplicación para simularlo.
        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_ruta_protegida_sin_token_responde_401_json(): void
    {
        $this->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message']);
    }

    public function test_registro_crea_postulante_y_devuelve_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ana Torres',
            'email' => 'ana.torres@emplealocal.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'postulante',
            'telefono' => '999888777',
            'distrito' => 'Puquio',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', User::ROLE_POSTULANTE)
            ->assertJsonPath('data.user.email_verified', false);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseHas('users', [
            'email' => 'ana.torres@emplealocal.test',
            'role' => 'postulante',
        ]);
    }

    public function test_registro_no_permite_crear_administradores(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Intruso',
            'email' => 'intruso@emplealocal.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['role']]);
    }

    public function test_registro_duplicado_responde_422(): void
    {
        User::factory()->create(['email' => 'repetido@emplealocal.test']);

        $this->postJson('/api/auth/register', [
            'name' => 'Repetido',
            'email' => 'repetido@emplealocal.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'postulante',
        ])->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_me_devuelve_el_perfil_autenticado(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.role', User::ROLE_ADMIN)
            ->assertJsonMissingPath('data.password');
    }
}
