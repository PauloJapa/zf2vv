<?php

namespace LivrariaAdmin\Form;

class MultiplosMinimosFilter extends AbstractFilter {

    public function __construct() {
                
        $this->notEmpty('seguradora');
        
        $this->notEmpty('multAluguel');
        
        $this->notEmpty('multConteudo');
        
        $this->notEmpty('multStatus');
        
        $this->notEmpty('multVigenciaInicio');
    }

}
