<?php

namespace LivrariaAdmin\Form;

class ClasseFilter extends AbstractFilter {

    public function __construct() {
        
        $this->notEmpty('descricao');
        
        $this->notEmpty('seguradora');
        
    }    
    
}
