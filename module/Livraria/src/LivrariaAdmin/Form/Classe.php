<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Classe extends Form {
    
    protected $seguradoras;    

    public function __construct($name = null, $em = null) {
        parent::__construct('classe');
        
        $this->seguradoras = $em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ClasseFilter);
              

        $this->add(array(
            'name' => 'id',
            'attibutes' => array(
                'type' => 'hidden'
            )
        ));
        $this->add(array(
            'name' => 'cod',
            'options' => array(
                'type' => 'text',
                'label' => 'Codigo'
            ),
            'attributes' => array(
                'id' => 'cod',
                'placeholder' => 'Entre com codigo da classe'
            )
        ));

        $this->add(array(
            'name' => 'descricao',
            'options' => array(
                'type' => 'text',
                'label' => 'Descrição'
            ),
            'attributes' => array(
                'id' => 'descricao',
                'placeholder' => 'Descricao da Classe'
            )
        ));

        $seguradora = new Select();
        $seguradora->setLabel("*Seguradora")
                ->setName("seguradora")
                ->setAttribute("id","seguradora")
                ->setOptions(array('value_options' => $this->seguradoras)
        );
        $this->add($seguradora);
     
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
