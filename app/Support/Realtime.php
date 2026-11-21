<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Emite eventos WebSocket sin comprometer la petición HTTP.
 *
 * Si el servidor Reverb no está disponible, la publicación se guarda igual y
 * sólo se registra un aviso: el sitio sigue funcionando sin tiempo real.
 */
class Realtime
{
    /**
     * @param  object  $evento  Evento que implementa ShouldBroadcast
     */
    public static function emitir(object $evento): void
    {
        // En pruebas o con el driver "log"/"null" no hay WebSocket que alimentar.
        if (in_array(config('broadcasting.default'), ['null', 'log'], true)) {
            return;
        }

        try {
            event($evento);
        } catch (Throwable $e) {
            Log::warning('No se pudo emitir el evento en tiempo real.', [
                'evento' => $evento::class,
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}
