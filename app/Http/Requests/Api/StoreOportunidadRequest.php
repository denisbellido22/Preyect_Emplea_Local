<?php

namespace App\Http\Requests\Api;

use App\Models\Oportunidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos para crear una beca, convocatoria, examen, práctica, curso o evento
 * desde la API (RF14). Sólo el Administrador puede gestionarlas.
 */
class StoreOportunidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas reutilizables por el controlador unificado de publicaciones.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function reglas(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'tipo' => ['required', 'string', Rule::in(Oportunidad::TIPOS)],
            'descripcion' => ['required', 'string', 'max:5000'],
            'institucion' => ['nullable', 'string', 'max:150'],
            'fecha' => ['nullable', 'date'],
            'lugar' => ['nullable', 'string', 'max:150'],
            'enlace' => ['nullable', 'url', 'max:255'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'estado' => ['nullable', 'string', Rule::in([Oportunidad::ESTADO_PUBLICADO, Oportunidad::ESTADO_BORRADOR])],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
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
            'tipo.in' => 'El tipo debe ser uno de: '.implode(', ', Oportunidad::TIPOS).'.',
            'enlace.url' => 'El enlace debe ser una URL válida.',
        ];
    }
}
