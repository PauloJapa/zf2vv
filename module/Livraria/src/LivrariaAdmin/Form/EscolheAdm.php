<?php

namespace LivrariaAdmin\Form;

/**
 * EscolhaAdm
 * Fomulario para manipular os dados da entity
 */
class EscolheAdm extends AbstractForm { 
    
    /**
     * Objeto para manipular dados do BD
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    public function __construct($name = null, $em = null, $filtro=[]) {
        parent::__construct('escolhaAdm');
        
        $this->em = $em;

        $this->setAttribute('method', 'post');
        //$this->setInputFilter(new EscolhaAdmFilter);

        $this->setInputHidden('id');
        $this->setInputHidden('subOpcao');
        $this->setInputHidden('ajaxStatus');
        $this->setInputHidden('autoComp');
     
        $this->setInputHidden('administradora');
        $attributes = ['placeholder' => 'Pesquise digitando a Administradora aqui!',
                       'onKeyUp' => 'autoCompAdministradora();',
                       'autoComplete'=>'off'];
        $this->setInputText('administradoraDesc', 'Administradora', $attributes);

        $this->setInputSubmit('enviar', 'Enviar');
        
    }
    
}
