<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Muestra el perfil del usuario autenticado (RF03).
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return view('perfil.show', compact('user'));
    }

    /**
     * Formulario de edición del perfil (RF03).
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('perfil.edit', compact('user'));
    }

    /**
     * Actualiza el perfil. Los campos dependen del rol (RF03).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'distrito' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ];

        if ($user->isPostulante()) {
            $rules['dni'] = ['required', 'numeric', 'digits:8', Rule::unique('users', 'dni')->ignore($user->id)];
            $rules['profesion'] = ['required', 'string', 'max:150'];
            $rules['experiencia'] = ['nullable', 'string', 'max:3000'];
        } else {
            $rules['empresa'] = ['nullable', 'string', 'max:150'];
            $rules['dni'] = ['nullable', 'numeric', 'digits:8', Rule::unique('users', 'dni')->ignore($user->id)];
            $rules['profesion'] = ['nullable', 'string', 'max:150'];
        }

        $data = $request->validate($rules);

        $user->update($data);

        return redirect()->route('perfil.show')
            ->with('success', 'Perfil actualizado correctamente.');
    }
}
