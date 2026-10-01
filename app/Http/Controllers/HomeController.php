<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Models\Publicidad;

class HomeController extends Controller
{
    /**
     * Página principal: ofertas aprobadas, oportunidades publicadas y publicidad vigente.
     */
    public function index()
    {
        $ofertas = Oferta::aprobadas()
            ->with('empleador')
            ->latest()
            ->take(6)
            ->get();

        $oportunidades = Oportunidad::publicadas()
            ->latest()
            ->take(3)
            ->get();

        $publicidades = Publicidad::vigentes()
            ->latest()
            ->take(3)
            ->get();

        return view('home', compact('ofertas', 'oportunidades', 'publicidades'));
    }
}
