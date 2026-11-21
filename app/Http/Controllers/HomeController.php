<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Models\Publicidad;
use App\Models\User;
use App\Support\PublicacionPublica;

class HomeController extends Controller
{
    /**
     * Página principal: últimas publicaciones públicas (empleos, becas,
     * convocatorias, exámenes y capacitaciones), estadísticas reales,
     * instituciones participantes y publicidad vigente.
     */
    public function index()
    {
        // Same feed que se envía por WebSocket: la portada y el tiempo real
        // comparten formato, por lo que las tarjetas nuevas encajan sin recargar.
        $ultimasPublicaciones = PublicacionPublica::recientes(30);

        $publicidades = Publicidad::vigentes()
            ->latest()
            ->take(3)
            ->get();

        $estadisticas = [
            'empleos' => Oferta::aprobadas()->count(),
            'oportunidades' => Oportunidad::publicadas()->count(),
            'instituciones' => User::query()
                ->where('role', User::ROLE_EMPLEADOR)
                ->whereHas('ofertas', fn ($q) => $q->aprobadas())
                ->count(),
            'distritos' => Oferta::aprobadas()
                ->distinct()
                ->count('lugar'),
        ];

        $instituciones = $this->institucionesParticipantes();

        return view('home', compact(
            'ultimasPublicaciones',
            'publicidades',
            'estadisticas',
            'instituciones'
        ));
    }

    /**
     * Empresas e instituciones con publicaciones visibles (máximo 8).
     *
     * @return array<int, string>
     */
    private function institucionesParticipantes(): array
    {
        $empresas = Oferta::aprobadas()
            ->with('empleador')
            ->latest()
            ->take(40)
            ->get()
            ->map(fn (Oferta $oferta) => $oferta->empleador?->empresa ?: $oferta->empleador?->nombreCompleto());

        $instituciones = Oportunidad::publicadas()
            ->whereNotNull('institucion')
            ->latest()
            ->take(40)
            ->pluck('institucion');

        return $empresas
            ->concat($instituciones)
            ->filter(fn ($nombre) => filled($nombre))
            ->unique()
            ->take(8)
            ->values()
            ->all();
    }
}
