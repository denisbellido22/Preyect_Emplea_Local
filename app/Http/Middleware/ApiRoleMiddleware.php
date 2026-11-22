<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autoriza rutas de la API según el rol del usuario (RF20).
 *
 * A diferencia de RoleMiddleware (interfaz web), nunca redirige al login:
 * responde 401 si no hay token válido y 403 si el rol no está autorizado.
 *
 * Uso: ->middleware('api.role:empleador') o 'api.role:admin,empleador'
 */
class ApiRoleMiddleware
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error(
                'No autenticado. Envía un token válido en la cabecera Authorization.',
                401
            );
        }

        if (! in_array($user->role, $roles, true)) {
            return ApiResponse::error('No tienes permiso para realizar esta acción.', 403);
        }

        return $next($request);
    }
}
