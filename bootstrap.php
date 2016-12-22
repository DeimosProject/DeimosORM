<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/models.php';

\Deimos\ORM\Connection::setConfig([
    'default' => [
        'dsn'      => 'mysql:host=localhost;dbname=test',
        'username' => 'root',
        'password' => ''
    ]
]);