<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
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
