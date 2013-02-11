<?php

namespace LivrariaAdmin\Form;

/**
 * ClasseAtividadeFilter
 * Campos Obrigatorios e validações nos dados de entrada do form 
 */
class ClasseAtividadeFilter extends AbstractFilter {

    public function __construct() {
        
        $this->notEmpty('inicio');
        
        $this->notEmpty('status');
        
        $this->notEmpty('classeTaxas');
        
        $this->notEmpty('atividade');
        
        $this->notEmpty('atividadeDesc');

    }    
    
}
