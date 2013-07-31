<?php

namespace LivrariaAdmin\Form;

class AtividadeFilter extends AbstractFilter {

    public function __construct() {
        
        $this->notEmpty('descricao');
        
        $this->notEmpty('seguradoraId');
        
    }    
    
}
