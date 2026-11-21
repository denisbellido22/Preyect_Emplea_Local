<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publicidad;
use Illuminate\Http\Request;

class PublicidadController extends Controller
{
    /**
     * Lista las solicitudes de publicidad (RF17).
     */
    public function index(Request $request)
    {
        $estado = $request->input('estado');

        $publicidades = Publicidad::query()
            ->when(in_array($estado, [
                Publicidad::ESTADO_PENDIENTE,
                Publicidad::ESTADO_APROBADO,
                Publicidad::ESTADO_RECHAZADO,
                Publicidad::ESTADO_FINALIZADO,
            ], true), fn ($q) => $q->where('estado', $estado))
            ->with('empleador')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.publicidades.index', compact('publicidades', 'estado'));
    }

    /**
     * Aprueba una solicitud de publicidad (RF17).
     */
    public function aprobar(Publicidad $publicidad)
    {
        $publicidad->update(['estado' => Publicidad::ESTADO_APROBADO]);

        return back()->with('success', __('Publicidad aprobada.'));
    }

    /**
     * Rechaza una solicitud de publicidad (RF17).
     */
    public function rechazar(Publicidad $publicidad)
    {
        $publicidad->update(['estado' => Publicidad::ESTADO_RECHAZADO]);

        return back()->with('success', __('Publicidad rechazada.'));
    }

    /**
     * Finaliza una publicidad aprobada (RF17).
     */
    public function finalizar(Publicidad $publicidad)
    {
        $publicidad->update(['estado' => Publicidad::ESTADO_FINALIZADO]);

        return back()->with('success', __('Publicidad finalizada.'));
    }
}
