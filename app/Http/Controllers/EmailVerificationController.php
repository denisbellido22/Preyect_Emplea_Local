<?php

namespace App\Http\Controllers;

use App\Support\MailFailureReporter;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerException;

/**
 * Verificación de correo electrónico con el mecanismo nativo de Laravel.
 *
 * La pantalla de aviso y el reenvío están siempre disponibles para el usuario
 * autenticado; la confirmación exige un enlace firmado, temporal y ligado al
 * identificador y al correo de la cuenta (middleware "signed").
 */
class EmailVerificationController extends Controller
{
    /** Envíos permitidos por usuario dentro de la ventana de tiempo. */
    public const MAX_ENVIOS = 3;

    /** Duración de la ventana de limitación, en segundos. */
    public const VENTANA_SEGUNDOS = 60;

    /**
     * Pantalla que indica al usuario que debe revisar su correo.
     */
    public function notice(Request $request)
    {
        return view('auth.verify-email', ['user' => $request->user()]);
    }

    /**
     * Confirma el correo sólo si el enlace firmado es válido.
     */
    public function verify(EmailVerificationRequest $request)
    {
        $yaEstabaVerificado = $request->user()->hasVerifiedEmail();

        // Marca el correo como verificado (no hace nada si ya lo estaba).
        $request->fulfill();

        if ($yaEstabaVerificado) {
            return redirect()->route('perfil.show')->with(
                'success',
                __('Tu correo electrónico ya estaba verificado. No necesitas verificarlo de nuevo.')
            );
        }

        return redirect()->route('perfil.show')->with(
            'success',
            __('¡Listo! Tu correo electrónico fue verificado correctamente.')
        );
    }

    /**
     * Reenvía el correo de verificación con un límite de frecuencia.
     */
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('perfil.show')->with(
                'success',
                __('Tu correo electrónico ya está verificado. No es necesario reenviar el mensaje.')
            );
        }

        $clave = 'verificacion-correo:'.$user->id;

        if (RateLimiter::tooManyAttempts($clave, self::MAX_ENVIOS)) {
            $segundos = RateLimiter::availableIn($clave);

            return back()->with(
                'error',
                __('Has solicitado el correo de verificación demasiadas veces. Espera :segundos segundo(s) e inténtalo de nuevo.', ['segundos' => $segundos])
            );
        }

        RateLimiter::hit($clave, self::VENTANA_SEGUNDOS);

        try {
            $user->sendEmailVerificationNotification();
        } catch (MailerException $e) {
            MailFailureReporter::report($e, 'No se pudo enviar el correo de verificación de cuenta.');

            return back()->with(
                'error',
                __('No pudimos enviar el correo de verificación en este momento. Revisa la configuración de correo del sistema o inténtalo de nuevo más tarde.')
            );
        }

        return back()->with(
            'success',
            __('Te enviamos un nuevo enlace de verificación a :email. Revisa tu bandeja de entrada y la carpeta de spam.', ['email' => $user->email])
        );
    }
}
