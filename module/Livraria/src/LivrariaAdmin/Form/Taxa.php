<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Taxa extends Form {
    
    protected $classes;    

    public function __construct($name = null, $em = null) {
        parent::__construct('taxa');
        
        $this->classes = $em->getRepository('Livraria\Entity\Classe')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new TaxaFilter);
              

        $this->add(array(
            'name' => 'id',
            'attibutes' => array(
                'type' => 'hidden'
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
                'label' => '*Fim da Vigência'
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

        $this->add(array(
            'name' => 'incendio',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ incêndio'
            ),
            'attributes' => array(
                'id' => 'incendio',
                'placeholder' => 'XXX,XX'
            )
        ));

        $this->add(array(
            'name' => 'incendioConteudo',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ incêndio + conteúdo'
            ),
            'attributes' => array(
                'id' => 'incendioConteudo',
                'placeholder' => 'XXX,XX'
            )
        ));

        $this->add(array(
            'name' => 'aluguel',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ aluguel'
            ),
            'attributes' => array(
                'id' => 'aluguel',
                'placeholder' => 'XXX,XX'
            )
        ));

        $this->add(array(
            'name' => 'eletrico',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ eletrica'
            ),
            'attributes' => array(
                'id' => 'eletrico',
                'placeholder' => 'XXX,XX'
            )
        ));

        $this->add(array(
            'name' => 'desastres',
            'options' => array(
                'type' => 'text',
                'label' => '*Taxa p/ desastres'
            ),
            'attributes' => array(
                'id' => 'desastres',
                'placeholder' => 'XXX,XX'
            )
        ));

        $classe = new Select();
        $classe->setLabel("*Classe")
                ->setName("classe")
                ->setAttribute("id","classe")
                ->setOptions(array('value_options' => $this->classes)
        );
        $this->add($classe);
     
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
