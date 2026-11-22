<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Datos para publicar una oferta de empleo (RF04).
 */
class StoreOfertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas reutilizables por el controlador unificado de publicaciones.
     *
     * @return array<string, array<int, string>>
     */
    public static function reglas(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'requisitos' => ['required', 'string', 'max:5000'],
            'lugar' => ['required', 'string', 'max:150'],
            'tipo_trabajo' => ['required', 'string', 'max:100'],
            'salario' => ['nullable', 'numeric', 'min:0'],
            'fecha_limite' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return static::reglas();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha_limite.after_or_equal' => 'La fecha límite debe ser hoy o una fecha futura.',
            'tipo_trabajo.required' => 'Indica la modalidad o categoría del empleo.',
        ];
    }
}
