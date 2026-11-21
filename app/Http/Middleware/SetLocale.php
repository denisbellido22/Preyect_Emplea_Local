<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica el idioma elegido por el usuario en cada petición web.
 *
 * El idioma se guarda en la sesión y, además, en una cookie de larga duración
 * para conservarlo al cerrar sesión o al volver más tarde. Si no hay una
 * preferencia válida se usa el idioma por defecto de la aplicación (español).
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $disponibles = array_keys((array) config('app.supported_locales', []));
        $locale = $request->session()->get('locale') ?? $request->cookie('locale');

        if (! is_string($locale) || ! in_array($locale, $disponibles, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
