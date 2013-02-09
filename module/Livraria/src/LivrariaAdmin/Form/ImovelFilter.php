<?php

namespace LivrariaAdmin\Form;
/**
 * ImovelFilter
 * Campos obrigatorios e validar a entrada de dados
 * 
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ImovelFilter extends EnderecoFilter {

    public function __construct() {
        
        // herdar as validações de endereços 
        parent::__construct();
        
        $this->notEmpty('locadorDesc');
        
        $this->notEmpty('status');
        
    }

}
