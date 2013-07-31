<?php

namespace LivrariaAdmin\Form;

class Importar extends AbstractForm {
    
    public function __construct($name = 'taxa') {
        parent::__construct($name);

        $this->setAttribute('method', 'post');

        $this->setInputHidden('subOpcao');
        $this->setInputHidden('id');

        $this->setInputSubmit('enviar', 'Salvar');

        $file = new \Zend\Form\Element\File('content');
        $file->setLabel('Selecione um arquivo')
             ->setAttribute('id', 'content');
        $this->add($file);
        
        $this->setInputSubmit('importar', 'Importar CSV', ['onClick'=>'importarFile();return false;']);
    }

}
