<?php

namespace App\Http\Resources;

use App\Models\Oportunidad;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Datos de una beca, convocatoria, examen, práctica, curso o evento.
 *
 * @mixin Oportunidad
 */
class OportunidadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'tipo' => $this->tipo,
            'tipo_label' => __('opportunity_type.'.$this->tipo),
            'descripcion' => $this->descripcion,
            'institucion' => $this->institucion,
            'fecha' => optional($this->fecha)->toDateString(),
            'lugar' => $this->lugar,
            'enlace' => $this->enlace,
            'imagen' => $this->imagen ? asset($this->imagen) : null,
            'estado' => $this->estado,
            'estado_label' => __('status.'.$this->estado),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'url' => route('oportunidades.show', $this->resource),
        ];
    }
}
