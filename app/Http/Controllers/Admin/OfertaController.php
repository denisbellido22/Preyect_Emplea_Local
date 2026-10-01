<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Oferta;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    /**
     * Lista todas las ofertas para moderación (RF06).
     */
    public function index(Request $request)
    {
        $estado = $request->input('estado');

        $ofertas = Oferta::query()
            ->when(in_array($estado, [Oferta::ESTADO_PENDIENTE, Oferta::ESTADO_APROBADO, Oferta::ESTADO_RECHAZADO], true),
                fn ($q) => $q->where('estado', $estado))
            ->with('empleador')
            ->withCount('postulaciones')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.ofertas.index', compact('ofertas', 'estado'));
    }

    /**
     * Aprueba una oferta (RF06).
     */
    public function aprobar(Oferta $oferta)
    {
        $oferta->update(['estado' => Oferta::ESTADO_APROBADO]);

        return back()->with('success', 'Oferta aprobada y publicada.');
    }

    /**
     * Rechaza una oferta (RF06).
     */
    public function rechazar(Oferta $oferta)
    {
        $oferta->update(['estado' => Oferta::ESTADO_RECHAZADO]);

        return back()->with('success', 'Oferta rechazada.');
    }

    /**
     * Formulario de edición administrativa (RF06).
     */
    public function edit(Oferta $oferta)
    {
        return view('admin.ofertas.edit', compact('oferta'));
    }

    /**
     * Actualiza una oferta desde el panel administrativo (RF06).
     */
    public function update(Request $request, Oferta $oferta)
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'requisitos' => ['required', 'string', 'max:5000'],
            'lugar' => ['required', 'string', 'max:150'],
            'tipo_trabajo' => ['required', 'string', 'max:100'],
            'salario' => ['nullable', 'numeric', 'min:0'],
            'fecha_limite' => ['required', 'date'],
            'estado' => ['required', 'in:pendiente,aprobado,rechazado'],
        ]);

        $oferta->update($data);

        return redirect()->route('admin.ofertas.index')
            ->with('success', 'Oferta actualizada correctamente.');
    }

    /**
     * Elimina una oferta (RF06).
     */
    public function destroy(Oferta $oferta)
    {
        $oferta->delete();

        return back()->with('success', 'Oferta eliminada correctamente.');
    }
}
