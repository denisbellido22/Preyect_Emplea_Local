<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\MailFailureReporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerException;

/**
 * Autenticación de la API mediante tokens personales (Laravel Sanctum).
 *
 * Los endpoints públicos de consulta no requieren token. Para publicar,
 * postularse o consultar datos privados se envía:
 *     Authorization: Bearer <token>
 */
class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     *
     * Registra un empleador o un postulante y devuelve un token listo para usar.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $partes = preg_split('/\s+/', trim($data['name']), 2);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'nombres' => $partes[0] ?? $data['name'],
            'apellidos' => $partes[1] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'distrito' => $data['distrito'] ?? null,
            'profesion' => $data['profesion'] ?? null,
            'empresa' => $data['empresa'] ?? null,
        ]);

        // El correo de verificación es el mismo del sitio web. Si el SMTP
        // falla, la cuenta ya está creada: se registra el aviso y se continúa.
        try {
            $user->sendEmailVerificationNotification();
        } catch (MailerException $e) {
            MailFailureReporter::report($e, 'No se pudo enviar el correo de verificación (API).');
        }

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $this->crearToken($user, $request),
            'token_type' => 'Bearer',
        ], 'Cuenta creada correctamente. Revisa tu correo para verificarla.', 201);
    }

    /**
     * POST /api/auth/login
     *
     * Devuelve un token personal si las credenciales son correctas.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->toString())->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return ApiResponse::error(
                'Las credenciales no coinciden con nuestros registros.',
                401
            );
        }

        return ApiResponse::success([
            'user' => new UserResource($user),
            'token' => $this->crearToken($user, $request),
            'token_type' => 'Bearer',
        ], 'Sesión iniciada correctamente.');
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($request->user()),
            'Perfil obtenido correctamente.'
        );
    }

    /**
     * POST /api/auth/logout
     *
     * Revoca únicamente el token usado en esta petición.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return ApiResponse::success(null, 'Sesión cerrada correctamente.');
    }

    /**
     * Crea el token con un nombre identificable (dispositivo o navegador).
     */
    private function crearToken(User $user, Request $request): string
    {
        $nombre = $request->input('device_name') ?: 'api';
        $agente = $request->userAgent();

        if ($agente) {
            $nombre .= ' - '.mb_substr($agente, 0, 80);
        }

        return $user->createToken($nombre)->plainTextToken;
    }
}
