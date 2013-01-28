<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Atividade extends Form {
    
    protected $classes;    

    public function __construct($name = null, $em = null) {
        parent::__construct('atividade');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new AtividadeFilter);

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'id' => 'id',
            )
        ));
        
        $this->add(array(
            'name' => 'descricao',
            'options' => array(
                'type' => 'text',
                'label' => '*Descrição da atividade'
            ),
            'attributes' => array(
                'id' => 'descricao',
                'placeholder' => 'Digite atividade'
            )
        ));
        
        $this->add(array(
            'name' => 'codSeguradora',
            'options' => array(
                'type' => 'text',
                'label' => 'Referência'
            ),
            'attributes' => array(
                'id' => 'codSeguradora',
                'placeholder' => 'Ref seguradora'
            )
        ));
        
        $this->add(array(
            'name' => 'ocupacao',
            'options' => array(
                'type' => 'text',
                'label' => ''
            ),
            'attributes' => array(
                'id' => 'ocupacao',
                'placeholder' => 'Ref seguradora'
            )
        ));

        $status = new Select();
        $status->setLabel("*Ocupação")
                ->setName("ocupacao")
                ->setAttribute('id' , 'ocupacao')
                ->setOptions(array('value_options' => array('comercial'=>'Comércio e Serviços',
                                                            'residencial'=>'Residencial',
                                                            'industria'=>'Industria'))
        );
        $this->add($status);

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
