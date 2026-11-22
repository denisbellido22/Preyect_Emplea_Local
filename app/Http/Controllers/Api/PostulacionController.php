<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePostulacionRequest;
use App\Http\Requests\Api\UpdatePostulacionEstadoRequest;
use App\Http\Resources\PostulacionResource;
use App\Models\Oferta;
use App\Models\Postulacion;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API de postulaciones (RF09, RF10, RF11 y RF12).
 *
 * - El postulante sólo gestiona y consulta sus propias postulaciones.
 * - El empleador consulta y evalúa las postulaciones de sus propias ofertas.
 * - El administrador conserva acceso de supervisión.
 */
class PostulacionController extends Controller
{
    /**
     * POST /api/ofertas/{oferta}/postulaciones
     */
    public function store(StorePostulacionRequest $request, Oferta $oferta): JsonResponse
    {
        $user = $request->user();

        $this->exigirCorreoVerificado($user);

        if ($oferta->estado !== Oferta::ESTADO_APROBADO) {
            return ApiResponse::error(
                'Sólo puedes postular a ofertas aprobadas y publicadas.',
                422
            );
        }

        // Primera barrera contra duplicados (RF10). La segunda es el índice
        // UNIQUE(oferta_id, user_id) de la base de datos.
        $yaPostulo = Postulacion::where('oferta_id', $oferta->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($yaPostulo) {
            return ApiResponse::error(
                'Ya te postulaste a esta oferta. No se permiten postulaciones duplicadas.',
                422,
                ['oferta_id' => ['Ya existe una postulación tuya para esta oferta.']]
            );
        }

        $postulacion = Postulacion::create([
            'oferta_id' => $oferta->id,
            'user_id' => $user->id,
            'estado' => Postulacion::ESTADO_PENDIENTE,
            'mensaje' => $request->validated('mensaje'),
        ]);

        return ApiResponse::created(
            new PostulacionResource($postulacion->load(['oferta', 'postulante'])),
            'Postulación enviada correctamente. Su estado inicial es pendiente.'
        );
    }

    /**
     * GET /api/postulaciones
     *
     * El contenido depende del rol de quien consulta.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $porPagina = min(50, max(1, (int) $request->input('per_page', 10)));

        $query = Postulacion::query()->with(['oferta', 'postulante'])->latest();

        if ($user->isPostulante()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isEmpleador()) {
            $idsOfertas = $user->ofertas()->pluck('id');
            $query->whereIn('oferta_id', $idsOfertas);
        }

        $postulaciones = $query->paginate($porPagina)->withQueryString();

        return ApiResponse::paginated(
            $postulaciones,
            PostulacionResource::collection($postulaciones),
            'Postulaciones obtenidas correctamente.'
        );
    }

    /**
     * GET /api/postulaciones/{postulacion}
     */
    public function show(Request $request, Postulacion $postulacion): JsonResponse
    {
        $user = $request->user();
        $postulacion->load(['oferta', 'postulante']);

        $puedeVer = $user->isAdmin()
            || $postulacion->user_id === $user->id
            || $postulacion->oferta?->user_id === $user->id;

        abort_unless($puedeVer, 403, 'No tienes permiso para consultar esta postulación.');

        return ApiResponse::success(
            new PostulacionResource($postulacion),
            'Postulación obtenida correctamente.'
        );
    }

    /**
     * GET /api/ofertas/{oferta}/postulantes
     *
     * El empleador autorizado consulta los postulantes de su oferta.
     */
    public function postulantes(Request $request, Oferta $oferta): JsonResponse
    {
        $this->autorizarOferta($request->user(), $oferta);

        $porPagina = min(50, max(1, (int) $request->input('per_page', 15)));

        $postulaciones = $oferta->postulaciones()
            ->with('postulante')
            ->latest()
            ->paginate($porPagina)
            ->withQueryString();

        return ApiResponse::paginated(
            $postulaciones,
            PostulacionResource::collection($postulaciones),
            'Postulantes de la oferta obtenidos correctamente.'
        );
    }

    /**
     * PATCH /api/postulaciones/{postulacion}/estado
     *
     * El empleador dueño de la oferta (o un admin) evalúa la candidatura.
     */
    public function actualizarEstado(
        UpdatePostulacionEstadoRequest $request,
        Postulacion $postulacion
    ): JsonResponse {
        $postulacion->load('oferta');
        $this->autorizarOferta($request->user(), $postulacion->oferta);

        $postulacion->update(['estado' => $request->validated('estado')]);

        return ApiResponse::success(
            new PostulacionResource($postulacion->fresh()->load(['oferta', 'postulante'])),
            'Estado de la postulación actualizado correctamente.'
        );
    }

    /**
     * Sólo el dueño de la oferta o un administrador.
     */
    private function autorizarOferta(User $user, ?Oferta $oferta): void
    {
        abort_unless(
            $oferta !== null && ($user->isAdmin() || $oferta->user_id === $user->id),
            403,
            'No puedes gestionar postulaciones de ofertas de otro empleador.'
        );
    }

    /**
     * Postularse envía datos personales: exige correo verificado (RF09).
     */
    private function exigirCorreoVerificado(User $user): void
    {
        abort_unless(
            $user->hasVerifiedEmail(),
            403,
            'Debes verificar tu correo electrónico antes de postularte.'
        );
    }
}
