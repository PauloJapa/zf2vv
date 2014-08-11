<?php

namespace Livraria\Service;

/**
 * Description of Mssql
 *
 * @author Paulo Cordeiro Watakabe
 */

class Mssql {
    protected $host     = 'bancoiis.vilavelha.intranet';
    protected $schema   = 'incendio';
    protected $user     = 'paulo';
    protected $password = '159357';
    protected $pdo ;
    protected $dsn ;
    protected $stmt ;
    protected $err;
                                                                
    public function __construct(array $option = array()){
        if(!empty($option)){
            if(isset($option['host'    ]))$this->host     = $option['host'    ];      
            if(isset($option['schema'  ]))$this->schema   = $option['schema'  ];      
            if(isset($option['user'    ]))$this->user     = $option['user'    ];      
            if(isset($option['password']))$this->password = $option['password'];      
        }
        //$banco = new PDO(‘dblib:host=servidorondeestaobanco;dbname=banco, ‘login’,'senha’);
        $this->dsn = 'dblib:host=' . $this->host . ';dbname=' . $this->schema;
        $this->conectar();
    }
    
    public function conectar(){
        try {
            $this->pdo = new \PDO($this->dsn, $this->user, $this->password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $this->err = $e->getMessage();
        }
    }
    
    public function desconectar(){
        $this->pdo = null;
    }
    
    public function getErr(){
        if($this->err){
            return $this->err;
        }
        return FALSE;
    }
    
    public function q($query){
        try {
            return $this->pdo->query($query);
        }catch (PDOException $e) {
            $this->err = $e->getMessage();
        }
        return false;
    } 
    
    public function ex($q){
        try {
            return $this->pdo->exec($q);
        }catch (PDOException $e) {
            $this->err = $e->getMessage();
        }
        return false;
    }
       
    public function p($sql){
        $resul = false;
        try {
            $this->stmt = $this->pdo->prepare($sql);
            return true;
        }catch (PDOException $e) {
            $this->err = $e->getMessage();
        }
        return $resul;
    }
       
    public function b($key, $value, $tp='STR'){
        try {
            if('STR' == $tp)
                $this->stmt->bindParam($key, $value, \PDO::PARAM_STR);
            else
                $this->stmt->bindParam($key, $value, \PDO::PARAM_INT);
            return true;
        }catch (PDOException $e) {
            $this->err = $e->getMessage();
        }
        return false;
    }
       
    public function e(array $array = array()){
        try {
            if(!empty($array)){
                return $this->stmt->execute($array);
            }
            return $this->stmt->execute();
        }catch (PDOException $e) {
            $this->err = $e->getMessage();
        }
        return false;
    }
    
    public function fAll($param=''){
        switch ($param){
            case '':
                return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
                break;
            
            case 'FETCH_COLUMN':
                return $this->stmt->fetchAll(\PDO::FETCH_COLUMN);
                break;
            
            case 'FETCH_NUM':
                return $this->stmt->fetchAll(\PDO::FETCH_NUM);
                break;
        }
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function bg(){
        $this->pdo->beginTransaction();
    }
    
    public function co(){
        $this->pdo->commit();
    }
    
    public function rb(){
        $this->pdo->rollBack();
    }
    /**
     * Retorna o ID do ultimo insert
     * @return int 
     */
    public function lastId(){
        return $this->pdo->lastInsertId();
    }

    /** 
     * Converte a variavel do tipo float para string para exibição
     * @param String $get com nome do metodo a ser convertido
     * @param Int $dec quantidade de casas decimais
     * @return String do numero no formato brasileiro padrão com 2 casas decimais
     */
    public function floatToStr($get,$dec = 2){
        if($get == ""){
            return "vazio!!";
        }
        $getter  = 'get' . ucwords($get);
        if(!method_exists($this,$getter)){
            return "Erro no metodo!!";
        }
        $float = call_user_func(array($this,$getter));
        return number_format($float, $dec, ',','.');
    }

    /** 
     * Faz tratamento na variavel string se necessario antes de converte em float
     * @param String $check variavel a ser convertida se tratada se necessario
     * @return String $check no formato float para gravação pelo doctrine
     */
    public function strToFloat($check){
        if(is_string($check)){
            $check = preg_replace("/[^0-9,]/", "", $check);
            return str_replace(",", ".", $check);
        }
        return $check;
    }
    
    public function strToDate($date){
        $date = explode("/", $date);
        return $date[2] . '/' . $date[0] . '/' . $date[1];
        //$this->data[$index]    = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
    }
           
    
}
