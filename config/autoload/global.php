<?php

return array(
    'db' => array(
        'driver' => 'Pdo',
        'dsn'    => 'mysql:dbname=zf2vv;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory'
        )
    ),
    'mailVilaVelhaP25' => array(
        'name' => 'smtp.vilavelha.com.br',
        'host' => 'smtp.vilavelha.com.br',
        'connection_class' => 'login',
        'port' => 25,
        'connection_config' => array(
            'username' => 'incendiolocacao@vilavelha.com.br',
            'password' => '111222',
            'from' => 'incendiolocacao@vilavelha.com.br',
            'fromName' => 'Incendio Locacao'
        )
    ),
    'mailWatakabe' => array(
        'name' => 'smtp.gmail.com',
        'host' => 'smtp.gmail.com',
        'connection_class' => 'login',
        'port' => 587,
        'connection_config' => array(
            'username' => 'watakabe05@gmail.com',
            'password' => '35413541',
            'ssl' => 'tls',
            'from' => 'watakabe05@gmail.com',
            'fromName' => 'Google Paulo'
        )
    ),
    'mail' => array(
        'name' => 'smtp.vilavelha.com.br',
        'host' => 'smtp.vilavelha.com.br',
        'connection_class' => 'login',
        'port' => 587,
        'connection_config' => array(
            'username' => 'incendiolocacao',
            'password' => 'il111222',
            'from' => 'incendiolocacao@vilavelha.com.br',
            //'ssl'  => 'tls',
            'fromName' => 'Incendio Locacao'
        )
    ),
    'mailoffice' => array(
        'name' => 'smtp.office365.com',
        'host' => 'smtp.office365.com',
        'connection_class' => 'login',
        'port' => 25,
        'connection_config' => array(
            'username' => 'incendiolocacao@vilavelha.com.br',
            'password' => 'il111222',
            'from' => 'incendiolocacao@vilavelha.com.br',
            'ssl'  => 'tls',
            'fromName' => 'Incendio Locacao'
        )
    )
);
