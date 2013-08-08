<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form;

class Login extends AbstractForm {
    
    public function __construct($name = null) {
        parent::__construct('user');
        
        $this->setAttribute('method', 'post');
        
        
        $this->setInputText2('email', 'Login/Email', ['placeholder' => 'Digite o Login/Email']);
        
        $this->setInputText2('password','Senha');

       
        $this->add(array(
           'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Entrar no Sistema',
                'class' => 'btn-success'
            )
        ));
    }
}
