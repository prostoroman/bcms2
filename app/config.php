<?php

//DB
//ORM::configure('mysql:host=localhost;dbname=bcms');
//ORM::configure('username', 'root');
//ORM::configure('password', '');
//ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
ORM::configure('sqlite:../app/data/bmcs.sqlite');
ORM::configure('logging', true);
ORM::configure('caching', true);

$bcms = new Pimple();

$bcms['slim.config'] = array(
    'mode' => 'development', // 'mode' => 'production',
    'debug' => 'true', // 'mode' => 'production',
    'templates.path' => '../app/view',
    'log.level' => 4,
    'log.enabled' => true,
    'log.writer' => new \Slim\Extras\Log\DateTimeFileWriter(array(
        'path' => '../app/logs',
        'name_format' => 'y-m-d'
    ))
);

$bcms['twig.config'] = array(
    'templates.path' => '../app/view',
    //'cache' => '../app/view/cache',
    'log.level' => 4,
    'log.enabled' => true,
    'log.writer' => new \Slim\Extras\Log\DateTimeFileWriter(array(
        'path' => '../app/logs',
        'name_format' => 'y-m-d'
    ))
);

$bcms['sessioncookie.config'] = array(
    'expires' => '20 days',
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'httponly' => false,
    'name' => 'slim_session',
    'secret' => 'CHANGE_ME',
    'cipher' => MCRYPT_RIJNDAEL_256,
    'cipher_mode' => MCRYPT_MODE_CBC);
