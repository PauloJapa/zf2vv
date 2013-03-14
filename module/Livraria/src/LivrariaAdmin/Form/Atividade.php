<?php

namespace LivrariaAdmin\Form;

class Atividade extends AbstractForm {
    
    protected $classes;    

    public function __construct($name = null, $em = null) {
        $this->em = $em;
        parent::__construct('atividade');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new AtividadeFilter);

        $this->setInputHidden('id');
        $this->setInputHidden('codSeguradora');
        
        $this->setInputText('descricao', '*Descrição da atividade', ['placeholder' => 'Digite atividade']);

        $options = ['01'=>'Comércio e Serviços', '02'=>'Residencial', '03'=>'Industria'];
        $this->setInputSelect('ocupacao', '*Ocupação', $options);

        $this->setInputSubmit('submit', 'Salvar');
    }
}
