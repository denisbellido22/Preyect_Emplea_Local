<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Registra fallos de envío de correo sin exponer credenciales SMTP ni
 * direcciones de correo en los registros de la aplicación.
 */
class MailFailureReporter
{
    /**
     * Guarda un registro saneado del error de correo.
     */
    public static function report(Throwable $e, string $contexto): void
    {
        $detalle = $e->getMessage();

        // Oculta el usuario y la contraseña SMTP si el servidor los menciona.
        foreach ([config('mail.mailers.smtp.username'), config('mail.mailers.smtp.password')] as $secreto) {
            if (is_string($secreto) && $secreto !== '') {
                $detalle = str_replace($secreto, '[oculto]', $detalle);
            }
        }

        // Oculta también cualquier dirección de correo que aparezca en el error.
        $detalle = preg_replace(
            '/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/',
            '[correo oculto]',
            (string) $detalle
        );

        Log::error($contexto, [
            'excepcion' => $e::class,
            'detalle' => Str::limit($detalle, 300),
        ]);
    }
}
