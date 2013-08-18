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

    /**
     * Coloca a mascara no campo digitado 
     * Ou retorna campo limpo livre da formatação
     * @param string  $campo
     * @param boolean $formatado
     * @return string 
     */
    public function formatarCPF_CNPJ($campo, $formatado = true){
	//retira formato
	$codigoLimpo = ereg_replace("[' '-./ t]",'',$campo);
	// pega o tamanho da string menos os digitos verificadores
	$tamanho = (strlen($codigoLimpo) -2);
	//verifica se o tamanho do código informado é válido
	if ($tamanho != 9 && $tamanho != 12){
		return false; 
	}
 
	if ($formatado){ 
		// seleciona a máscara para cpf ou cnpj
		$mascara = ($tamanho == 9) ? '###.###.###-##' : '##.###.###/####-##'; 
 
		$indice = -1;
		for ($i=0; $i < strlen($mascara); $i++) {
			if ($mascara[$i]=='#') $mascara[$i] = $codigoLimpo[++$indice];
		}
		//retorna o campo formatado
		$retorno = $mascara;
 
	}else{
		//se não quer formatado, retorna o campo limpo
		$retorno = $codigoLimpo;
	}
 
	return $retorno;
 
    }
}

