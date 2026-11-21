<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Http\Request;

class PostulacionController extends Controller
{
    /**
     * El postulante se postula a una oferta aprobada (RF09, RF10).
     */
    public function store(Request $request, Oferta $oferta)
    {
        $user = $request->user();

        if ($oferta->estado !== Oferta::ESTADO_APROBADO) {
            return back()->with('error', __('Sólo puedes postular a ofertas aprobadas.'));
        }

        // RF10: validación a nivel de aplicación
        $existe = Postulacion::where('oferta_id', $oferta->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($existe) {
            return back()->with('error', __('Ya te postulaste a esta oferta. No se permiten postulaciones duplicadas.'));
        }

        $data = $request->validate([
            'mensaje' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            Postulacion::create([
                'oferta_id' => $oferta->id,
                'user_id' => $user->id,
                'estado' => Postulacion::ESTADO_PENDIENTE, // estado inicial
                'mensaje' => $data['mensaje'] ?? null,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // RF10: restricción de base de datos como segunda barrera
            return back()->with('error', __('Ya te postulaste a esta oferta. No se permiten postulaciones duplicadas.'));
        }

        return redirect()->route('postulaciones.index')
            ->with('success', __('Postulación enviada correctamente. Estado inicial: :estado.', ['estado' => __('status.pendiente')]));
    }

    /**
     * Mis postulaciones (RF11).
     */
    public function index(Request $request)
    {
        $postulaciones = $request->user()->postulaciones()
            ->with('oferta.empleador')
            ->latest()
            ->paginate(10);

        return view('postulaciones.index', compact('postulaciones'));
    }

    /**
     * Postulaciones recibidas en las ofertas del empleador (RF12).
     */
    public function postulantes(Request $request)
    {
        $idsOfertas = $request->user()->ofertas()->pluck('id');

        $postulaciones = Postulacion::with(['postulante', 'oferta'])
            ->whereIn('oferta_id', $idsOfertas)
            ->latest()
            ->paginate(15);

        return view('empleador.postulantes', compact('postulaciones'));
    }

    /**
     * El empleador actualiza el estado de una postulación de sus ofertas (RF11/RF12).
     */
    public function actualizarEstado(Request $request, Postulacion $postulacion)
    {
        $user = $request->user();
        $postulacion->load('oferta');

        abort_unless(
            $user->isAdmin() || $postulacion->oferta->user_id === $user->id,
            403,
            __('No puedes gestionar postulaciones de ofertas de otro empleador.')
        );

        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,revisada,aceptada,rechazada'],
        ]);

        $postulacion->update(['estado' => $data['estado']]);

        return back()->with('success', __('Estado de la postulación actualizado a :estado.', ['estado' => __('status.'.$data['estado'])]));
    }
}
