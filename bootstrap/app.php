<?php

use App\Http\Middleware\ApiRoleMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Idioma seleccionado por el usuario (sesión + cookie) en cada petición web.
        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            // Autorización por rol para la API (responde JSON, nunca redirige).
            'api.role' => ApiRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
        |------------------------------------------------------------------
        | API REST: respuestas JSON uniformes
        |------------------------------------------------------------------
        | Las peticiones a /api/* siempre se contestan en JSON, aunque el
        | cliente no envíe la cabecera Accept. Así Postman, pruebas y
        | consumidores externos reciben códigos HTTP y mensajes claros.
        */
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $e->getMessage() ?: 'Los datos enviados no son válidos.',
                    422,
                    $e->errors()
                );
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'No autenticado. Envía un token válido en la cabecera Authorization.',
                    401
                );
            }
        });

        $exceptions->render(function (AccessDeniedHttpException|AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $e->getMessage() ?: 'No tienes permiso para realizar esta acción.',
                    403
                );
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $e->getMessage() ?: 'El recurso solicitado no existe.',
                    404
                );
            }
        });

        // Enlaces de verificación inválidos, caducados o de otra cuenta: se
        // vuelve a la pantalla de aviso con un mensaje claro en español.
        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if ($request->routeIs('verification.verify')) {
                return redirect()->route('verification.notice')->with(
                    'error',
                    __('El enlace de verificación no es válido o ha caducado. Solicita uno nuevo.')
                );
            }
        });

        // Firma correcta pero el enlace no corresponde a la cuenta autenticada
        // (Laravel transforma el fallo de autorización del formulario en 403).
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->routeIs('verification.verify')) {
                return redirect()->route('verification.notice')->with(
                    'error',
                    __('El enlace de verificación no corresponde a tu cuenta o ya no es válido. Solicita uno nuevo.')
                );
            }
        });
    })->create();
