<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/models.php';

\Deimos\ORM\Connection::setConfig([
    'default' => [
        'dsn'      => 'mysql:host=localhost;dbname=auto_dev',
        'username' => 'root',
        'password' => ''
    ],
    'sphinx'  => [
        'dsn'      => 'mysql:host=localhost:9306',
        'username' => '',
        'password' => ''
    ],
]);