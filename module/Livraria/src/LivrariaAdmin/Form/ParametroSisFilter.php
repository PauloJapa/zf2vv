<?php

namespace LivrariaAdmin\Form;

class ParametroSisFilter extends AbstractFilter {

    public function __construct() {
                
        $this->notEmpty('key');
        
        $this->notEmpty('conteudo');
        
    }

}
