<?php

namespace LivrariaAdmin\Form;

class TaxaFilter extends AbstractFilter {

    public function __construct() {
        
        $this->notEmpty('inicio');
        $this->notEmpty('status');
        $this->notEmpty('incendio');
        $this->notEmpty('aluguel');
        $this->notEmpty('classe');
        $this->notEmpty('seguradora');
        $this->notEmpty('validade');
        $this->notEmpty('ocupacao');
        $this->notEmpty('comissao');
        
    }    
    
}
