<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Lista todos los usuarios identificando su rol (RF13).
     */
    public function index(Request $request)
    {
        $rol = $request->input('role');

        $usuarios = User::query()
            ->when(in_array($rol, [User::ROLE_ADMIN, User::ROLE_EMPLEADOR, User::ROLE_POSTULANTE], true),
                fn ($q) => $q->where('role', $rol))
            ->withCount(['ofertas', 'postulaciones'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.usuarios.index', compact('usuarios', 'rol'));
    }

    /**
     * Formulario de edición de un usuario (rol y datos) (RF13).
     */
    public function edit(User $usuario)
    {
        return view('admin.usuarios.edit', ['usuario' => $usuario]);
    }

    /**
     * Actualiza rol y datos del usuario (RF13).
     */
    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'role' => ['required', 'in:admin,empleador,postulante'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'distrito' => ['nullable', 'string', 'max:100'],
        ]);

        // Evita que el administrador se quite a sí mismo el acceso.
        if ($usuario->id === $request->user()->id && $data['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', __('No puedes cambiar tu propio rol de administrador.'));
        }

        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')
            ->with('success', __('Usuario actualizado correctamente.'));
    }

    /**
     * Elimina un usuario (RF13).
     */
    public function destroy(Request $request, User $usuario)
    {
        if ($usuario->id === $request->user()->id) {
            return back()->with('error', __('No puedes eliminar tu propia cuenta de administrador.'));
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', __('Usuario eliminado correctamente.'));
    }
}
