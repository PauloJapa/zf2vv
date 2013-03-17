<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Renovacao extends AbstractForm {

    public function __construct($name = null, $em = null) {
        parent::__construct('renovacao');
        $this->em = $em;

        $this->setAttribute('method', 'post');
        //$this->setInputFilter(new RenovacaoFilter);  
        
        $this->setInputHidden('administradora');
        $attributes = ['placeholder' => 'Pesquise digitando a Administradora aqui!',
                       'onKeyUp' => 'autoCompAdministradora();',
                       'class' => 'input-xmlarge',
                       'autoComplete'=>'off'];        
        $this->setInputText('administradoraDesc', 'Pertence a administradora', $attributes); 

        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', '*Periodo inicio', $attributes);
        
        $this->setInputText('fim', '*Periodo fim', $attributes);
        
        $this->setInputSubmit('enviar', 'Buscar',['onClick' => 'return buscar()']);
    }
    
}