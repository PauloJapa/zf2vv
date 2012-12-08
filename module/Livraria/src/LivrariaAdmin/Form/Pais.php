<?php


namespace LivrariaAdmin\Form;

use Zend\Form\Form;

class Pais extends Form {
    
     public function __construct($name = null) {
        parent::__construct('pais');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new PaisFilter);
        
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
                'placeholder' => 'Entre com o nome do Pais'
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
                'placeholder' => 'Entre com a silga do Pais'
            )
        ));
        
        $this->add(array(
           'name' => 'codigo',
            'options' => array(
                'type' => 'text',
                'label' => 'Codigo'
            ),
            'attributes' => array(
                'id' => 'codigo',
                'placeholder' => 'Codigo Pais'
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
