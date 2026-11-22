<?php

namespace App\Http\Resources;

use App\Models\Postulacion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Postulación vista por su autor, por el empleador dueño de la oferta o por
 * un administrador. Incluye los datos de contacto que el empleador necesita
 * para gestionar la candidatura, pero nunca DNI, contraseñas ni tokens.
 *
 * @mixin Postulacion
 */
class PostulacionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'estado' => $this->estado,
            'estado_label' => __('status.'.$this->estado),
            'mensaje' => $this->mensaje,
            'oferta' => $this->whenLoaded('oferta', fn () => [
                'id' => $this->oferta?->id,
                'titulo' => $this->oferta?->titulo,
                'lugar' => $this->oferta?->lugar,
                'estado' => $this->oferta?->estado,
            ]),
            'postulante' => $this->whenLoaded('postulante', fn () => [
                'id' => $this->postulante?->id,
                'nombre' => $this->postulante?->nombreCompleto(),
                'profesion' => $this->postulante?->profesion,
                'distrito' => $this->postulante?->distrito,
                'email' => $this->postulante?->email,
                'telefono' => $this->postulante?->telefono,
            ]),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
