<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAccountsTest extends TestCase
{
    use RefreshDatabase;

    private function crearCuentasDePrueba(): void
    {
        User::factory()->admin()->create(['name' => 'Administrador General', 'email' => 'admin@emplealocal.test']);
        User::factory()->empleador()->create(['name' => 'Constructora Parinacochas', 'email' => 'empleador@emplealocal.test']);
        User::factory()->postulante()->create(['name' => 'María Huamán', 'email' => 'postulante@emplealocal.test']);
    }

    /** Por defecto la sección no se muestra (producción queda deshabilitada) */
    public function test_esta_deshabilitado_por_defecto(): void
    {
        $this->crearCuentasDePrueba();

        $this->assertFalse(config('demo_accounts.enabled'));

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Acceso rápido a cuentas de prueba');
    }

    /** Con la opción activa muestra nombre, correo y rol, pero nunca la contraseña */
    public function test_muestra_nombre_correo_y_rol_sin_contrasenas(): void
    {
        config(['demo_accounts.enabled' => true]);
        $this->crearCuentasDePrueba();
        User::factory()->postulante()->create(['name' => 'Persona Real', 'email' => 'persona@gmail.com']);

        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee('Acceso rápido a cuentas de prueba')
            ->assertSee('Administrador General')
            ->assertSee('Constructora Parinacochas')
            ->assertSee('María Huamán')
            ->assertSee('admin@emplealocal.test')
            ->assertSee('empleador@emplealocal.test')
            ->assertSee('postulante@emplealocal.test')
            ->assertSee('data-email="admin@emplealocal.test"', false)
            ->assertSee('Administrador')
            ->assertSee('Empleador')
            ->assertSee('Postulante')
            // Las cuentas reales nunca aparecen en el listado.
            ->assertDontSee('Persona Real')
            ->assertDontSee('persona@gmail.com')
            // Y nunca se filtra la contraseña ni su hash.
            ->assertDontSee('password123')
            ->assertDontSee('$2y$');

        // Las cuentas enviadas a la vista no incluyen la contraseña.
        $cuentas = $response->viewData('cuentasDemo');

        $this->assertCount(3, $cuentas);
        $this->assertSame(['admin', 'empleador', 'postulante'], $cuentas->pluck('role')->all());
        $this->assertArrayNotHasKey('password', $cuentas->first()->getAttributes());
    }

    /** Solo se listan las cuentas de prueba autorizadas que existen */
    public function test_solo_lista_las_cuentas_autorizadas_existentes(): void
    {
        config(['demo_accounts.enabled' => true]);
        User::factory()->admin()->create(['name' => 'Administrador General', 'email' => 'admin@emplealocal.test']);

        $response = $this->get(route('login'))->assertOk();

        $this->assertCount(1, $response->viewData('cuentasDemo'));
        $response->assertSee('admin@emplealocal.test')
            ->assertDontSee('empleador@emplealocal.test')
            ->assertDontSee('postulante@emplealocal.test');
    }

    /** Si no hay cuentas de prueba, la sección no se renderiza */
    public function test_no_se_muestra_si_no_existen_cuentas_de_prueba(): void
    {
        config(['demo_accounts.enabled' => true]);
        User::factory()->postulante()->create(['email' => 'persona@gmail.com']);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Acceso rápido a cuentas de prueba');
    }

    /** Aunque la opción esté activa, en producción nunca se muestra */
    public function test_nunca_se_muestra_en_produccion(): void
    {
        config(['demo_accounts.enabled' => true]);
        $this->app['env'] = 'production';
        $this->crearCuentasDePrueba();

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Acceso rápido a cuentas de prueba')
            ->assertDontSee('admin@emplealocal.test');
    }
}
