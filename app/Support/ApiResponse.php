<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Formato único de respuesta para toda la API REST de Emplea Local.
 *
 * Éxito:  { success: true,  message, data, meta? }
 * Error:  { success: false, message, errors? }
 */
class ApiResponse
{
    /**
     * Respuesta correcta (200 por defecto).
     */
    public static function success(
        mixed $data = null,
        string $message = 'Operación realizada correctamente.',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    /**
     * Recurso creado (201).
     */
    public static function created(mixed $data, string $message = 'Recurso creado correctamente.'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Respuesta de error con código HTTP explícito.
     *
     * @param  array<string, array<int, string>>  $errors
     */
    public static function error(
        string $message,
        int $status = 400,
        array $errors = []
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Respuesta paginada: los elementos van en "data" y la paginación en "meta".
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        mixed $items,
        string $message = 'Listado obtenido correctamente.'
    ): JsonResponse {
        return self::success($items, $message, 200, self::meta($paginator));
    }

    /**
     * Metadatos de paginación.
     *
     * @return array<string, mixed>
     */
    public static function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
