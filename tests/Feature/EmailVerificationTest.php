<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Genera el enlace firmado que recibiría el usuario por correo.
     */
    private function urlVerificacion(User $usuario, ?string $hash = null, ?string $expira = null): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            $expira ? now()->addMinutes($expira) : now()->addMinutes(60),
            [
                'id' => $usuario->getKey(),
                'hash' => $hash ?? sha1($usuario->getEmailForVerification()),
            ]
        );
    }

    private function oferta(User $empleador): Oferta
    {
        return Oferta::create([
            'user_id' => $empleador->id,
            'titulo' => 'Asistente administrativo',
            'descripcion' => 'Apoyo administrativo.',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Medio tiempo',
            'estado' => Oferta::ESTADO_APROBADO,
        ]);
    }

    /** 1. Un usuario nuevo queda pendiente de verificación */
    public function test_un_usuario_nuevo_queda_pendiente_de_verificacion(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Ana Torres',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'postulante',
        ])->assertRedirect(route('verification.notice'));

        $usuario = User::where('email', 'ana@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($usuario);
        $this->assertNull($usuario->email_verified_at);
        $this->assertFalse($usuario->hasVerifiedEmail());
        $this->assertSame(User::ROLE_POSTULANTE, $usuario->role);
    }

    /** 2. El registro envía la notificación de verificación */
    public function test_el_registro_envia_la_notificacion_de_verificacion(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Constructora Demo',
            'email' => 'empresa@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'empleador',
        ]);

        $usuario = User::where('email', 'empresa@example.com')->firstOrFail();

        Notification::assertSentTo($usuario, VerifyEmailNotification::class, function (VerifyEmailNotification $notificacion) use ($usuario) {
            $correo = $notificacion->toMail($usuario);
            $url = $correo->viewData['actionUrl'];

            $this->assertSame('emails.verify-email', $correo->view['html']);
            $this->assertSame('emails.verify-email-text', $correo->view['text']);
            $this->assertStringContainsString('/email/verify/'.$usuario->id.'/', $url);
            $this->assertStringContainsString(sha1($usuario->email), $url);
            $this->assertStringContainsString('signature=', $url);

            return true;
        });
    }

    /** El correo usa la plantilla en español y el diseño de Emplea Local */
    public function test_el_correo_de_verificacion_usa_la_plantilla_en_espanol(): void
    {
        $usuario = User::factory()->unverified()->create(['nombres' => 'Ana', 'apellidos' => 'Quispe']);

        $correo = (new VerifyEmailNotification)->toMail($usuario);
        $html = (string) $correo->render();
        $texto = view($correo->view['text'], $correo->viewData)->render();

        $this->assertSame('Emplea Local · Verifica tu correo electrónico', $correo->subject);

        $this->assertStringContainsString('EMPLEA LOCAL', $html);
        $this->assertStringContainsString('Hola, <strong>Ana Quispe</strong>', $html);
        $this->assertStringContainsString('Verificar mi correo electrónico', $html);
        $this->assertStringContainsString('caduca en 60 minutos', $html);
        $this->assertStringContainsString('¿No creaste esta cuenta?', $html);
        $this->assertStringContainsString('/email/verify/', $html);

        $this->assertStringContainsString('Verifica tu correo electrónico', $texto);
        $this->assertStringContainsString('caduca en 60 minutos', $texto);
        $this->assertStringContainsString('/email/verify/', $texto);
    }

    /** 3. Un usuario puede verificar su correo con un enlace válido */
    public function test_un_usuario_puede_verificar_su_correo_con_un_enlace_valido(): void
    {
        $usuario = User::factory()->unverified()->postulante()->create();
        $url = $this->urlVerificacion($usuario);

        $this->actingAs($usuario)->get($url)
            ->assertRedirect(route('perfil.show'))
            ->assertSessionHas('success', '¡Listo! Tu correo electrónico fue verificado correctamente.');

        $usuario->refresh();

        $this->assertTrue($usuario->hasVerifiedEmail());
        $this->assertNotNull($usuario->email_verified_at);
    }

    /** 4. Un enlace inválido o caducado no verifica el correo */
    public function test_un_enlace_invalido_o_caducado_no_verifica_el_correo(): void
    {
        $usuario = User::factory()->unverified()->create();

        // Firma manipulada.
        $this->actingAs($usuario)->get($this->urlVerificacion($usuario).'0')
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('error');

        // Enlace caducado.
        $caducado = URL::temporarySignedRoute('verification.verify', now()->subMinute(), [
            'id' => $usuario->id,
            'hash' => sha1($usuario->email),
        ]);

        $this->actingAs($usuario)->get($caducado)
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('error');

        $this->assertFalse($usuario->fresh()->hasVerifiedEmail());
    }

    /** 5. Un enlace manipulado no verifica una cuenta ajena */
    public function test_un_enlace_manipulado_no_verifica_una_cuenta_ajena(): void
    {
        $victima = User::factory()->unverified()->create();
        $atacante = User::factory()->unverified()->create();

        // Firma válida, pero el hash no corresponde al correo del usuario.
        $url = $this->urlVerificacion($victima, sha1('otro@example.com'));

        $this->actingAs($victima)->get($url)->assertRedirect(route('verification.notice'));

        // Enlace de la víctima usado por otra cuenta autenticada.
        $this->actingAs($atacante)->get($this->urlVerificacion($victima))
            ->assertRedirect(route('verification.notice'));

        $this->assertFalse($victima->fresh()->hasVerifiedEmail());
        $this->assertFalse($atacante->fresh()->hasVerifiedEmail());
    }

    /** 6. Un enlace ya utilizado no permite una nueva verificación indebida */
    public function test_un_enlace_ya_utilizado_no_vuelve_a_verificar(): void
    {
        $usuario = User::factory()->unverified()->create();
        $url = $this->urlVerificacion($usuario);

        $this->actingAs($usuario)->get($url)->assertRedirect(route('perfil.show'));

        $primeraFecha = $usuario->fresh()->email_verified_at;

        $this->actingAs($usuario)->get($url)
            ->assertRedirect(route('perfil.show'))
            ->assertSessionHas('success', 'Tu correo electrónico ya estaba verificado. No necesitas verificarlo de nuevo.');

        $this->assertEquals($primeraFecha, $usuario->fresh()->email_verified_at);
    }

    /** 7. El botón de reenvío funciona y respeta los límites de frecuencia */
    public function test_el_reenvio_funciona_y_respeta_el_limite_de_frecuencia(): void
    {
        Notification::fake();

        $usuario = User::factory()->unverified()->create();

        for ($intento = 0; $intento < 3; $intento++) {
            $this->actingAs($usuario)->post(route('verification.send'))
                ->assertSessionHasNoErrors()
                ->assertSessionHas('success');
        }

        Notification::assertSentToTimes($usuario, VerifyEmailNotification::class, 3);

        $this->actingAs($usuario)->post(route('verification.send'))
            ->assertSessionHas('error');

        Notification::assertSentToTimes($usuario, VerifyEmailNotification::class, 3);
    }

    /** 8. Un usuario ya verificado no necesita verificar nuevamente */
    public function test_un_usuario_verificado_no_necesita_verificar_de_nuevo(): void
    {
        Notification::fake();

        $usuario = User::factory()->create();

        $this->actingAs($usuario)->post(route('verification.send'))
            ->assertRedirect(route('perfil.show'))
            ->assertSessionHas('success', 'Tu correo electrónico ya está verificado. No es necesario reenviar el mensaje.');

        Notification::assertNothingSent();

        $this->actingAs($usuario)->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Correo verificado')
            ->assertDontSee('Reenviar correo de verificación');
    }

    /** La pantalla de aviso exige sesión y muestra el correo de la cuenta */
    public function test_la_pantalla_de_aviso_muestra_el_correo_y_opciones(): void
    {
        $this->get(route('verification.notice'))->assertRedirect(route('login'));

        $usuario = User::factory()->unverified()->create(['email' => 'pendiente@example.com']);

        $this->actingAs($usuario)->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('pendiente@example.com')
            ->assertSee('Reenviar correo de verificación')
            ->assertSee('Volver al inicio de sesión')
            ->assertSee('name="_token"', false);
    }

    /** 9. Las operaciones protegidas rechazan a usuarios no verificados */
    public function test_las_operaciones_protegidas_rechazan_usuarios_no_verificados(): void
    {
        $empleador = User::factory()->unverified()->empleador()->create();

        $this->actingAs($empleador)->get(route('empleador.ofertas.create'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($empleador)->post(route('empleador.ofertas.store'), [])
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($empleador)->get(route('empleador.publicidades.create'))
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('ofertas', 0);

        $oferta = $this->oferta(User::factory()->empleador()->create());
        $postulante = User::factory()->unverified()->postulante()->create();

        $this->actingAs($postulante)->post(route('postulaciones.store', $oferta))
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('postulaciones', 0);
    }

    /** 10. Los usuarios verificados conservan los permisos de su rol */
    public function test_los_usuarios_verificados_conservan_sus_permisos(): void
    {
        $empleador = User::factory()->empleador()->create();
        $postulante = User::factory()->postulante()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($empleador)->get(route('empleador.ofertas.create'))->assertOk();
        $this->actingAs($postulante)->get(route('postulaciones.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.usuarios.index'))->assertOk();

        // Sin verificar, el administrador conserva el acceso al panel interno.
        $adminSinVerificar = User::factory()->unverified()->admin()->create();
        $this->actingAs($adminSinVerificar)->get(route('admin.usuarios.index'))->assertOk();
    }

    /** El perfil muestra el estado de verificación y permite reenviar */
    public function test_el_perfil_muestra_el_estado_de_verificacion(): void
    {
        $pendiente = User::factory()->unverified()->postulante()->create();

        $this->actingAs($pendiente)->get(route('perfil.show'))
            ->assertOk()
            ->assertSee('Verificación de correo electrónico')
            ->assertSee('Correo pendiente de verificación')
            ->assertSee('Reenviar correo de verificación')
            ->assertSee($pendiente->email);

        $verificado = User::factory()->create();

        $this->actingAs($verificado)->get(route('perfil.show'))
            ->assertOk()
            ->assertSee('Correo verificado')
            ->assertSee($verificado->email_verified_at->format('d/m/Y H:i'))
            ->assertDontSee('Reenviar correo de verificación');
    }

    /** Cambiar el correo del perfil obliga a verificarlo de nuevo */
    public function test_cambiar_el_correo_del_perfil_exige_una_nueva_verificacion(): void
    {
        Notification::fake();

        $usuario = User::factory()->empleador()->create(['email' => 'antiguo@example.com']);

        $this->actingAs($usuario)->put(route('perfil.update'), [
            'name' => 'Empresa Nueva',
            'nombres' => 'Julio',
            'apellidos' => 'Quispe',
            'email' => 'nuevo@example.com',
            'telefono' => '999888777',
            'distrito' => 'Coracora',
        ])->assertRedirect(route('perfil.show'))
            ->assertSessionHas('success');

        $usuario->refresh();

        $this->assertSame('nuevo@example.com', $usuario->email);
        $this->assertNull($usuario->email_verified_at);
        Notification::assertSentTo($usuario, VerifyEmailNotification::class);
    }

    /** El menú de usuario muestra el correo de la cuenta y enlaza a su buzón */
    public function test_el_menu_de_usuario_muestra_el_correo_y_enlaza_al_buzon(): void
    {
        $postulante = User::factory()->postulante()->create(['email' => 'ana.postulante@gmail.com']);

        $this->actingAs($postulante)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ana.postulante@gmail.com')
            ->assertSee('https://mail.google.com/mail/u/0/#inbox', false)
            ->assertSee('Abrir mi correo electrónico');

        // Para dominios sin buzón web conocido se abre el cliente de correo.
        $otro = User::factory()->create(['email' => 'contacto@empresa.pe']);

        $this->actingAs($otro)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('mailto:contacto@empresa.pe', false);
    }

    /** La recuperación de contraseña sigue funcionando con la verificación activa */
    public function test_la_recuperacion_de_contrasena_sigue_funcionando(): void
    {
        $usuario = User::factory()->unverified()->create();

        $this->post(route('password.email'), ['email' => $usuario->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $usuario->email]);
    }
}
