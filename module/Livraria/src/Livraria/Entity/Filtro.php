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
        if($check == '01/01/1000' OR $check == '01/01/0001'){
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
            return "Erro no metodo inexistente!!";
        }
        $float = call_user_func(array($this,$getter));
        if($float == 0.0001)
            return 'Não Calcular';
        if(is_null($float))
            $float = 0.0;
        if($float == 0){
            return '';
        }
        return number_format($float, $dec, ',','.');
    }
 
    /** 
     * Faz tratamento na variavel string se necessario antes de converte em float
     * @param String $check variavel a ser convertida se tratada se necessario
     * @return String $check no formato float para gravação pelo doctrine
     */    
    public function trataFloat($valor,$dec=2){
        if(is_float($valor)){
            return $valor;
            //return number_format($valor, $dec, ',','.');
        }
        if(is_string($valor)){
            $valor = str_replace(",", ".", preg_replace("/[^0-9,-]/", "", $valor));
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

    /**
     * Coloca a mascara no campo digitado 
     * Ou retorna campo limpo livre da formatação
     * @param string  $campo
     * @param boolean $formatado
     * @return string 
     */
    public function formatarCPF_CNPJ($campo, $formatado = true){
        if(empty($campo)){
            return '';
        }
	//retira formatação do codigo
	$codigoLimpo = $this->cleanDocFomatacao($campo);
	if ($formatado){ 
            // seleciona a máscara para cpf ou cnpj
            $mascara = (strlen($codigoLimpo) == 14)  ? '##.###.###/####-##' : '###.###.###-##' ;  
            $indice = -1;
            for ($i=0; $i < strlen($mascara); $i++) {
                    if ($mascara[$i]=='#') $mascara[$i] = $codigoLimpo[++$indice];
            }
            //retorna o campo formatado
            return $mascara; 
	}else{
            //se não quer formatado, retorna o campo limpo
            return $codigoLimpo;
	}
 
    }
    
    /**
     * Retirar tudo que não é numero e coloca zeros a esquerda 
     * para completar 11 digitos para cpf e 14 para cnpf
     * @param string $doc
     * @return string formatada com zeros a esquedar
     */
    public function cleanDocFomatacao($doc) {
        if(empty($doc)){
            return '';
        }
        $clean = preg_replace("/[^0-9]/", "", $doc);
        $tamanho = strlen($clean);
        if($tamanho <= 11){
            return str_pad($clean, 11, '0', STR_PAD_LEFT);  
        }else{
            return str_pad($clean, 14, '0', STR_PAD_LEFT);              
        }        
    }
}

