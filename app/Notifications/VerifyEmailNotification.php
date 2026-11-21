<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Correo de verificación de Emplea Local.
 *
 * Reutiliza la URL firmada y temporal que genera Laravel para la ruta
 * verification.verify (id del usuario + hash de su correo) y la presenta con
 * una plantilla propia en español. No se guardan tokens adicionales: la
 * validez del enlace la comprueba el middleware "signed" de Laravel.
 */
class VerifyEmailNotification extends BaseVerifyEmail
{
    /**
     * Construye el mensaje usando la plantilla HTML y su versión de texto.
     */
    public function toMail($notifiable): MailMessage
    {
        $appName = config('app.name', 'Emplea Local');
        $expiraMinutos = (int) config('auth.verification.expire', 60);

        $datos = [
            'appName' => $appName,
            'nombre' => method_exists($notifiable, 'nombreCompleto')
                ? $notifiable->nombreCompleto()
                : $notifiable->name,
            'actionUrl' => $this->verificationUrl($notifiable),
            'expiraMinutos' => $expiraMinutos,
        ];

        return (new MailMessage)
            ->subject($appName.' · '.__('Verifica tu correo electrónico'))
            ->view('emails.verify-email', $datos)
            ->text('emails.verify-email-text', $datos);
    }
}
