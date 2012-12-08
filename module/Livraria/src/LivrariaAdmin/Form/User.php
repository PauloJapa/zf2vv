<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class User extends Form {
    
    public function __construct($name = null) {
        parent::__construct('user');
        
        $this->setAttribute('method', 'post');
        #$this->setInputFilter(new CategoriaFilter);
        
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
                'placeholder' => 'Entre com o nome'
            )
        ));
        
        $this->add(array(
           'name' => 'email',
            'options' => array(
                'type' => 'email',
                'label' => 'Email'
            ),
            'attributes' => array(
                'placeholder' => 'Entre com o email'
            )
        ));
        
        
        $tipo = new Select();
        $tipo->setLabel("Tipo de Acesso")
             ->setName("tipo")
              ->setOptions(array('value_options' => array('guest'=>'guest','admin'=>'admin','user'=>'user'))
        );
        $this->add($tipo);
        
        $this->add(array(
           'name' => 'password',
            'options' => array(
                'type' => 'Password',
                'label' => 'Senha'
            ),
            'attributes' => array(
                'type' => 'password'
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
