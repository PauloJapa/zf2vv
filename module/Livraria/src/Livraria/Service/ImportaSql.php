<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Zend\Session\Container as SessionContainer;
use Livraria\Service\Mssql;
use Livraria\Service\Mysql;


/*
 * 
 */

/**
 * Description of ImportaSql
 * Ler fechados da adm robotton para atualizar documentos dos locatario com documento invalidos
 *
 * @author Paulo Watakabe
 * 
 */
class ImportaSql  extends AbstractService{
    
    /**
     *
     * @var \Livraria\Service\Mysql
     */
    protected $sel;
    
    
    /**
     * Contruct recebe EntityManager para manipulação de registros
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    
    
    
    public function importaRobotton() {
        echo
        '<html><head>',
        '<meta http-equiv="content-language" content="pt-br" />',
        '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />',
        '</head><body>',
        '';
        echo '<h1>inicio' , date('d/m/Y - h:i') , '</h1>';
        
        
        //IMPORTAÇÃO DIRETA CONEXAO MSSQL
        $con = new Mssql();
        $ok = 0;
        $er = 0;
        $adm = 196;
        // Prepara a query
        $res = $con->p("Select top 500 * from cad_fechados where ue_cod = :adm AND inicio >= :inicio AND inicio <= :fim");
        if(!$res){            
            echo '<h1>error ' . $con->getErr() , '</H1>' ;            die;        
        }
        // Parametriza e executa query
        $con->b(':adm', $adm, 'INT');
        $re = $con->e();
        if(!$re){            
            echo '<h1>error 2' . $con->getErr() , '</H1>' ;            die;        
        }
        // Montar um array com os dados encontrados 
        $dados = $con->fAll('FETCH_NUM');
        if(!$dados or empty($dados)){
            echo '<h1>Sem dados!!' , '</H1>' ;
            echo '</body></html>';
            die;
        }        
        // faz um loop nos registros encontrados
        foreach ($dados as  $key => $value) {
            //Procura se vai se locatario esta na fila a ser inserido !!
            $nome = trim($value[4]);
            $tipo = ($value[5] == 'F') ? 'fisica' : 'juridica' ;
            $doc = $this->validaCPF($value[6],$tipo);
            if($doc === FALSE){
                continue;
            }
            

        }
        
        
        
    }
    
    public function validaDoc($doc, $tipo) {
        if($tipo == 'fisica'){
            return $this->validaCPF($doc);
        }
        return $this->validaCnpj($doc);
    }
    
    
    
    public function validaCnpj($cnpj = null) {
    	// Deixa o CNPJ com apenas números
        $cnpj = str_pad(preg_replace( '/[^0-9]/', '', $cnpj ), 14, '0', STR_PAD_LEFT);
        // O valor original
        $cnpj_original = $cnpj;
        // Captura os primeiros 12 números do CNPJ
        $primeiros_numeros_cnpj = substr( $cnpj, 0, 12 );    
        // Faz o primeiro cálculo
        $primeiro_calculo = $this->multiplica_cnpj( $primeiros_numeros_cnpj );
        // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
        // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
        $primeiro_digito = ( $primeiro_calculo % 11 ) < 2 ? 0 :  11 - ( $primeiro_calculo % 11 );
        // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
        // Agora temos 13 números aqui
        $primeiros_numeros_cnpj .= $primeiro_digito;

        // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
        $segundo_calculo = $this->multiplica_cnpj( $primeiros_numeros_cnpj, 6 );
        $segundo_digito = ( $segundo_calculo % 11 ) < 2 ? 0 :  11 - ( $segundo_calculo % 11 );
        // Concatena o segundo dígito ao CNPJ
        $cnpj = $primeiros_numeros_cnpj . $segundo_digito;
        // Verifica se o CNPJ gerado é idêntico ao enviado
        if ($cnpj === $cnpj_original) {
            return $cnpj_original;
        }
        return FALSE;
    }
    
    	
    /**
     * Multiplicação do CNPJ
     *
     * @param string $cnpj Os digitos do CNPJ
     * @param int $posicoes A posição que vai iniciar a regressão
     * @return int O
     *
     */
    public function multiplica_cnpj($cnpj, $posicao = 5) {
        // Variável para o cálculo
        $calculo = 0;
        // Laço para percorrer os item do cnpj
        for ($i = 0; $i < strlen($cnpj); $i++) {
            // Cálculo mais posição do CNPJ * a posição
            $calculo = $calculo + ( $cnpj[$i] * $posicao );
            // Decrementa a posição a cada volta do laço
            $posicao--;
            // Se a posição for menor que 2, ela se torna 9
            if ($posicao < 2) {
                $posicao = 9;
            }
        }
        // Retorna o cálculo
        return $calculo;
    }

    public function validaCPF($cpf = null) {
        // Verifica se um número foi informado
        if(empty($cpf)) {
            return false;
        }
        // Elimina possivel mascara
        $cpf = ereg_replace('[^0-9]', '', $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        // Verifica se o numero de digitos informados é igual a 11
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se nenhuma das sequências invalidas abaixo
        // foi digitada. Caso afirmativo, retorna falso
        if ($cpf == '00000000000' ||
            $cpf == '11111111111' ||
            $cpf == '22222222222' ||
            $cpf == '33333333333' ||
            $cpf == '44444444444' ||
            $cpf == '55555555555' ||
            $cpf == '66666666666' ||
            $cpf == '77777777777' ||
            $cpf == '88888888888' ||
            $cpf == '99999999999') {
            return false;
         // Calcula os digitos verificadores para verificar se o
         // CPF é válido
         } else {  
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
            return $cpf;
        }
    }
    
    
    
}
