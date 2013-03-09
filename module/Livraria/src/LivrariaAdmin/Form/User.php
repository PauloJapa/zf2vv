<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class User  extends AbstractEndereco {
    
    public function __construct($name = null, $em = null) {
        parent::__construct('user');
        
        $this->setAttribute('method', 'post');
        $this->setAttribute('onSubmit', 'return submitvalida(this)');
        $this->setInputFilter(new UserFilter);
        
        $this->setInputHidden('id');
        
        $this->setInputText('nome','Nome',['class' => 'input-xmlarge','placeholder'=>'Entre com o nome do Usuário']);
        
        $this->add(array(
           'name' => 'email',
            'options' => array(
                'type' => 'email',
                'label' => 'Email'
            ),
            'attributes' => array(
                'id' => 'email',
                'placeholder' => 'Entre com o email'
            )
        ));
        
        
        $tipo = new Select();
        $tipo->setLabel("Tipo de Acesso")
             ->setName("tipo")
              ->setOptions(array('value_options' => array('guest'=>'Visitante','admin'=>'Vila Velha','user'=>'Imobiliaria'))
        );
        $this->add($tipo);
        
        $isAdmin = new Select();
        $isAdmin->setLabel("Administrador")
             ->setName("isAdmin")
              ->setOptions(array('value_options' => array('0'=>'não é Root','1'=>'sim é Root'))
        );
        $this->add($isAdmin);


     
        $this->setInputHidden('administradora');
        $attributes = ['placeholder' => 'Pesquise digitando a Administradora aqui!',
                       'onKeyUp' => 'autoCompAdministradora();',
                       'class' => 'input-xmlarge',
                       'autoComplete'=>'off'];        
        $this->setInputText('administradoraDesc', 'Pertence a administradora', $attributes);

        $this->add(array(
           'name' => 'password',
            'options' => array(
                'type' => 'Password',
                'label' => 'Senha'
            ),
            'attributes' => array(
                'id' => 'password',
                'type' => 'password'
            )
        ));       
        
        $this->add(array(
           'name' => 'password2',
            'options' => array(
                'type' => 'Password',
                'label' => 'Repetir Senha'
            ),
            'attributes' => array(
                'id' => 'password2',
                'type' => 'password'
            )
        ));
        
        $this->getEnderecoElements($em);
        
        $this->setInputSubmit('enviar', 'Salvar');
    } 
    
    /**
     * Atualiza o form para o modo de edição bloqueando campos se necessario
     */
    public function setEdit(){
        $this->isEdit = TRUE;
        //$this->get('seguradora')->setAttribute('disabled', 'disabled');   
        //$this->get('desastres')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
    }
}
