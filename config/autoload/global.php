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
    'mail' => array(
        'name' => 'smtp.vilavelha.com.br',
        'host' => 'smtp.vilavelha.com.br',
        'connection_class' => 'login',
        'port' => 25,
        'connection_config' => array(
            'username' => 'incendiolocacao@vilavelha.com.br',
            'password' => '111222',
            'from' => 'incendiolocacao@vilavelha.com.br'
        )
    )
    /*
     * Usa este somente para testes
    'mail' => array(
        'name' => 'smtp.gmail.com',
        'host' => 'smtp.gmail.com',
        'connection_class' => 'login',
        'port' => 587,
        'connection_config' => array(
            'username' => 'incendiolocacao@gmail.com',
            'password' => '111222v!',
            'ssl' => 'tls',
            'from' => 'incendiolocacao@gmail.com'
        )
    )
     */
);
