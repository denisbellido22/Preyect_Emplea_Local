<?php

namespace Tests\Feature;

use App\Models\Oportunidad;
use App\Models\Publicidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private array $archivosCreados = [];

    protected function tearDown(): void
    {
        foreach ($this->archivosCreados as $archivo) {
            if (is_file($archivo)) {
                @unlink($archivo);
            }
        }
        parent::tearDown();
    }

    /** RF18: la imagen de oportunidad se guarda realmente */
    public function test_oportunidad_guarda_imagen_real(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.oportunidades.store'), [
            'titulo' => 'Oportunidad con imagen',
            'tipo' => 'curso',
            'descripcion' => 'Curso con imagen.',
            'estado' => 'publicado',
            'imagen' => $this->pngSubible('curso.png'),
        ]);

        $oportunidad = Oportunidad::where('titulo', 'Oportunidad con imagen')->first();
        $this->assertNotNull($oportunidad);
        $this->assertNotNull($oportunidad->imagen);
        $this->assertFileExists(public_path($oportunidad->imagen));
        $this->archivosCreados[] = public_path($oportunidad->imagen);
    }

    /** RF18: la imagen de publicidad se guarda realmente */
    public function test_publicidad_guarda_imagen_real(): void
    {
        $empleador = User::factory()->empleador()->create();

        $this->actingAs($empleador)->post(route('empleador.publicidades.store'), [
            'titulo' => 'Publicidad con imagen',
            'descripcion' => 'Anuncio con imagen.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(10)->toDateString(),
            'imagen' => $this->pngSubible('anuncio.png'),
        ]);

        $publicidad = Publicidad::where('titulo', 'Publicidad con imagen')->first();
        $this->assertNotNull($publicidad);
        $this->assertFileExists(public_path($publicidad->imagen));
        $this->archivosCreados[] = public_path($publicidad->imagen);
    }

    /** RF18: se rechazan archivos que no son imágenes */
    public function test_rechaza_archivo_no_imagen(): void
    {
        $empleador = User::factory()->empleador()->create();

        $response = $this->actingAs($empleador)->post(route('empleador.publicidades.store'), [
            'titulo' => 'Publicidad inválida',
            'descripcion' => 'Archivo no válido.',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(10)->toDateString(),
            'imagen' => \Illuminate\Http\UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('imagen');
        $this->assertDatabaseMissing('publicidades', ['titulo' => 'Publicidad inválida']);
    }

    /** RF18: la imagen se muestra posteriormente */
    public function test_imagen_se_muestra_en_el_listado(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.oportunidades.store'), [
            'titulo' => 'Oportunidad visible con imagen',
            'tipo' => 'beca',
            'descripcion' => 'Beca con imagen.',
            'estado' => 'publicado',
            'imagen' => $this->pngSubible('beca.png'),
        ]);

        $oportunidad = Oportunidad::where('titulo', 'Oportunidad visible con imagen')->first();
        $this->archivosCreados[] = public_path($oportunidad->imagen);

        $this->actingAs($admin)->get(route('admin.oportunidades.index'))
            ->assertOk()
            ->assertSee($oportunidad->imagen, false);
    }
}
