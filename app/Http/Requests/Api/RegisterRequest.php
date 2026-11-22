<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Registro de un Empleador o Postulante desde la API (RF01).
 * El rol Administrador nunca puede crearse desde este endpoint.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', PasswordRule::min(8), 'confirmed'],
            'role' => ['required', 'string', Rule::in(['empleador', 'postulante'])],
            'telefono' => ['nullable', 'string', 'max:20'],
            'distrito' => ['nullable', 'string', 'max:150'],
            'profesion' => ['nullable', 'string', 'max:150'],
            'empresa' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.in' => 'El rol debe ser empleador o postulante.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'email.unique' => 'Ya existe una cuenta registrada con ese correo electrónico.',
        ];
    }
}
