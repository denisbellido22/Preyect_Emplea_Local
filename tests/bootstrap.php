<?php

/*
|--------------------------------------------------------------------------
| Entorno seguro de pruebas
|--------------------------------------------------------------------------
| Docker (u otro entorno) puede exportar APP_ENV, DB_CONNECTION y DB_DATABASE
| apuntando a la base SQLite real. Estas variables se fijan ANTES de cargar
| Composer y de crear la aplicación Laravel para que la suite nunca ejecute
| RefreshDatabase sobre database/database.sqlite.
*/

$variablesSeguras = [
    'APP_ENV' => 'testing',
    'APP_MAINTENANCE_DRIVER' => 'file',
    'BROADCAST_CONNECTION' => 'null',
    'CACHE_STORE' => 'array',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'DEMO_ACCOUNTS_ENABLED' => 'false',
    'BCRYPT_ROUNDS' => '4',
    'MAIL_MAILER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'array',
];

foreach ($variablesSeguras as $clave => $valor) {
    putenv("{$clave}={$valor}");
    $_ENV[$clave] = $valor;
    $_SERVER[$clave] = $valor;
}

require __DIR__.'/../vendor/autoload.php';
