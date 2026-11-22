<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOportunidadRequest;
use App\Http\Requests\Api\UpdateOportunidadRequest;
use App\Http\Resources\OportunidadResource;
use App\Models\Oportunidad;
use App\Support\ApiResponse;
use App\Support\ImageUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API de oportunidades: becas, convocatorias, exámenes, prácticas, cursos,
 * charlas y eventos (RF13/RF15). La consulta es pública; la gestión
 * corresponde al rol Administrador, igual que en la interfaz web.
 */
class OportunidadController extends Controller
{
    /**
     * GET /api/oportunidades
     *
     * Filtros: q, tipo (o categoria), recientes, orden, page, per_page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Oportunidad::query()->publicadas();

        $this->aplicarFiltros($query, $request);

        $this->aplicarOrden($query, $request);

        $oportunidades = $query->paginate($this->porPagina($request))->withQueryString();

        return ApiResponse::paginated(
            $oportunidades,
            OportunidadResource::collection($oportunidades),
            'Oportunidades obtenidas correctamente.'
        );
    }

    /**
     * GET /api/oportunidades/recientes
     */
    public function recientes(Request $request): JsonResponse
    {
        $limite = min(50, max(1, (int) $request->input('limite', 10)));

        $oportunidades = Oportunidad::query()
            ->publicadas()
            ->latest()
            ->take($limite)
            ->get();

        return ApiResponse::success(
            OportunidadResource::collection($oportunidades),
            'Oportunidades recientes obtenidas correctamente.'
        );
    }

    /**
     * GET /api/oportunidades/{oportunidad}
     *
     * Los borradores sólo son visibles para el administrador.
     */
    public function show(Request $request, Oportunidad $oportunidad): JsonResponse
    {
        $esVisible = $oportunidad->estado === Oportunidad::ESTADO_PUBLICADO
            || $request->user()?->isAdmin() === true;

        abort_unless($esVisible, 404, 'La oportunidad solicitada no existe o no está publicada.');

        return ApiResponse::success(
            new OportunidadResource($oportunidad),
            'Oportunidad obtenida correctamente.'
        );
    }

    /**
     * POST /api/oportunidades (sólo administrador).
     */
    public function store(StoreOportunidadRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
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
     * PUT/PATCH /api/oportunidades/{oportunidad} (sólo administrador).
     */
    public function update(UpdateOportunidadRequest $request, Oportunidad $oportunidad): JsonResponse
    {
        $data = $request->validated();
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
     * DELETE /api/oportunidades/{oportunidad} (sólo administrador).
     */
    public function destroy(Oportunidad $oportunidad): JsonResponse
    {
        ImageUploader::eliminar($oportunidad->imagen);
        $oportunidad->delete();

        return ApiResponse::success(null, 'Oportunidad eliminada correctamente.');
    }

    /**
     * Búsqueda por texto y filtro por tipo de oportunidad.
     */
    private function aplicarFiltros(Builder $query, Request $request): void
    {
        $termino = trim((string) $request->input('q'));

        if ($termino !== '') {
            $query->where(function (Builder $sub) use ($termino) {
                $sub->where('titulo', 'like', '%'.$termino.'%')
                    ->orWhere('institucion', 'like', '%'.$termino.'%')
                    ->orWhere('lugar', 'like', '%'.$termino.'%');
            });
        }

        $tipo = trim((string) $request->input('tipo') ?: (string) $request->input('categoria'));

        if ($tipo !== '' && in_array($tipo, Oportunidad::TIPOS, true)) {
            $query->where('tipo', $tipo);
        }

        if ($request->boolean('recientes')) {
            $query->where('created_at', '>=', now()->subDays(30));
        }
    }

    /**
     * Orden solicitado.
     */
    private function aplicarOrden(Builder $query, Request $request): void
    {
        match ($request->input('orden', 'recientes')) {
            'antiguos' => $query->oldest(),
            'fecha' => $query->orderBy('fecha'),
            'fecha_desc' => $query->orderByDesc('fecha'),
            default => $query->latest(),
        };
    }

    /**
     * Tamaño de página acotado.
     */
    private function porPagina(Request $request): int
    {
        return min(50, max(1, (int) $request->input('per_page', 9)));
    }
}
