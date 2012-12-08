<?php


namespace LivrariaAdmin\Form;

use Zend\Form\Form;

class Estado extends Form {
    
     public function __construct($name = null) {
        parent::__construct('estado');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new EstadoFilter);
        
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
                'placeholder' => 'Entre com o nome do Estado'
            )
        ));
        
        $this->add(array(
           'name' => 'sigla',
            'options' => array(
                'type' => 'text',
                'label' => 'Sigla'
            ),
            'attributes' => array(
                'id' => 'sigla',
                'placeholder' => 'Sigla do Estado'
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
