<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form;

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
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'estado',
            'attributes' => array(
                'id' => 'estado'
            ),
            'options' => array(
                'label' => '*Estado',
                'empty_option' => 'Escolha o estado!',
                'value_options' => $this->estados
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'pais',
            'attributes' => array(
                'id' => 'pais'
            ),
            'options' => array(
                'label' => '*País',
                'empty_option' => 'Escolha o pais!',
                'value_options' => $this->paises
            )
        ));
        
    }

}
