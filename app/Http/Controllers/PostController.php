<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreOfertaRequest;
use App\Http\Requests\Api\StoreOportunidadRequest;
use App\Http\Resources\OfertaResource;
use App\Http\Resources\OportunidadResource;
use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\ImageUploader;
use App\Support\PublicacionPublica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Publicaciones de la portada: ofertas de empleo y oportunidades.
 *
 * No existe una tabla "posts": este controlador proyecta los modelos reales
 * (ofertas y oportunidades) con el mismo formato público que se transmite por
 * WebSocket, por lo que nunca expone correos, DNI ni postulaciones.
 *
 * Rutas:
 *   GET    /api/publicaciones
 *   GET    /api/publicaciones/{tipo}/{id}
 *   POST   /api/publicaciones
 *   PUT    /api/publicaciones/{tipo}/{id}
 *   DELETE /api/publicaciones/{tipo}/{id}
 */
class PostController extends Controller
{
    /**
     * Lista pública combinada de ofertas aprobadas y oportunidades publicadas.
     *
     * Filtros: tipo_publicacion (oferta|oportunidad), categoria, q,
     * page, per_page.
     */
    public function index(Request $request): JsonResponse
    {
        $tipoPublicacion = $request->input('tipo_publicacion');
        $categoria = trim((string) $request->input('categoria'));
        $termino = mb_strtolower(trim((string) $request->input('q')));

        $publicaciones = collect();

        if (in_array($tipoPublicacion, [null, '', 'oferta'], true)) {
            $publicaciones = $publicaciones->concat(
                Oferta::query()
                    ->aprobadas()
                    ->with('empleador')
                    ->latest()
                    ->get()
                    ->map(fn (Oferta $oferta) => PublicacionPublica::desdeOferta($oferta))
            );
        }

        if (in_array($tipoPublicacion, [null, '', 'oportunidad'], true)) {
            $publicaciones = $publicaciones->concat(
                Oportunidad::query()
                    ->publicadas()
                    ->latest()
                    ->get()
                    ->map(fn (Oportunidad $oportunidad) => PublicacionPublica::desdeOportunidad($oportunidad))
            );
        }

        if ($categoria !== '') {
            $publicaciones = $publicaciones->where('categoria', $categoria);
        }

        if ($termino !== '') {
            $publicaciones = $publicaciones->filter(function (array $publicacion) use ($termino) {
                $texto = mb_strtolower(implode(' ', array_filter([
                    $publicacion['titulo'] ?? '',
                    $publicacion['descripcion'] ?? '',
                    $publicacion['institucion'] ?? '',
                    $publicacion['lugar'] ?? '',
                ])));

                return str_contains($texto, $termino);
            });
        }

        $publicaciones = $publicaciones->sortByDesc('orden')->values();

        $porPagina = min(50, max(1, (int) $request->input('per_page', 10)));
        $pagina = Paginator::resolveCurrentPage();

        /** @var Collection<int, array<string, mixed>> $items */
        $items = $publicaciones->forPage($pagina, $porPagina)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $publicaciones->count(),
            $porPagina,
            $pagina,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        return ApiResponse::paginated(
            $paginator,
            $items,
            'Publicaciones obtenidas correctamente.'
        );
    }

    /**
     * Detalle público de una publicación.
     */
    public function show(Request $request, string $tipo, int $id): JsonResponse
    {
        if ($tipo === 'oferta') {
            $oferta = Oferta::with('empleador')->find($id);

            abort_if($oferta === null, 404, 'La publicación solicitada no existe.');

            $user = $request->user();
            $visible = $oferta->estado === Oferta::ESTADO_APROBADO
                || ($user && ($user->isAdmin() || $user->id === $oferta->user_id));

            abort_unless($visible, 404, 'La publicación solicitada no está disponible.');

            return ApiResponse::success(
                PublicacionPublica::desdeOferta($oferta),
                'Publicación obtenida correctamente.'
            );
        }

        $oportunidad = Oportunidad::find($id);

        abort_if($oportunidad === null, 404, 'La publicación solicitada no existe.');

        $visible = $oportunidad->estado === Oportunidad::ESTADO_PUBLICADO
            || $request->user()?->isAdmin() === true;

        abort_unless($visible, 404, 'La publicación solicitada no está disponible.');

        return ApiResponse::success(
            PublicacionPublica::desdeOportunidad($oportunidad),
            'Publicación obtenida correctamente.'
        );
    }

    /**
     * Crea una publicación. El tipo determina el modelo y el rol autorizado:
     * oferta -> empleador; oportunidad -> administrador.
     */
    public function store(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo_publicacion', 'oferta');
        $user = $request->user();

        if (! in_array($tipo, ['oferta', 'oportunidad'], true)) {
            return ApiResponse::error(
                'El tipo de publicación debe ser oferta u oportunidad.',
                422,
                ['tipo_publicacion' => ['Valor no admitido.']]
            );
        }

        if ($tipo === 'oferta') {
            abort_unless($user->isEmpleador(), 403, 'Sólo los empleadores pueden publicar ofertas.');
            $this->exigirCorreoVerificado($user);

            $data = $request->validate(StoreOfertaRequest::reglas());
            $data['user_id'] = $user->id;
            $data['estado'] = Oferta::ESTADO_PENDIENTE;

            $oferta = Oferta::create($data)->load('empleador');

            return ApiResponse::created(
                new OfertaResource($oferta),
                'Oferta registrada. Quedará visible cuando el administrador la apruebe.'
            );
        }

        abort_unless($user->isAdmin(), 403, 'Sólo el administrador puede publicar oportunidades.');

        $data = $request->validate(StoreOportunidadRequest::reglas());
        $data['user_id'] = $user->id;
        $data['estado'] = $data['estado'] ?? Oportunidad::ESTADO_PUBLICADO;

        if ($request->hasFile('imagen')) {
            $data['imagen'] = ImageUploader::guardar($request->file('imagen'), 'oportunidades');
        }

        $oportunidad = Oportunidad::create($data);

        return ApiResponse::created(
            new OportunidadResource($oportunidad),
            'Oportunidad creada correctamente.'
        );
    }

    /**
     * Actualiza una publicación existente respetando la propiedad del recurso.
     */
    public function update(Request $request, string $tipo, int $id): JsonResponse
    {
        $user = $request->user();

        if ($tipo === 'oferta') {
            $oferta = Oferta::find($id);

            abort_if($oferta === null, 404, 'La publicación solicitada no existe.');
            $this->autorizarOferta($user, $oferta);

            $data = $request->validate(StoreOfertaRequest::reglas());

            if (! $user->isAdmin()) {
                $data['estado'] = Oferta::ESTADO_PENDIENTE;
            }

            $oferta->update($data);

            return ApiResponse::success(
                new OfertaResource($oferta->fresh()->load('empleador')),
                'Oferta actualizada. Será revisada nuevamente por el administrador.'
            );
        }

        abort_unless($tipo === 'oportunidad', 404, 'La publicación solicitada no existe.');
        abort_unless($user->isAdmin(), 403, 'Sólo el administrador puede modificar oportunidades.');

        $oportunidad = Oportunidad::find($id);

        abort_if($oportunidad === null, 404, 'La publicación solicitada no existe.');

        $data = $request->validate(StoreOportunidadRequest::reglas());
        unset($data['imagen']);

        if ($request->hasFile('imagen')) {
            ImageUploader::eliminar($oportunidad->imagen);
            $data['imagen'] = ImageUploader::guardar($request->file('imagen'), 'oportunidades');
        }

        $oportunidad->update($data);

        return ApiResponse::success(
            new OportunidadResource($oportunidad->fresh()),
            'Oportunidad actualizada correctamente.'
        );
    }

    /**
     * Elimina una publicación propia (empleador) o cualquiera (administrador).
     */
    public function destroy(Request $request, string $tipo, int $id): JsonResponse
    {
        $user = $request->user();

        if ($tipo === 'oferta') {
            $oferta = Oferta::find($id);

            abort_if($oferta === null, 404, 'La publicación solicitada no existe.');
            $this->autorizarOferta($user, $oferta);

            $oferta->delete();

            return ApiResponse::success(null, 'Oferta eliminada correctamente.');
        }

        abort_unless($tipo === 'oportunidad', 404, 'La publicación solicitada no existe.');
        abort_unless($user->isAdmin(), 403, 'Sólo el administrador puede eliminar oportunidades.');

        $oportunidad = Oportunidad::find($id);

        abort_if($oportunidad === null, 404, 'La publicación solicitada no existe.');

        ImageUploader::eliminar($oportunidad->imagen);
        $oportunidad->delete();

        return ApiResponse::success(null, 'Oportunidad eliminada correctamente.');
    }

    /**
     * Sólo el empleador dueño de la oferta o un administrador.
     */
    private function autorizarOferta(User $user, Oferta $oferta): void
    {
        abort_unless(
            $user->isAdmin() || $oferta->user_id === $user->id,
            403,
            'No puedes gestionar publicaciones de otro empleador.'
        );
    }

    /**
     * Publicar exige correo verificado, igual que en la interfaz web.
     */
    private function exigirCorreoVerificado(User $user): void
    {
        abort_unless(
            $user->hasVerifiedEmail(),
            403,
            'Debes verificar tu correo electrónico antes de publicar ofertas.'
        );
    }
}
