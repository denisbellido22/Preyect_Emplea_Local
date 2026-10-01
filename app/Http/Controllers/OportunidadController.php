<?php

namespace App\Http\Controllers;

use App\Models\Oportunidad;
use Illuminate\Http\Request;

class OportunidadController extends Controller
{
    /**
     * Los usuarios consultan las oportunidades publicadas (RF15).
     */
    public function index(Request $request)
    {
        $tipo = $request->input('tipo');

        $oportunidades = Oportunidad::publicadas()
            ->when(in_array($tipo, Oportunidad::TIPOS, true), fn ($q) => $q->where('tipo', $tipo))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $tipos = Oportunidad::TIPOS;

        return view('oportunidades.index', compact('oportunidades', 'tipos', 'tipo'));
    }

    /**
     * Detalle de una oportunidad publicada (RF15).
     */
    public function show(Oportunidad $oportunidad)
    {
        abort_unless(
            $oportunidad->estado === Oportunidad::ESTADO_PUBLICADO || (auth()->check() && auth()->user()->isAdmin()),
            404
        );

        return view('oportunidades.show', compact('oportunidad'));
    }
}
