<?php

namespace App\Http\Resources;

use App\Models\Oferta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Datos públicos de una oferta de empleo.
 *
 * Sólo expone al empleador su identificador, nombre visible y empresa:
 * nunca correo, DNI ni teléfono.
 *
 * @mixin Oferta
 */
class OfertaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'requisitos' => $this->requisitos,
            'lugar' => $this->lugar,
            'tipo_trabajo' => $this->tipo_trabajo,
            'salario' => $this->salario !== null ? (float) $this->salario : null,
            'fecha_limite' => optional($this->fecha_limite)->toDateString(),
            'estado' => $this->estado,
            'estado_label' => __('status.'.$this->estado),
            'empleador' => $this->whenLoaded('empleador', fn () => [
                'id' => $this->empleador?->id,
                'nombre' => $this->empleador?->nombreCompleto(),
                'empresa' => $this->empleador?->empresa,
            ]),
            'postulaciones_count' => $this->whenCounted('postulaciones'),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'url' => route('ofertas.show', $this->resource),
        ];
    }
}
