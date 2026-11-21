<?php

namespace App\Observers;

use App\Events\PublicacionActualizada;
use App\Events\PublicacionPublicada;
use App\Events\PublicacionRetirada;
use App\Models\Oferta;
use App\Support\PublicacionPublica;
use App\Support\Realtime;

/**
 * Mantiene sincronizada la portada cuando una oferta cambia.
 *
 * Se ejecuta después de guardar: las ofertas pendientes o rechazadas nunca se
 * emiten como publicaciones visibles.
 */
class OfertaObserver
{
    public function created(Oferta $oferta): void
    {
        if (PublicacionPublica::ofertaEsPublica($oferta)) {
            Realtime::emitir(new PublicacionPublicada(PublicacionPublica::desdeOferta($oferta)));
        }
    }

    public function updated(Oferta $oferta): void
    {
        $eraPublica = $oferta->getRawOriginal('estado') === Oferta::ESTADO_APROBADO;
        $esPublica = PublicacionPublica::ofertaEsPublica($oferta);

        if ($esPublica && ! $eraPublica) {
            // Aprobación (o republicación) de una oferta que aún no se veía.
            Realtime::emitir(new PublicacionPublicada(PublicacionPublica::desdeOferta($oferta)));

            return;
        }

        if ($esPublica && $eraPublica) {
            // Edición de una oferta que ya estaba visible.
            Realtime::emitir(new PublicacionActualizada(PublicacionPublica::desdeOferta($oferta)));

            return;
        }

        if (! $esPublica && $eraPublica) {
            // Rechazo, vuelta a pendiente o borrador: se retira de la portada.
            Realtime::emitir(new PublicacionRetirada('oferta:'.$oferta->id, 'empleo'));
        }
    }

    public function deleted(Oferta $oferta): void
    {
        if ($oferta->estado === Oferta::ESTADO_APROBADO) {
            Realtime::emitir(new PublicacionRetirada('oferta:'.$oferta->id, 'empleo'));
        }
    }
}
