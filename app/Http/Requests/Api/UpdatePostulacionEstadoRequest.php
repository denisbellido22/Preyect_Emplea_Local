<?php

namespace App\Http\Requests\Api;

use App\Models\Postulacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Cambio de estado de una postulación por parte del empleador (RF11/RF12).
 */
class UpdatePostulacionEstadoRequest extends FormRequest
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
                    Postulacion::ESTADO_PENDIENTE,
                    Postulacion::ESTADO_REVISADA,
                    Postulacion::ESTADO_ACEPTADA,
                    Postulacion::ESTADO_RECHAZADA,
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
            'estado.in' => 'El estado debe ser pendiente, revisada, aceptada o rechazada.',
        ];
    }
}
