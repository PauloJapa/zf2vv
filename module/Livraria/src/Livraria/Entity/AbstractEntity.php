<?php

/*
 * 
 *Contem todas os metodos comuns da entity a serem copiados 
 * 
 */

/**
 * AbstractEntity
 * 
 * Contém os metodos de uso geral de todas as entitys.
 *
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
abstract class AbstractEntity {
    
 
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
            $check = str_replace(",", ".", $check);
        }
        return $check;
    }
}

?>
