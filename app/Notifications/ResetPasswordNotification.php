<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Correo de recuperación de contraseña de Emplea Local.
 *
 * Reutiliza el token y la URL que genera el broker de contraseñas de Laravel
 * y los presenta con una plantilla propia en español. El token sigue siendo
 * temporal, de un solo uso y se guarda hasheado en password_reset_tokens.
 */
class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Construye el mensaje usando la plantilla HTML y su versión de texto.
     */
    public function toMail($notifiable): MailMessage
    {
        $appName = config('app.name', 'Emplea Local');
        $expiraMinutos = (int) config(
            'auth.passwords.'.config('auth.defaults.passwords').'.expire',
            60
        );

        $datos = [
            'appName' => $appName,
            'nombre' => method_exists($notifiable, 'nombreCompleto')
                ? $notifiable->nombreCompleto()
                : $notifiable->name,
            'actionUrl' => $this->resetUrl($notifiable),
            'expiraMinutos' => $expiraMinutos,
        ];

        return (new MailMessage)
            ->subject($appName.' · '.__('Recuperación de contraseña'))
            ->view('emails.reset-password', $datos)
            ->text('emails.reset-password-text', $datos);
    }
}
