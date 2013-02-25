<?php

namespace Livraria\Entity;

/**
 * Filtro
 * 
 * Filtro basico para tratamento de dados dos setters e getters
 * 
 */
class  Filtro
{
    
    /**
     * Converte objto data para exibiçao caso data for em branco
     * Retorna vigente ou branco
     * @param \DateTime $objDate
     * @return string
     */
    public function trataData(\DateTime $objDate,$op="vigente")
    {
        $check = $objDate->format('d/m/Y');
        if($check == '01/01/1000'){
            return $op;
        }else{
            return $check;
        }        
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
    public function trataFloat($valor,$dec=2){
        if(is_float($valor)){
            return number_format($valor, $dec, ',','.');
        }
        if(is_string($valor)){
            $valor = str_replace(",", ".", preg_replace("/[^0-9,]/", "", $valor));
            return floatval($valor);
        }
        return FALSE;
    }
 
    /** 
     * Faz tratamento na variavel string se necessario antes de converte em float
     * @param String $check variavel a ser convertida se tratada se necessario
     * @return String $check no formato float para gravação pelo doctrine
     */    
    public function trataInt($valor){
        if(is_string($valor)){
            $valor = preg_replace("/[^0-9]/", "", $valor);
            return intval($valor);
        }
        if(is_int($valor)){
            return $valor;
        }
        return FALSE;
    }
}

