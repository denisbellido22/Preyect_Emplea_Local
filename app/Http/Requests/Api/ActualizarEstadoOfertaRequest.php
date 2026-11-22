<?php

namespace App\Http\Requests\Api;

use App\Models\Oferta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Moderación de una oferta por parte del Administrador (RF06).
 */
class ActualizarEstadoOfertaRequest extends FormRequest
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
            'estado' => [
                'required',
                'string',
                Rule::in([
                    Oferta::ESTADO_PENDIENTE,
                    Oferta::ESTADO_APROBADO,
                    Oferta::ESTADO_RECHAZADO,
                ]),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'estado.in' => 'El estado debe ser pendiente, aprobado o rechazado.',
        ];
    }
}
