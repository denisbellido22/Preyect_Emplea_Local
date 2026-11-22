<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mensaje opcional que acompaña una postulación (RF09).
 */
class StorePostulacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'mensaje' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
