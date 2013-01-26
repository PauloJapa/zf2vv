<?php


namespace LivrariaAdmin\Form;

use Zend\Form\Form;

class Seguradora extends AbstractEndereco {
    

    public function __construct($name = null, $em = null) {
        parent::__construct('seguradora');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new SeguradoraFilter);
        
        $this->add(array(
           'name' =>'id',
            'attibutes' => array(
                'id' => 'id',
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
                'placeholder' => 'Entre com o nome do seguradora'
            )
        ));
        
        $this->add(array(
           'name' => 'apelido',
            'options' => array(
                'type' => 'text',
                'label' => 'Apelido'
            ),
            'attributes' => array(
                'id' => 'apelido',
                'placeholder' => 'Entre com o apelido da seguradora'
            )
        ));

        $this->add(array(
            'name' => 'cnpj',
            'options' => array(
                'type' => 'text',
                'label' => 'CNPJ'
            ),
            'attributes' => array(
                'id' => 'cnpj',
                'placeholder' => ''
            )
        ));

        $this->add(array(
            'name' => 'tel',
            'options' => array(
                'type' => 'text',
                'label' => 'Telefone'
            ),
            'attributes' => array(
                'id' => 'tel',
                'placeholder' => ''
            )
        ));

        $this->add(array(
            'name' => 'email',
            'options' => array(
                'type' => 'text',
                'label' => 'Email'
            ),
            'attributes' => array(
                'id' => 'email',
                'placeholder' => ''
            )
        ));

        $this->add(array(
            'name' => 'site',
            'options' => array(
                'type' => 'text',
                'label' => 'Site'
            ),
            'attributes' => array(
                'id' => 'site',
                'placeholder' => 'Site da empresa'
            )
        ));

        $this->add(array(
            'name' => 'site',
            'options' => array(
                'type' => 'text',
                'label' => 'Site'
            ),
            'attributes' => array(
                'id' => 'site',
                'placeholder' => 'Site da empresa'
            )
        ));
        
        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'status',
                'attributes' => array(
                    'id' => 'status'
                ),
                'options' => array(
                    'label' => 'Situação',
                    'empty_option' => 'Escolha a situação do cadastro!',
                    'value_options' => array(
                        'A'=>'Ativo',
                        'B'=>'Bloqueado',
                        'C'=>'Cancelado',
                ),
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
