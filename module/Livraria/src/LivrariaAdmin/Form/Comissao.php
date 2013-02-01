<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Comissao extends Form {
    
    protected $administradoras;    

    public function __construct($name = null, $em = null) {
        parent::__construct('comissao');
        
        $this->administradoras = $em->getRepository('Livraria\Entity\Administradora')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ComissaoFilter);

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'id' => 'id',
            )
        ));

        $this->add(array(
            'name' => 'comissao',
            'options' => array(
                'type' => 'text',
                'label' => '*Comissao'
            ),
            'attributes' => array(
                'id' => 'comissao',
                'placeholder' => 'XX,XX'
            )
        ));
        
        $this->add(array(
            'name' => 'inicio',
            'options' => array(
                'type' => 'text',
                'label' => '*Inicio da Vigência'
            ),
            'attributes' => array(
                'id' => 'inicio',
                'placeholder' => 'dd/mm/yyyy',
                'onClick' => "displayCalendar(this,dateFormat,this)"
            )
        ));
        
        $this->add(array(
            'name' => 'fim',
            'options' => array(
                'type' => 'text',
                'label' => 'Fim da Vigência'
            ),
            'attributes' => array(
                'id' => 'fim',
                'placeholder' => 'dd/mm/yyyy',
                'onClick' => "displayCalendar(this,dateFormat,this)"
            )
        ));

        $status = new Select();
        $status->setLabel("*Situação")
                ->setName("status")
                ->setOptions(array('value_options' => array('A'=>'Ativo','B'=>'Bloqueado','C'=>'Cancelado'))
        );
        $this->add($status);

        $administradora = new Select();
        $administradora->setLabel("*Administradora")
                ->setName("administradora")
                ->setAttribute("id","administradora")
                ->setOptions(array('value_options' => $this->administradoras)
        );
        $this->add($administradora);
     
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
