<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    /**
     * Listado público de ofertas aprobadas con búsqueda real (RF07).
     */
    public function index(Request $request)
    {
        $termino = $request->input('q');

        $ofertas = Oferta::aprobadas()
            ->buscar($termino)
            ->with('empleador')
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('ofertas.index', compact('ofertas', 'termino'));
    }

    /**
     * Detalle de una oferta (RF08).
     */
    public function show(Request $request, Oferta $oferta)
    {
        // Sólo ofertas aprobadas son visibles; el dueño y el admin pueden verlas todas.
        $user = $request->user();
        $puedeVer = $oferta->estado === Oferta::ESTADO_APROBADO
            || ($user && ($user->isAdmin() || $user->id === $oferta->user_id));

        abort_unless($puedeVer, 404);

        $oferta->load(['empleador', 'postulaciones' => function ($q) {
            $q->latest();
        }]);

        $yaPostulo = $user && $user->isPostulante()
            ? $oferta->postulaciones()->where('user_id', $user->id)->exists()
            : false;

        return view('ofertas.show', compact('oferta', 'yaPostulo'));
    }

    /**
     * Ofertas del empleador autenticado (RF05).
     */
    public function misOfertas(Request $request)
    {
        $ofertas = $request->user()->ofertas()
            ->withCount('postulaciones')
            ->latest()
            ->paginate(10);

        return view('empleador.ofertas.index', compact('ofertas'));
    }

    /**
     * Formulario de nueva oferta (RF04).
     */
    public function create()
    {
        return view('empleador.ofertas.create');
    }

    /**
     * Guarda la oferta con estado PENDIENTE (RF04).
     */
    public function store(Request $request)
    {
        $data = $this->validarOferta($request);

        $data['user_id'] = $request->user()->id;
        $data['estado'] = Oferta::ESTADO_PENDIENTE;

        Oferta::create($data);

        return redirect()->route('empleador.ofertas.index')
            ->with('success', __('Oferta registrada. Quedará visible cuando el administrador la apruebe.'));
    }

    /**
     * Formulario de edición de la propia oferta (RF05).
     */
    public function edit(Request $request, Oferta $oferta)
    {
        $this->autorizarPropietario($request, $oferta);

        return view('empleador.ofertas.edit', compact('oferta'));
    }

    /**
     * Actualiza la propia oferta (RF05). Vuelve a estado PENDIENTE.
     */
    public function update(Request $request, Oferta $oferta)
    {
        $this->autorizarPropietario($request, $oferta);

        $data = $this->validarOferta($request);
        // Al editar, la oferta debe volver a moderación.
        $data['estado'] = Oferta::ESTADO_PENDIENTE;

        $oferta->update($data);

        return redirect()->route('empleador.ofertas.index')
            ->with('success', __('Oferta actualizada. Será revisada nuevamente por el administrador.'));
    }

    /**
     * Elimina la propia oferta (RF05).
     */
    public function destroy(Request $request, Oferta $oferta)
    {
        $this->autorizarPropietario($request, $oferta);

        $oferta->delete();

        return redirect()->route('empleador.ofertas.index')
            ->with('success', __('Oferta eliminada correctamente.'));
    }

    /**
     * Reglas de validación del lado del servidor (RF04).
     */
    private function validarOferta(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'requisitos' => ['required', 'string', 'max:5000'],
            'lugar' => ['required', 'string', 'max:150'],
            'tipo_trabajo' => ['required', 'string', 'max:100'],
            'salario' => ['nullable', 'numeric', 'min:0'],
            'fecha_limite' => ['required', 'date', 'after_or_equal:today'],
        ]);
    }

    /**
     * Garantiza que sólo el dueño (o un admin) gestione la oferta (RF05).
     */
    private function autorizarPropietario(Request $request, Oferta $oferta): void
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $oferta->user_id === $user->id, 403, __('No puedes gestionar ofertas de otro empleador.'));
    }
}
