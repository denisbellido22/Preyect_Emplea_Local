<?php

namespace App\Observers;

use App\Events\PublicacionActualizada;
use App\Events\PublicacionPublicada;
use App\Events\PublicacionRetirada;
use App\Models\Oportunidad;
use App\Support\PublicacionPublica;
use App\Support\Realtime;

/**
 * Mantiene sincronizada la portada cuando una beca, convocatoria, examen,
 * práctica o capacitación cambia. Los borradores nunca se transmiten.
 */
class OportunidadObserver
{
    public function created(Oportunidad $oportunidad): void
    {
        if (PublicacionPublica::oportunidadEsPublica($oportunidad)) {
            Realtime::emitir(new PublicacionPublicada(PublicacionPublica::desdeOportunidad($oportunidad)));
        }
    }

    public function updated(Oportunidad $oportunidad): void
    {
        $eraPublica = $oportunidad->getRawOriginal('estado') === Oportunidad::ESTADO_PUBLICADO;
        $esPublica = PublicacionPublica::oportunidadEsPublica($oportunidad);

        if ($esPublica && ! $eraPublica) {
            Realtime::emitir(new PublicacionPublicada(PublicacionPublica::desdeOportunidad($oportunidad)));

            return;
        }

        if ($esPublica && $eraPublica) {
            Realtime::emitir(new PublicacionActualizada(PublicacionPublica::desdeOportunidad($oportunidad)));

            return;
        }

        if (! $esPublica && $eraPublica) {
            Realtime::emitir(new PublicacionRetirada('oportunidad:'.$oportunidad->id, $oportunidad->tipo));
        }
    }

    public function deleted(Oportunidad $oportunidad): void
    {
        if ($oportunidad->estado === Oportunidad::ESTADO_PUBLICADO) {
            $categoria = in_array($oportunidad->tipo, array_keys(PublicacionPublica::CATEGORIAS), true)
                ? $oportunidad->tipo
                : 'otra';

            Realtime::emitir(new PublicacionRetirada('oportunidad:'.$oportunidad->id, $categoria));
        }
    }
}
