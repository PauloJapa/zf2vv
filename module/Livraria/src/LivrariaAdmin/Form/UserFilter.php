<?php

namespace LivrariaAdmin\Form;

class UserFilter extends EnderecoFilter {

    public function __construct() {
        
        // Especie do bug no zf2 que força a validação dos campos selects
        $this->add( array(
            'name' => 'estado',
            'required' => false,
        ) );
        
        $this->add( array(
            'name' => 'pais',
            'required' => false,
        ) );
        
        // herdar as validações de endereços 
        // não é necessario validar enderecos da seguradora
        // parent::__construct();
        
        $this->notEmpty('nome');
        $this->notEmpty('email');
        $this->notEmpty('tipo');
        
    }

}
