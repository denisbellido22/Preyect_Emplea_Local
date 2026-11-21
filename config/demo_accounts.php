<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Acceso rápido a cuentas de prueba (solo desarrollo)
    |--------------------------------------------------------------------------
    |
    | Muestra en la pantalla de inicio de sesión un listado con las cuentas de
    | prueba autorizadas para demos y pruebas académicas. Incluye el nombre, el
    | correo, el rol y la contraseña de demostración para agilizar el ingreso.
    | Nunca expone contraseñas de cuentas reales ni hashes.
    |
    | Desactivado por defecto: en producción no se muestra nada. Actívalo en el
    | archivo .env local con DEMO_ACCOUNTS_ENABLED=true.
    |
    */

    'enabled' => filter_var(env('DEMO_ACCOUNTS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    /*
    | Solo se muestran las cuentas cuyos correos figuren aquí (cuentas creadas
    | por UserSeeder). Ninguna cuenta de usuario real se expone en esta lista.
    */
    'emails' => [
        'admin@emplealocal.test',
        'empleador@emplealocal.test',
        'postulante@emplealocal.test',
    ],

    /*
    | Además, el correo debe pertenecer a este dominio de pruebas. Es una
    | barrera extra para que ninguna cuenta personal pueda añadirse a la lista
    | por error.
    */
    'allowed_domain' => 'emplealocal.test',

    /*
    | Contraseña compartida por las cuentas creadas en UserSeeder. Solo se
    | muestra en el login cuando `enabled` está activo y fuera de producción.
    */
    'password' => 'password123',

];
