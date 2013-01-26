<?php

namespace LivrariaAdmin\Form;

class SeguradoraFilter extends EnderecoFilter {

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
        
        $this->add(array(
            'name' => 'nome',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'cnpj',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'status',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array('isEmpty' => 'Não pode estar em branco'),
                    ),
                ),
            ),
        ));
        
    }

}
