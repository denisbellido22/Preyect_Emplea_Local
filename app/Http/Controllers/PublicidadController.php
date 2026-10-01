<?php

namespace App\Http\Controllers;

use App\Models\Publicidad;
use App\Support\ImageUploader;
use Illuminate\Http\Request;

class PublicidadController extends Controller
{
    /**
     * Solicitudes de publicidad del empleador autenticado (RF16).
     */
    public function index(Request $request)
    {
        $publicidades = $request->user()->publicidades()->latest()->paginate(10);

        return view('empleador.publicidades.index', compact('publicidades'));
    }

    /**
     * Formulario de nueva solicitud de publicidad (RF16).
     */
    public function create()
    {
        return view('empleador.publicidades.create');
    }

    /**
     * Registra la solicitud con estado PENDIENTE y guarda la imagen real (RF16, RF18).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string', 'max:3000'],
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['estado'] = Publicidad::ESTADO_PENDIENTE;
        $data['imagen'] = ImageUploader::guardar($request->file('imagen'), 'publicidades');

        Publicidad::create($data);

        return redirect()->route('empleador.publicidades.index')
            ->with('success', 'Solicitud de publicidad enviada. Quedará pendiente hasta la aprobación del administrador.');
    }
}
