<?php

namespace LivrariaAdmin\Form;

class Endereco extends AbstractEndereco {
    
    public function __construct($name = null, $em = null) {
        parent::__construct('endereco');

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new EnderecoFilter);

        $this->getEnderecoElements($em);
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Salvar',
                'class' => 'btn-success'
            )
        ));
    }

}
