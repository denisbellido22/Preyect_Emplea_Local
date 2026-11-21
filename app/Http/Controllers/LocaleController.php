<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

/**
 * Cambia el idioma de la interfaz (español / inglés).
 */
class LocaleController extends Controller
{
    /**
     * Guarda el idioma elegido y vuelve a la misma página donde estaba el usuario.
     *
     * La ruta exige POST + token CSRF y sólo acepta los idiomas declarados en
     * config/app.php, de modo que no se puede usar como redirección abierta:
     * el destino se resuelve con la página anterior de la propia sesión.
     */
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, (array) config('app.supported_locales', [])), 404);

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        return redirect()
            ->to($this->destinoSeguro($request))
            ->withCookie(cookie()->forever('locale', $locale));
    }

    /**
     * Página anterior dentro del mismo sitio; si no la hay, el inicio.
     *
     * Se compara el host para no aceptar un Referer externo como destino
     * (evita redirecciones abiertas).
     */
    private function destinoSeguro(Request $request): string
    {
        $anterior = url()->previous();
        $hostAnterior = parse_url($anterior, PHP_URL_HOST);

        if ($hostAnterior && strcasecmp($hostAnterior, $request->getHost()) === 0) {
            return $anterior;
        }

        return route('home');
    }
}
