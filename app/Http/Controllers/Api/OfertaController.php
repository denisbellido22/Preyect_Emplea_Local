<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ActualizarEstadoOfertaRequest;
use App\Http\Requests\Api\StoreOfertaRequest;
use App\Http\Requests\Api\UpdateOfertaRequest;
use App\Http\Resources\OfertaResource;
use App\Models\Oferta;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API de empleos (tabla real: ofertas).
 *
 * Las consultas son públicas y sólo devuelven ofertas aprobadas. Publicar,
 * editar o eliminar exige token de empleador y respeta la moderación del
 * administrador: toda oferta nueva o editada vuelve a estado "pendiente".
 */
class OfertaController extends Controller
{
    /**
     * GET /api/ofertas
     *
     * Filtros: q, titulo, ubicacion, categoria (o tipo_trabajo), recientes,
     * orden (recientes|antiguos|salario|salario_asc), page, per_page.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Oferta::query()->aprobadas()->with('empleador');

        $this->aplicarFiltros($query, $request);

        $this->aplicarOrden($query, $request);

        $ofertas = $query->paginate($this->porPagina($request))->withQueryString();

        return ApiResponse::paginated(
            $ofertas,
            OfertaResource::collection($ofertas),
            'Empleos obtenidos correctamente.'
        );
    }

    /**
     * GET /api/ofertas/recientes
     */
    public function recientes(Request $request): JsonResponse
    {
        $limite = min(50, max(1, (int) $request->input('limite', 10)));

        $ofertas = Oferta::query()
            ->aprobadas()
            ->with('empleador')
            ->latest()
            ->take($limite)
            ->get();

        return ApiResponse::success(
            OfertaResource::collection($ofertas),
            'Empleos recientes obtenidos correctamente.'
        );
    }

    /**
     * GET /api/ofertas/{oferta}
     *
     * Las ofertas pendientes o rechazadas sólo las ve su dueño o un admin.
     */
    public function show(Request $request, Oferta $oferta): JsonResponse
    {
        $user = $request->user();
        $esVisible = $oferta->estado === Oferta::ESTADO_APROBADO
            || ($user && ($user->isAdmin() || $user->id === $oferta->user_id));

        abort_unless($esVisible, 404, 'La oferta solicitada no existe o no está publicada.');

        $oferta->load('empleador');

        return ApiResponse::success(
            new OfertaResource($oferta),
            'Oferta obtenida correctamente.'
        );
    }

    /**
     * POST /api/ofertas (empleador con correo verificado).
     */
    public function store(StoreOfertaRequest $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user->isEmpleador(), 403, 'Sólo los empleadores pueden publicar ofertas.');
        $this->exigirCorreoVerificado($user);

        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['estado'] = Oferta::ESTADO_PENDIENTE;

        $oferta = Oferta::create($data)->load('empleador');

        return ApiResponse::created(
            new OfertaResource($oferta),
            'Oferta registrada. Quedará visible cuando el administrador la apruebe.'
        );
    }

    /**
     * PUT/PATCH /api/ofertas/{oferta} (dueño o administrador).
     */
    public function update(UpdateOfertaRequest $request, Oferta $oferta): JsonResponse
    {
        $user = $request->user();
        $this->autorizarGestion($user, $oferta);

        $data = $request->validated();

        // El empleador no puede autoaprobarse: al editar vuelve a moderación.
        if (! $user->isAdmin()) {
            $data['estado'] = Oferta::ESTADO_PENDIENTE;
        }

        $oferta->update($data);

        return ApiResponse::success(
            new OfertaResource($oferta->fresh()->load('empleador')),
            'Oferta actualizada. Será revisada nuevamente por el administrador.'
        );
    }

    /**
     * DELETE /api/ofertas/{oferta} (dueño o administrador).
     */
    public function destroy(Request $request, Oferta $oferta): JsonResponse
    {
        $this->autorizarGestion($request->user(), $oferta);

        $oferta->delete();

        return ApiResponse::success(null, 'Oferta eliminada correctamente.');
    }

    /**
     * PATCH /api/admin/ofertas/{oferta}/estado (sólo administrador).
     *
     * Mantiene el flujo de aprobación existente (RF06).
     */
    public function actualizarEstado(ActualizarEstadoOfertaRequest $request, Oferta $oferta): JsonResponse
    {
        $oferta->update(['estado' => $request->validated('estado')]);

        return ApiResponse::success(
            new OfertaResource($oferta->fresh()->load('empleador')),
            'Estado de la oferta actualizado correctamente.'
        );
    }

    /**
     * Aplica búsqueda por título, ubicación y categoría.
     */
    private function aplicarFiltros(Builder $query, Request $request): void
    {
        $termino = trim((string) $request->input('q'));

        if ($termino !== '') {
            $query->buscar($termino);
        }

        $titulo = trim((string) $request->input('titulo'));

        if ($titulo !== '') {
            $query->where('titulo', 'like', '%'.$titulo.'%');
        }

        $ubicacion = trim((string) $request->input('ubicacion'));

        if ($ubicacion !== '') {
            $query->where('lugar', 'like', '%'.$ubicacion.'%');
        }

        // "categoria" es el nombre público del filtro; en la base de datos la
        // categoría del empleo se guarda en tipo_trabajo (tiempo completo,
        // medio tiempo, prácticas, etc.).
        $categoria = trim((string) ($request->input('categoria') ?: $request->input('tipo_trabajo')));

        if ($categoria !== '') {
            $query->where('tipo_trabajo', $categoria);
        }

        if ($request->boolean('recientes')) {
            $query->where('created_at', '>=', now()->subDays(30));
        }
    }

    /**
     * Orden solicitado, siempre sobre ofertas aprobadas.
     */
    private function aplicarOrden(Builder $query, Request $request): void
    {
        match ($request->input('orden', 'recientes')) {
            'antiguos' => $query->oldest(),
            'salario' => $query->orderByDesc('salario'),
            'salario_asc' => $query->orderBy('salario'),
            default => $query->latest(),
        };
    }

    /**
     * Tamaño de página acotado para evitar respuestas gigantes.
     */
    private function porPagina(Request $request): int
    {
        return min(50, max(1, (int) $request->input('per_page', 9)));
    }

    /**
     * Sólo el dueño de la oferta o un administrador pueden modificarla.
     */
    private function autorizarGestion(User $user, Oferta $oferta): void
    {
        abort_unless(
            $user->isAdmin() || $oferta->user_id === $user->id,
            403,
            'No puedes gestionar ofertas de otro empleador.'
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
