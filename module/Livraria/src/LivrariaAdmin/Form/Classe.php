<?php

namespace LivrariaAdmin\Form;

class Classe extends AbstractForm {
    
    protected $seguradoras;    

    public function __construct($name = null, $em = null) {
        parent::__construct('classe');
        
        $this->em = $em;
        $this->seguradoras = $em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ClasseFilter);

        $this->setInputHidden('id');
        $this->setInputHidden('cod');
        
        $this->setInputText('descricao', 'Descrição', ['placeholder' => 'Descricao da Classe']);

        $this->setInputSelect('seguradora', '*Seguradora', $this->seguradoras, ["onChange"=>"buscaSeguradora()"]);
     
        $this->setInputSubmit('enviar', 'Salvar');
    }
}
