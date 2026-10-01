<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Oportunidad;
use App\Support\ImageUploader;
use Illuminate\Http\Request;

class OportunidadController extends Controller
{
    /**
     * Lista de oportunidades (RF14).
     */
    public function index()
    {
        $oportunidades = Oportunidad::latest()->paginate(15);

        return view('admin.oportunidades.index', compact('oportunidades'));
    }

    /**
     * Formulario de creación (RF14).
     */
    public function create()
    {
        $tipos = Oportunidad::TIPOS;

        return view('admin.oportunidades.create', compact('tipos'));
    }

    /**
     * Guarda una oportunidad con imagen real (RF14, RF18).
     */
    public function store(Request $request)
    {
        $data = $this->validar($request);

        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('imagen')) {
            $data['imagen'] = ImageUploader::guardar($request->file('imagen'), 'oportunidades');
        }

        Oportunidad::create($data);

        return redirect()->route('admin.oportunidades.index')
            ->with('success', 'Oportunidad creada correctamente.');
    }

    /**
     * Formulario de edición (RF14).
     */
    public function edit(Oportunidad $oportunidad)
    {
        $tipos = Oportunidad::TIPOS;

        return view('admin.oportunidades.edit', compact('oportunidad', 'tipos'));
    }

    /**
     * Actualiza una oportunidad, reemplazando la imagen si se sube otra (RF14).
     */
    public function update(Request $request, Oportunidad $oportunidad)
    {
        $data = $this->validar($request);

        if ($request->hasFile('imagen')) {
            ImageUploader::eliminar($oportunidad->imagen);
            $data['imagen'] = ImageUploader::guardar($request->file('imagen'), 'oportunidades');
        }

        $oportunidad->update($data);

        return redirect()->route('admin.oportunidades.index')
            ->with('success', 'Oportunidad actualizada correctamente.');
    }

    /**
     * Elimina una oportunidad y su imagen (RF14).
     */
    public function destroy(Oportunidad $oportunidad)
    {
        ImageUploader::eliminar($oportunidad->imagen);
        $oportunidad->delete();

        return redirect()->route('admin.oportunidades.index')
            ->with('success', 'Oportunidad eliminada correctamente.');
    }

    /**
     * Reglas de validación con mensajes en español (RF14/RF18).
     */
    private function validar(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'tipo' => ['required', 'in:'.implode(',', Oportunidad::TIPOS)],
            'descripcion' => ['required', 'string', 'max:5000'],
            'institucion' => ['nullable', 'string', 'max:150'],
            'fecha' => ['nullable', 'date'],
            'lugar' => ['nullable', 'string', 'max:150'],
            'enlace' => ['nullable', 'url', 'max:255'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'estado' => ['required', 'in:publicado,borrador'],
        ]);
    }
}
