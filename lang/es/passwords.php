<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de restablecimiento de contraseña
    |--------------------------------------------------------------------------
    |
    | Estas líneas corresponden a los mensajes que devuelve el broker de
    | contraseñas de Laravel (Password::sendResetLink / Password::reset).
    |
    */

    'reset' => 'Tu contraseña fue restablecida correctamente. Ya puedes iniciar sesión con la nueva.',
    'sent' => 'Te enviamos un enlace para restablecer tu contraseña al correo registrado.',
    'throttled' => 'Espera un momento antes de volver a solicitar otro enlace.',
    'token' => 'El enlace para restablecer la contraseña no es válido, expiró o ya fue utilizado.',
    'user' => 'No encontramos ninguna cuenta registrada con ese correo electrónico.',

];
