<?php

namespace LivrariaAdmin\Form;

class AdministradoraFilter extends EnderecoFilter {

    public function __construct() {
        
        // herdar as validações de endereços
        $this->notValidateEndereco();
        
        $this->emptyTrue('formaPagto');
        $this->emptyTrue('validade');
        $this->emptyTrue('tipoCobertura');
        $this->emptyTrue('tipoCoberturaRes');
        $this->emptyTrue('propPag');

        $this->notEmpty('id');
        $this->notEmpty('nome');
        $this->notEmpty('cnpj');
        $this->notEmpty('status');
        $this->notEmpty('codigoCol');
        
    }

}
