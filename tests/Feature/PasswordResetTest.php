<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD_ANTERIOR = 'password';

    private const PASSWORD_NUEVA = 'NuevaClave2026';

    /**
     * Payload del formulario de nueva contraseña.
     */
    private function payload(User $usuario, string $token, ?string $password = null, ?string $confirmacion = null): array
    {
        $password ??= self::PASSWORD_NUEVA;

        return [
            'token' => $token,
            'email' => $usuario->email,
            'password' => $password,
            'password_confirmation' => $confirmacion ?? $password,
        ];
    }

    /** El login ofrece el enlace de recuperación */
    public function test_el_login_muestra_el_enlace_de_recuperacion(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('¿Olvidaste tu contraseña?')
            ->assertSee(route('password.request'), false);
    }

    /** El formulario de solicitud pide el correo registrado */
    public function test_muestra_el_formulario_para_pedir_el_correo(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Recuperar contraseña')
            ->assertSee('name="email"', false)
            ->assertSee('name="_token"', false);
    }

    /** Se envía el enlace por correo a cualquier rol registrado */
    public function test_envia_el_enlace_a_los_tres_roles(): void
    {
        Notification::fake();

        foreach ([User::ROLE_ADMIN, User::ROLE_EMPLEADOR, User::ROLE_POSTULANTE] as $rol) {
            $usuario = User::factory()->create(['role' => $rol]);

            $this->post(route('password.email'), ['email' => $usuario->email])
                ->assertSessionHasNoErrors()
                ->assertSessionHas('success', 'Te enviamos un enlace para restablecer tu contraseña al correo registrado.');

            Notification::assertSentTo($usuario, ResetPasswordNotification::class);
            // El token temporal queda guardado para esa cuenta.
            $this->assertDatabaseHas('password_reset_tokens', ['email' => $usuario->email]);
        }
    }

    /** El correo contiene el enlace con token seguro y correo de la cuenta */
    public function test_el_correo_contiene_el_enlace_con_token(): void
    {
        Notification::fake();
        $usuario = User::factory()->postulante()->create();

        $this->post(route('password.email'), ['email' => $usuario->email]);

        Notification::assertSentTo($usuario, ResetPasswordNotification::class, function (ResetPasswordNotification $notificacion) use ($usuario) {
            $correo = $notificacion->toMail($usuario);
            $url = $correo->viewData['actionUrl'];

            $this->assertStringContainsString('/reset-password/', $url);
            $this->assertStringContainsString($notificacion->token, $url);
            $this->assertStringContainsString(urlencode($usuario->email), $url);
            $this->assertSame('emails.reset-password', $correo->view['html']);
            $this->assertSame('emails.reset-password-text', $correo->view['text']);

            return true;
        });
    }

    /** El correo usa la plantilla en español con botón, caducidad y aviso de seguridad */
    public function test_el_correo_usa_la_plantilla_en_espanol(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Ana', 'apellidos' => 'Quispe']);

        $correo = (new ResetPasswordNotification('token-de-prueba'))->toMail($usuario);
        $html = (string) $correo->render();
        $texto = view($correo->view['text'], $correo->viewData)->render();

        $this->assertSame('Emplea Local · Recuperación de contraseña', $correo->subject);

        // HTML: marca, saludo, botón, caducidad y aviso de seguridad.
        $this->assertStringContainsString('EMPLEA LOCAL', $html);
        $this->assertStringContainsString('Hola, <strong>Ana Quispe</strong>', $html);
        $this->assertStringContainsString('Restablecer mi contraseña', $html);
        $this->assertStringContainsString('caduca en 60 minutos', $html);
        $this->assertStringContainsString('Aviso de seguridad', $html);
        $this->assertStringContainsString('Si no solicitaste este cambio', $html);
        $this->assertStringContainsString('token-de-prueba', $html);

        // Versión de texto plano equivalente.
        $this->assertStringContainsString('Restablece tu contraseña', $texto);
        $this->assertStringContainsString('caduca en 60 minutos', $texto);
        $this->assertStringContainsString('token-de-prueba', $texto);
    }

    /** Un fallo de SMTP se muestra como error genérico y no filtra credenciales */
    public function test_un_fallo_de_smtp_no_expone_credenciales(): void
    {
        Log::spy();

        $usuario = User::factory()->create(['email' => 'titular@example.com']);

        config([
            'mail.mailers.smtp.username' => 'tucorreo@gmail.com',
            'mail.mailers.smtp.password' => 'supersecreto',
        ]);

        $mailer = \Mockery::mock(MailFactory::class);
        $mailer->shouldReceive('mailer')->andReturnSelf();
        $mailer->shouldReceive('send')->andThrow(new TransportException(
            'Failed to authenticate on SMTP server with username "tucorreo@gmail.com" and password "supersecreto"; contact soporte@example.org'
        ));
        $this->app->instance('mail.manager', $mailer);

        $respuesta = $this->post(route('password.email'), ['email' => $usuario->email]);

        $respuesta->assertSessionHasInput('email', $usuario->email);
        $respuesta->assertSessionHas('error');

        $mensaje = (string) session('error');
        $this->assertStringContainsString('No pudimos enviar el correo', $mensaje);
        $this->assertStringNotContainsString('tucorreo@gmail.com', $mensaje);
        $this->assertStringNotContainsString('supersecreto', $mensaje);

        Log::shouldHaveReceived('error')->withArgs(function (string $texto, array $contexto): bool {
            $registro = json_encode($contexto, JSON_UNESCAPED_UNICODE);

            return str_contains($texto, 'recuperación de contraseña')
                && str_contains($registro, 'TransportException')
                && str_contains($registro, '[oculto]')
                && str_contains($registro, '[correo oculto]')
                && ! str_contains($registro, 'tucorreo@gmail.com')
                && ! str_contains($registro, 'supersecreto')
                && ! str_contains($registro, 'soporte@example.org');
        });
    }

    /** Un correo no registrado no recibe ningún enlace */
    public function test_rechaza_un_correo_no_registrado(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'nadie@example.com'])
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    /** El formulario de nueva contraseña se abre desde el enlace */
    public function test_abre_el_formulario_de_nueva_contrasena(): void
    {
        $usuario = User::factory()->create();
        $token = Password::createToken($usuario);

        $this->get(route('password.reset', ['token' => $token, 'email' => $usuario->email]))
            ->assertOk()
            ->assertSee('Nueva contraseña')
            ->assertSee('name="token"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee($usuario->email);
    }

    /** Flujo completo: restablece la contraseña y permite iniciar sesión con la nueva */
    public function test_restablece_la_contrasena_y_permite_iniciar_sesion(): void
    {
        $usuario = User::factory()->postulante()->create();
        $token = Password::createToken($usuario);

        $this->post(route('password.store'), $this->payload($usuario, $token))
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Tu contraseña fue restablecida correctamente. Ya puedes iniciar sesión con la nueva.');

        $usuario->refresh();

        $this->assertTrue(Hash::check(self::PASSWORD_NUEVA, $usuario->password));
        $this->assertNotSame(self::PASSWORD_NUEVA, $usuario->password);
        // El token se elimina: el enlace no se puede volver a usar.
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $usuario->email]);

        // La contraseña anterior deja de funcionar.
        $this->post(route('login'), ['email' => $usuario->email, 'password' => self::PASSWORD_ANTERIOR])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // La nueva sí permite iniciar sesión.
        $this->post(route('login'), ['email' => $usuario->email, 'password' => self::PASSWORD_NUEVA])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);
    }

    /** Un enlace ya utilizado no se puede reutilizar */
    public function test_no_se_puede_reutilizar_el_enlace(): void
    {
        $usuario = User::factory()->create();
        $token = Password::createToken($usuario);

        $this->post(route('password.store'), $this->payload($usuario, $token))->assertRedirect(route('login'));

        $this->post(route('password.store'), $this->payload($usuario, $token, 'OtraClave2026'))
            ->assertSessionHasErrors('email');

        // La contraseña sigue siendo la del primer uso.
        $this->assertTrue(Hash::check(self::PASSWORD_NUEVA, $usuario->fresh()->password));
        $this->assertFalse(Hash::check('OtraClave2026', $usuario->fresh()->password));
    }

    /** Un token inválido o el correo de otra cuenta son rechazados */
    public function test_rechaza_token_invalido_o_correo_de_otra_cuenta(): void
    {
        $usuario = User::factory()->create();
        $otro = User::factory()->create();
        $token = Password::createToken($usuario);

        $this->post(route('password.store'), $this->payload($usuario, 'token-invalido'))
            ->assertSessionHasErrors('email');

        $this->post(route('password.store'), $this->payload($otro, $token))
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check(self::PASSWORD_ANTERIOR, $usuario->fresh()->password));
        $this->assertTrue(Hash::check(self::PASSWORD_ANTERIOR, $otro->fresh()->password));
    }

    /** La confirmación y la longitud mínima se validan en el servidor */
    public function test_valida_confirmacion_y_longitud_minima(): void
    {
        $usuario = User::factory()->create();
        $token = Password::createToken($usuario);

        $this->post(route('password.store'), $this->payload($usuario, $token, self::PASSWORD_NUEVA, 'OtraClave2026'))
            ->assertSessionHasErrors('password');

        $this->post(route('password.store'), $this->payload($usuario, $token, 'Clave12'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check(self::PASSWORD_ANTERIOR, $usuario->fresh()->password));
    }

    /** La expiración del enlace está configurada */
    public function test_el_enlace_caduca_a_los_sesenta_minutos(): void
    {
        $this->assertSame(60, config('auth.passwords.users.expire'));
    }

    /** Las rutas de recuperación no requieren sesión iniciada */
    public function test_las_rutas_de_recuperacion_son_publicas(): void
    {
        $usuario = User::factory()->create();
        $token = Password::createToken($usuario);

        $this->assertGuest();
        $this->get(route('password.request'))->assertOk();
        $this->get(route('password.reset', ['token' => $token, 'email' => $usuario->email]))->assertOk();
    }

    /** Un usuario autenticado es redirigido a su panel (igual que en el login) */
    public function test_un_usuario_autenticado_no_usa_el_formulario_de_recuperacion(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->get(route('password.request'))->assertRedirect(route('dashboard'));
    }
}
