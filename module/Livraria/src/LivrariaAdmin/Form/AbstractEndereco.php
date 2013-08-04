<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form;

abstract class AbstractEndereco extends AbstractForm {
   
    protected $estados;
    protected $paises;
    
    public function __construct($name = null) {
        parent::__construct($name);
    }
    
    public function getEnderecoElements($em)    {
        $this->em = $em;
        $this->estados = $this->em->getRepository('Livraria\Entity\Estado')->fetchPairs();
        $this->paises  = $this->em->getRepository('Livraria\Entity\Pais')->fetchPairs();
        
        $this->setInputHidden('idEnde');

        $this->setInputHidden('ajaxStatus');

        $this->setInputText('rua', 'Rua', ['placeholder' => 'Endereço','class' => 'input-xmlarge']);

        $this->setInputText('numero', 'Numero', ['class' => 'input-mini']);

        $this->setInputText('compl', 'Complemento');

        $this->setInputText('cep', '*CEP');

        $this->setInputText('bairroDesc', 'Bairro', ['placeholder' => 'Pesquise digitando o bairro aqui!','onKeyUp' => 'autoCompBairro();']);

        $this->setInputHidden('bairro');

        $this->setInputText('cidadeDesc', 'Cidade', ['placeholder' => 'Pesquise digitando a Cidade aqui!','onKeyUp' => 'autoCompCidade();']);

        $this->setInputHidden('cidade');

        $this->setInputSelect('estado', 'Estado', $this->estados,['class'=>'input-small']);

        $this->setInputSelect('pais', 'País', $this->paises,['class'=>'input-small']);
        
    }
    /**
     * Função para setar varios inputs com com algo padrão
     * Por padrão o array são os inputs visiveis na tela
     * 
     * @param string $key
     * @param string $attribute
     * @param array  $inputs
     */
    public function addAttributeInputs($key,$attribute,array $inputs=[]){
        if(empty($inputs)){
            $inputs = ['cep','rua','numero','compl','bairroDesc','cidadeDesc','estado','pais'];
        }
        foreach ($inputs as $input) {
            $this->get($input)->setAttribute($key, $attribute);
        }
    }

}
