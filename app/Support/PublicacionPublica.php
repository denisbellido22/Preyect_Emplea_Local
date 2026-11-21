<?php

namespace App\Support;

use App\Models\Oferta;
use App\Models\Oportunidad;
use Illuminate\Support\Str;

/**
 * Proyección pública de una oferta u oportunidad.
 *
 * Es el ÚNICO formato que se envía por los canales WebSocket y el que usa la
 * portada. Sólo incluye campos visibles para cualquier visitante: nunca
 * correos, DNI, teléfonos, postulaciones ni datos del empleador.
 */
class PublicacionPublica
{
    /** Categorías que se muestran en los filtros de la portada. */
    public const CATEGORIAS = [
        'empleo' => 'Empleo',
        'beca' => 'Beca',
        'convocatoria' => 'Convocatoria',
        'examen' => 'Examen',
        'practica' => 'Práctica',
        'capacitacion' => 'Capacitación',
        'curso' => 'Curso',
        'charla' => 'Charla',
        'evento' => 'Evento',
        'otra' => 'Otra',
    ];

    /**
     * Ofertas aprobadas + oportunidades publicadas, de la más reciente a la más antigua.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function recientes(int $limite = 24): array
    {
        $ofertas = Oferta::aprobadas()
            ->with('empleador')
            ->latest()
            ->take($limite)
            ->get()
            ->map(fn (Oferta $oferta) => self::desdeOferta($oferta));

        $oportunidades = Oportunidad::publicadas()
            ->latest()
            ->take($limite)
            ->get()
            ->map(fn (Oportunidad $oportunidad) => self::desdeOportunidad($oportunidad));

        return $ofertas
            ->concat($oportunidades)
            ->sortByDesc(fn (array $publicacion) => $publicacion['orden'])
            ->take($limite)
            ->values()
            ->all();
    }

    /**
     * Convierte una oferta aprobada en una tarjeta pública.
     *
     * @return array<string, mixed>
     */
    public static function desdeOferta(Oferta $oferta): array
    {
        $oferta->loadMissing('empleador');

        return [
            'key' => 'oferta:'.$oferta->id,
            'tipo_publicacion' => 'oferta',
            'id' => $oferta->id,
            'categoria' => 'empleo',
            'categoria_label' => __('opportunity_type.empleo'),
            'tipo_trabajo' => $oferta->tipo_trabajo ? __('job_type.'.$oferta->tipo_trabajo) : null,
            'titulo' => $oferta->titulo,
            'descripcion' => Str::limit(strip_tags($oferta->descripcion), 150),
            'institucion' => $oferta->empleador?->empresa ?: $oferta->empleador?->nombreCompleto(),
            'lugar' => $oferta->lugar,
            'salario' => $oferta->salario !== null
                ? 'S/ '.number_format((float) $oferta->salario, 2)
                : null,
            'imagen' => null,
            'publicado_en' => optional($oferta->created_at)->toIso8601String(),
            'publicado_legible' => optional($oferta->created_at)->format('d/m/Y H:i'),
            'publicado_relativo' => optional($oferta->created_at)
                ?->locale(app()->getLocale())
                ->diffForHumans(),
            'fecha_limite' => optional($oferta->fecha_limite)->toDateString(),
            'fecha_limite_legible' => optional($oferta->fecha_limite)->format('d/m/Y'),
            'estado' => $oferta->estado,
            'estado_label' => __('status.'.$oferta->estado),
            'url' => route('ofertas.show', $oferta),
            'orden' => optional($oferta->created_at)->getTimestamp() ?? 0,
        ];
    }

    /**
     * Convierte una oportunidad publicada en una tarjeta pública.
     *
     * @return array<string, mixed>
     */
    public static function desdeOportunidad(Oportunidad $oportunidad): array
    {
        $categoria = in_array($oportunidad->tipo, array_keys(self::CATEGORIAS), true)
            ? $oportunidad->tipo
            : 'otra';

        return [
            'key' => 'oportunidad:'.$oportunidad->id,
            'tipo_publicacion' => 'oportunidad',
            'id' => $oportunidad->id,
            'categoria' => $categoria,
            'categoria_label' => __('opportunity_type.'.$categoria),
            'tipo_trabajo' => null,
            'titulo' => $oportunidad->titulo,
            'descripcion' => Str::limit(strip_tags($oportunidad->descripcion), 150),
            'institucion' => $oportunidad->institucion,
            'lugar' => $oportunidad->lugar,
            'salario' => null,
            'imagen' => $oportunidad->imagen ? asset($oportunidad->imagen) : null,
            'publicado_en' => optional($oportunidad->created_at)->toIso8601String(),
            'publicado_legible' => optional($oportunidad->created_at)->format('d/m/Y H:i'),
            'publicado_relativo' => optional($oportunidad->created_at)
                ?->locale(app()->getLocale())
                ->diffForHumans(),
            // En las oportunidades la fecha registrada es la fecha del evento o del cierre.
            'fecha_limite' => optional($oportunidad->fecha)->toDateString(),
            'fecha_limite_legible' => optional($oportunidad->fecha)->format('d/m/Y'),
            'estado' => $oportunidad->estado,
            'estado_label' => __('status.'.$oportunidad->estado),
            'url' => route('oportunidades.show', $oportunidad),
            'orden' => optional($oportunidad->created_at)->getTimestamp() ?? 0,
        ];
    }

    /**
     * ¿La oferta es visible públicamente en este momento?
     */
    public static function ofertaEsPublica(?Oferta $oferta): bool
    {
        return $oferta !== null && $oferta->estado === Oferta::ESTADO_APROBADO;
    }

    /**
     * ¿La oportunidad es visible públicamente en este momento?
     */
    public static function oportunidadEsPublica(?Oportunidad $oportunidad): bool
    {
        return $oportunidad !== null && $oportunidad->estado === Oportunidad::ESTADO_PUBLICADO;
    }
}
