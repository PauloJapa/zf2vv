<?php 
/* 
* Função de busca de Endereço pelo CEP 
* - Desenvolvido Felipe Olivaes para ajaxbox.com.br 
* - Utilizando WebService de CEP da republicavirtual.com.br 
* - Alterado por Eric Rodrigo de Freitas em 22/11/2007 
*/ 

class busca_cep{ 
    function busca_cep($cep){ 
        $resultado = @file_get_contents('http://republicavirtual.com.br/web_cep.php?cep='.urlencode($cep).'&formato=json'); 
        if(!$resultado){ 
            $resultado = "&resultado=0&resultado_txt=erro+ao+buscar+cep"; 
        } 
        parse_str($resultado, $_retorno);

        /*
        * - Cria XMl
        */ 
        header("Content-Type: application/xml");
        $_xml = '<?xml version="1.0" encoding="ISO-8859-1" ?>'."\r\n"; 


        $_xml.= "\t"."<cep>\r\n";
        switch($_retorno['resultado']){ 
        case '2':

        $_xml.= "\t\t".'<cor_msg>green</cor_msg>'."\r\n"; //-> Define Cor da mensagem
        $_xml.= "\t\t".'<msg>Cidade com logradouro único</msg>'."\r\n";
        $_xml.= "\t\t".'<cidade>'.$_retorno['cidade'].'</cidade>'."\r\n";
        $_xml.= "\t\t".'<uf>'.$_retorno['uf'].'</uf>'."\r\n";

        break; 

        case '1':

        $_xml.= "\t\t".'<cor_msg>green</cor_msg>'."\r\n"; //-> Define Cor da mensagem
        $_xml.= "\t\t".'<msg>Cidade com logradouro completo</msg>'."\r\n";
        $_xml.= "\t\t".'<cidade>'.$_retorno['cidade'].'</cidade>'."\r\n";
        $_xml.= "\t\t".'<uf>'.$_retorno['uf'].'</uf>'."\r\n"; 
        $_xml.= "\t\t".'<tipo_logradouro>'.$_retorno['tipo_logradouro'].'</tipo_logradouro>'."\r\n";
        $_xml.= "\t\t".'<logradouro>'.$_retorno['logradouro'].'</logradouro>'."\r\n";
        $_xml.= "\t\t".'<bairro>'.$_retorno['bairro'].'</bairro>'."\r\n";

        break; 

        default:

        $_xml_.="\t\t".'<cor_msg>red</cor_msg>'."\r\n"; //-> Define Cor da mensagem
        $_xml.= "\t\t".'<msg>Falha ao buscar CEP!</msg>'."\r\n"; 

        break; 
        }
        $_xml.= "\t"."</cep>\r\n";


        echo $_xml;
    }
}

(new busca_cep($_GET['cep']));
