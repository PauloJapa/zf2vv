<?php


namespace Livraria\Service;

/**
 * Description of Mysql
 * 
 * Extende os mesmo metodos de Mssql porem mudando variaveis e driver de conexão para mysql
 *
 * @author Paulo Cordeiro Watakabe
 */

class Mysql extends Mssql{
    
    public function __construct(array $option = array()){
        $this->host     = 'localhost';
        $this->schema   = 'zf2vv';
        $this->user     = 'root';
//        $this->password = 't3cn0m3d';
        $this->password = 'root01';
        if(!empty($option)){
            if(isset($option['host'    ]))$this->host     = $option['host'    ];      
            if(isset($option['schema'  ]))$this->schema   = $option['schema'  ];      
            if(isset($option['user'    ]))$this->user     = $option['user'    ];      
            if(isset($option['password']))$this->password = $option['password'];      
        }
        $this->dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->schema . ';charset=UTF8';
        $this->conectar();
    }
    
}
