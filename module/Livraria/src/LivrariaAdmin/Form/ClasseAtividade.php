<?php

namespace LivrariaAdmin\Form;

/**
 * ClasseAtividade
 * Fomulario para manipular os dados da entity
 */
class ClasseAtividade extends AbstractForm { 
    
    /**
     * Objeto para manipular dados do BD
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    /**
     * Para setar o form corretamente para edição de dados
     * @var bollean 
     */
    protected $isEdit = false;
    
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
        $this->classeTaxas = $this->em->getRepository('Livraria\Entity\Classe')->fetchPairs($filtro);
        $this->seguradoras = $this->em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new ClasseAtividadeFilter);

        $this->setInputHidden('id');
        $this->setInputHidden('subOpcao');
        $this->setInputHidden('ajaxStatus');
        $this->setInputHidden('autoComp');
        
        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', '*Inicio da Vigência', $attributes);

        $this->setInputText('fim', '*Fim da Vigência', $attributes);

        $options = ['A'=>'Ativo','B'=>'Bloqueado','C'=>'Cancelado'];
        $this->setInputSelect('status', '*Situação', $options);

        $this->setInputSelect('classeTaxas', '*Classe', $this->classeTaxas);

        $this->setInputHidden('atividade');

        $this->setInputText('atividadeDesc', '*Atividade', ['placeholder' => 'Pesquise digitando a atividade aqui!','onKeyUp' => 'autoCompAtividade();']);

        $attributes = ['onChange'=>'buscaSeguradora()'];
        $this->setInputSelect('seguradora', 'Seguradora', $this->seguradoras, $attributes);

        $this->setInputSubmit('enviar', 'Salvar');
        
    }
    
    /**
     * Recarrega o select baseado em filtro
     * @param array $filtro
     */
    public function reloadSelectClasse(array $filtro){
        $this->classeTaxas = $this->em->getRepository('Livraria\Entity\Classe')->fetchPairs($filtro);
        
        $this->setInputSelect('classeTaxas', '*Classe', $this->classeTaxas);

        if($this->isEdit)
            $this->setEdit ();
    }

}
