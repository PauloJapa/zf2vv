<?php

namespace LivrariaAdmin\Form;

class MultiplosMinimosFilter extends AbstractFilter {

    public function __construct() {
                
        $this->notEmpty('seguradora');
        
        $this->notEmpty('minAluguel');
        
        $this->notEmpty('minIncendio');
        
        $this->notEmpty('multStatus');
        
        $this->notEmpty('multVigenciaInicio');
    }

}
