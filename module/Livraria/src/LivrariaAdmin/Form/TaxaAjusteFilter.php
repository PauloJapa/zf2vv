<?php

namespace LivrariaAdmin\Form;

class TaxaAjusteFilter extends AbstractFilter {

    public function __construct() {
        
        $this->notEmpty('inicio');
        $this->notEmpty('status');
        $this->notEmpty('seguradora');
        $this->notEmpty('validade');
        $this->notEmpty('ocupacao');
        
    }    
    
}
