<?php

namespace LivrariaAdmin\Form;

class OrcamentoFilter extends EnderecoFilter {

    public function __construct() {
        // validar enderecos do seguro
        $this->ValidateEndereco();
        
        $this->emptyTrue('mesNiver');
        
        $this->notEmpty('locadorNome');
        $this->notEmpty('locatarioNome');
        $this->notEmpty('atividadeDesc');
        $this->notEmpty('valorAluguel');
        $this->notEmpty('inicio');
        $this->notEmpty('seguradora');
        $this->notEmpty('administradora');
        $this->notEmpty('rua');
        $this->notEmpty('numero');
    }

}
