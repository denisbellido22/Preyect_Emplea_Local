<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Acceso rápido a cuentas de prueba (solo desarrollo)
    |--------------------------------------------------------------------------
    |
    | Muestra en la pantalla de inicio de sesión un listado con las cuentas de
    | prueba autorizadas para demos y pruebas académicas. El listado solo
    | incluye el nombre, el correo y el rol: nunca contraseñas ni hashes, y el
    | usuario debe escribir la contraseña para autenticarse.
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

];
