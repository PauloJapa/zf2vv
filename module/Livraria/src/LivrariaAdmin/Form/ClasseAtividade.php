<?php

namespace LivrariaAdmin\Form;

/**
 * ClasseAtividade
 * Fomulario para manipular os dados da entity
 */
class ClasseAtividade extends AbstractForm { 
    
    /**
     * Todos os registros de \Livraria\Entity\ClasseTaxa para o select
     * @var array
     */
    protected $classeTaxas;  
    
    /**
     * Todos os registros de \Livraria\Entity\Seguradora para o select
     * @var array  
     */
    protected $seguradoras;

    public function __construct($name = null, $em = null, $filtro=[]) {
        parent::__construct('classeAtividade');
        $this->em = $em;

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ClasseAtividadeFilter);

        $this->setInputHidden('id');
        $this->setInputHidden('codOld');
        $this->setInputHidden('codciaOld');
        $this->setInputHidden('seq');
        
        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', '*Inicio da Vigência', $attributes);

        $this->setInputText('fim', '*Fim da Vigência', $attributes);

        $status = $this->getParametroSelect('status');
        $this->setInputSelect('status', '*Situação', $status);

        $this->classeTaxas = $this->em->getRepository('Livraria\Entity\Classe')->fetchPairs($filtro);
        $this->setInputSelect('classeTaxas', '*Classe', $this->classeTaxas);

        $this->setInputHidden('atividade');
        $this->setInputText(
                'atividadeDesc', 
                '*Atividade', 
                [
                    'placeholder'  => 'Pesquise digitando a atividade aqui!',
                    'onKeyUp'      => 'autoCompAtividade();',
                    'autoComplete' => 'off'
                ]
        );

        $this->setInputSubmit('enviar', 'Salvar');

        $file = new \Zend\Form\Element\File('content');
        $file->setLabel('Selecione um arquivo')
             ->setAttribute('id', 'content');
        $this->add($file);
        
        $this->setInputSubmit('importar', 'Importar CSV', ['onClick'=>'importarFile();return false;']);
        
    }
    
}
