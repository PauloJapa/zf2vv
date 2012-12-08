<?php


namespace LivrariaAdmin\Form;

use Zend\Form\Form;

class Bairro extends Form {
    
     public function __construct($name = null) {
        parent::__construct('bairro');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new BairroFilter);
        
        $this->add(array(
           'name' =>'id',
            'attibutes' => array(
                'type' => 'hidden'
            )
        ));
        
        $this->add(array(
           'name' => 'nome',
            'options' => array(
                'type' => 'text',
                'label' => 'Nome'
            ),
            'attributes' => array(
                'id' => 'nome',
                'placeholder' => 'Entre com o nome do bairro'
            )
        ));
        
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
