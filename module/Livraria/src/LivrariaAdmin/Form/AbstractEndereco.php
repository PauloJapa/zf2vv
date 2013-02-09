<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form;

abstract class AbstractEndereco extends AbstractForm {

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
        
        $this->setInputHidden('idEnde');

        $this->setInputHidden('ajaxStatus');

        $this->setInputText('rua', 'Rua', ['placeholder' => 'Endereço','class' => 'input-xmlarge']);

        $this->setInputText('numero', 'Numero', ['class' => 'input-mini']);

        $this->setInputText('compl', 'Complemento');

        $this->setInputText('cep', '*CEP', ['onKeyPress' => 'return submitenter(this,event)']);

        $this->setInputText('bairroDesc', 'Bairro', ['placeholder' => 'Pesquise digitando o bairro aqui!','onKeyUp' => 'autoCompBairro();']);

        $this->setInputHidden('bairro');

        $this->setInputText('cidadeDesc', 'Cidade', ['placeholder' => 'Pesquise digitando a Cidade aqui!','onKeyUp' => 'autoCompCidade();']);

        $this->setInputHidden('cidade');

        $this->setInputSelect('estado', 'Estado', $this->estados);

        $this->setInputSelect('pais', 'País', $this->paises);
        
    }

}
