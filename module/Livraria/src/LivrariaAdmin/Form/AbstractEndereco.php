<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

abstract class AbstractEndereco extends Form {

    /**
     *
     * @var EntityManager
     */
    protected $em;    
    protected $estados;
    protected $paises;
    
    public function __construct($name = null) {
        parent::__construct($name);
    }
    
    public function getEnderecoElements($em)    {
        $this->estados = $em->getRepository('Livraria\Entity\Estado')->fetchPairs();
        $this->paises  = $em->getRepository('Livraria\Entity\Pais')->fetchPairs();
        
        $this->add(array(
            'name'      => 'idEnde',
            'attributes' => array(
                'id'    => 'idEnde',
                'type'  => 'hidden'
            )
        ));
        
        $this->add(array(
            'name'      => 'rua',
            'options'   => array(
                'type'  => 'text',
                'label' => '*Rua',
            ),
            'attributes'      => array(
                'id'          => 'rua',
                'placeholder' => 'Endereço',
                'class'       => 'input-xxlarge'
            )
        ));

        $this->add(array(
            'name' => 'numero',
            'options' => array(
                'type' => 'text',
                'label' => '*Numero'
            ),
            'attributes' => array(
                'id' => 'numero',
                'placeholder' => '',
                'class'       => 'input-mini'
            )
        ));

        $this->add(array(
            'name' => 'compl',
            'options' => array(
                'type' => 'text',
                'label' => 'Complemento'
            ),
            'attributes' => array(
                'id' => 'compl',
                'placeholder' => ''
            )
        ));

        $this->add(array(
            'name' => 'cep',
            'options' => array(
                'type' => 'text',
                'label' => '*CEP'
            ),
            'attributes' => array(
                'id' => 'cep',
                'placeholder' => '',
                'onKeyPress' => 'return submitenter(this,event)'
            )
        ));
       

        $this->add(array(
            'name' => 'bairroDesc',
            'options' => array(
                'type' => 'text',
                'label' => '*Bairro'
            ),
            'attributes' => array(
                'id' => 'bairroDesc',
                'placeholder' => 'Pesquise digitando o bairro aqui!',
                'onKeyUp' => 'autoCompBairro();'
            )
        ));
        
        $this->add(array(
            'name'      => 'bairro',
            'attributes' => array(
                'id'    => 'bairro',
                'type'  => 'hidden'
            )
        ));

        $this->add(array(
            'name' => 'cidadeDesc',
            'options' => array(
                'type' => 'text',
                'label' => '*Cidade'
            ),
            'attributes' => array(
                'id' => 'cidadeDesc',
                'placeholder' => 'Pesquise digitando a Cidade aqui!',
                'onKeyUp' => 'autoCompCidade();'
            )
        ));
        
        $this->add(array(
            'name'      => 'cidade',
            'attributes' => array(
                'id'    => 'cidade',
                'type'  => 'hidden'
            )
        ));
        
        $estado = new Select();
        $estado->setLabel("*Estado")
                ->setName("estado")
                ->setAttribute("id","estado")
                ->setOptions(array('value_options' => $this->estados)
        );
        $this->add($estado);

        
        $pais = new Select();
        $pais->setLabel("*País")
                ->setName("pais")
                ->setAttribute("id","pais")
                ->setOptions(array('value_options' => $this->paises)
        );
        $this->add($pais);
        
    }

}
