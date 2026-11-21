<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    /** El español es el idioma inicial de la aplicación. */
    public function test_el_idioma_inicial_es_espanol(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="es">', false)
            ->assertSee('Últimas oportunidades laborales y académicas')
            ->assertSee('Ingresar');
    }

    /** Cambiar a inglés traduce la interfaz y conserva la página actual. */
    public function test_cambia_a_ingles_y_conserva_la_pagina_actual(): void
    {
        $this->from('/ofertas')
            ->post('/idioma/en')
            ->assertRedirect('/ofertas')
            ->assertSessionHas('locale', 'en');

        $this->get('/ofertas')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Job openings')
            ->assertSee('Search by title, location or job type')
            ->assertDontSee('Ofertas laborales');
    }

    /** Se puede regresar de inglés a español sin dejar textos mezclados. */
    public function test_regresa_de_ingles_a_espanol(): void
    {
        $this->from('/ofertas')->post('/idioma/en');
        $this->get('/ofertas')->assertSee('Job openings');

        $this->from('/ofertas')->post('/idioma/es')->assertRedirect('/ofertas');

        $this->get('/ofertas')
            ->assertSee('<html lang="es">', false)
            ->assertSee('Ofertas laborales')
            ->assertDontSee('Job openings');
    }

    /** El portugués se suma a los idiomas disponibles. */
    public function test_cambia_a_portugues(): void
    {
        $this->from('/ofertas')
            ->post('/idioma/pt')
            ->assertRedirect('/ofertas')
            ->assertSessionHas('locale', 'pt');

        $this->get('/ofertas')
            ->assertOk()
            ->assertSee('<html lang="pt">', false)
            ->assertSee('Vagas de emprego')
            ->assertSee('Buscar por cargo, local ou tipo de trabalho')
            ->assertDontSee('Ofertas laborales');

        $this->get('/login')
            ->assertOk()
            ->assertSee('Lembrar de mim')
            ->assertSee('Entrar');
    }

    /** Sólo se aceptan los idiomas configurados. */
    public function test_rechaza_un_idioma_no_disponible(): void
    {
        $this->post('/idioma/fr')->assertNotFound();

        $this->get('/')->assertSee('<html lang="es">', false);
    }

    /** El idioma se conserva al navegar, iniciar y cerrar sesión. */
    public function test_el_idioma_se_conserva_entre_paginas_y_sesiones(): void
    {
        $user = User::factory()->postulante()->create(['password' => 'password123']);

        $this->from('/login')->post('/idioma/en')->assertRedirect('/login');

        // La pantalla de acceso ya está en inglés.
        $this->get('/login')->assertOk()->assertSee('Remember me')->assertSee('Sign in');

        // Se mantiene después de iniciar sesión.
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Applicant Panel');

        // Y también después de cerrar sesión.
        $this->post('/logout')->assertRedirect(route('home'));

        $this->get('/')->assertSee('<html lang="en">', false)->assertSee('All rights reserved.');
    }

    /** Los mensajes de validación y autenticación también se traducen. */
    public function test_los_errores_de_autenticacion_se_traducen(): void
    {
        User::factory()->create(['email' => 'login@example.com']);

        $this->from('/login')->post('/idioma/en');

        $this->from('/login')
            ->post('/login', [
                'email' => 'login@example.com',
                'password' => 'incorrecta',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
    }

    /** Las plantillas de correo también se traducen. */
    public function test_los_correos_se_traducen_al_ingles(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Ana', 'apellidos' => 'Quispe']);

        app()->setLocale('en');

        $correo = (new ResetPasswordNotification('token-de-prueba'))->toMail($usuario);
        $html = (string) $correo->render();

        $this->assertSame('Emplea Local · Password recovery', $correo->subject);
        $this->assertStringContainsString('Reset your password', $html);
        $this->assertStringContainsString('Reset my password', $html);
        $this->assertStringContainsString('Security notice', $html);
        $this->assertStringContainsString('Hello, <strong>Ana Quispe</strong>', $html);
    }

    /** Estados y tipos del sistema se traducen; el contenido del usuario no. */
    public function test_traduce_estados_y_conserva_el_contenido_de_los_usuarios(): void
    {
        $admin = User::factory()->admin()->create();
        $empleador = User::factory()->empleador()->create();

        $oferta = Oferta::create([
            'user_id' => $empleador->id,
            'titulo' => 'Albañil de obra',
            'descripcion' => 'Trabajo de construcción.',
            'requisitos' => 'Experiencia previa.',
            'lugar' => 'Coracora',
            'tipo_trabajo' => 'Tiempo completo',
            'fecha_limite' => now()->addWeek(),
            'estado' => Oferta::ESTADO_PENDIENTE,
        ]);

        // En español el estado y el tipo se muestran en español.
        $this->actingAs($admin)
            ->get('/admin/ofertas')
            ->assertSee('Pendiente')
            ->assertSee('Albañil de obra');

        $this->actingAs($admin)
            ->get('/ofertas/'.$oferta->id)
            ->assertSee('Tiempo completo');

        // En inglés se traduce la interfaz y los estados, no el contenido propio.
        $this->from('/admin/ofertas')->post('/idioma/en');

        $this->actingAs($admin)
            ->get('/admin/ofertas')
            ->assertSee('Job post moderation')
            ->assertSee('Pending')
            ->assertDontSee('Pendiente');

        $this->actingAs($admin)
            ->get('/ofertas/'.$oferta->id)
            ->assertSee('Full time')
            ->assertSee('Location:')
            ->assertSee('Albañil de obra')
            ->assertSee('Trabajo de construcción.');
    }
}
