<?php

namespace LivrariaAdmin\Form;

class Atividade extends AbstractForm {
    
    protected $seguradoras;    

    public function __construct($name = null, $em = null) {
        $this->em = $em;
        parent::__construct('atividade');
        
        $this->setAttribute('method', 'post');
        $this->setInputFilter(new AtividadeFilter);

        $this->setInputHidden('id');
        $this->setInputHidden('codSeguradora');
        $this->setInputHidden('danosEletricos');
        $this->setInputHidden('equipEletro');
        $this->setInputHidden('vendavalFumaca');
        $this->setInputHidden('basica');
        $this->setInputHidden('roubo');
        
        $this->setInputText('descricao', '*Descrição da atividade', ['placeholder' => 'Digite atividade']);

        $ocupacao = $this->getParametroSelect('ocupacao');
        $this->setInputSelect('ocupacao', '*Ocupação', $ocupacao);
        
        $status = $this->getParametroSelect('status');
        $this->setInputSelect('status', 'Situação', $status);
        
        $this->setInputText('danosEletricos', 'Danos Eletricos');
        $this->setInputText('equipEletro', 'Equipamento Eletrico');
        $this->setInputText('vendavalFumaca', 'Vendaval Fumaça');
        $this->setInputText('basica', 'Basica');
        $this->setInputText('roubo', 'Roubo');
        
        $this->seguradoras = $em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();
        $this->setInputSelect('seguradoraId', '*Seguradora', $this->seguradoras);

        $this->setInputSubmit('enviar', 'Salvar');

        $file = new \Zend\Form\Element\File('content');
        $file->setLabel('Selecione um arquivo')
             ->setAttribute('id', 'content');
        $this->add($file);
        
        $this->setInputSubmit('importar', 'Importar CSV', ['onClick'=>'importarFile();return false;']);
    }
}
