<?php

namespace LivrariaAdmin\Form;

class EnderecoFilter extends AbstractFilter {

    public function __construct() {
        
        $this->notEmpty('rua');

        $this->notEmpty('numero');

        $this->notEmpty('bairroDesc');

        $this->notEmpty('cidadeDesc');

        $this->notEmpty('estado');

        $this->notEmpty('pais');
    }
    
    public function notValidateEndereco(){
        // Especie do bug no zf2 que força a validação dos campos selects
        $this->add( array(
            'name' => 'estado',
            'required' => false,
        ) );
        
        $this->add( array(
            'name' => 'pais',
            'required' => false,
        ) );
    }

}
