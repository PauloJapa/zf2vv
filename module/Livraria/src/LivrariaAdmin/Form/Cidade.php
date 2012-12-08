<?php


namespace LivrariaAdmin\Form;

use Zend\Form\Form;

class Cidade extends Form {
    
     public function __construct($name = null) {
        parent::__construct('cidade');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new CidadeFilter);
        
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
                'placeholder' => 'Entre com o nome da cidade'
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
