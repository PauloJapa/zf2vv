<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class User  extends AbstractEndereco {
    
    public function __construct($name = null, $em = null) {
        parent::__construct('user');
        
        $this->setAttribute('method', 'post');
        $this->setAttribute('onKeyPress', 'return submitvalida(this,event)');
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
        
        $isAdmin = new Select();
        $isAdmin->setLabel("Administrador")
             ->setName("isAdmin")
              ->setOptions(array('value_options' => array('false'=>'não é admin','true'=>'é sim admin'))
        );
        $this->add($isAdmin);


        $this->add(array(
            'name' => 'administradoraDesc',
            'options' => array(
                'type' => 'text',
                'label' => 'Pertence a administradora'
            ),
            'attributes' => array(
                'id' => 'administradoraDesc',
                'placeholder' => 'Pesquise digitando a administradora aqui!',
                'onKeyUp' => 'autoCompAdminis();'
            )
        ));
        
        $this->add(array(
            'name'      => 'administradora',
            'attributes' => array(
                'id'    => 'administradora',
                'type'  => 'hidden'
            )
        ));        
        
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
