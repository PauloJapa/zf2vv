<?php

namespace LivrariaAdmin\Form;

class LocadorFilter extends EnderecoFilter {

    public function __construct() {
        // não é necessario validar enderecos da seguradora
        $this->notValidateEndereco();
        
        $this->notEmpty('nome');

        $this->notEmpty('tipo');

        $this->notEmpty('status');
        
        $this->notEmpty('administradora');
        $this->notEmpty('administradoraDesc');
        
    }

}
