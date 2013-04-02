<?php

namespace LivrariaAdmin\Form;

class Classe extends AbstractForm {
    
    protected $seguradoras;    

    public function __construct($name = null, $em = null) {
        parent::__construct('classe');
        
        $this->em = $em;

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ClasseFilter);

        $this->setInputHidden('id');
        $this->setInputHidden('cod');
        
        $this->setInputText('descricao', 'Descrição', ['placeholder' => 'Descricao da Classe']);

        $status = $this->getParametroSelect('status');
        $this->setInputSelect('status', '*Situação', $status);
     
        $this->setInputSubmit('enviar', 'Salvar');
    }
}
