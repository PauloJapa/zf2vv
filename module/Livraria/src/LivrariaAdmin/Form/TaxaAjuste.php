<?php

namespace LivrariaAdmin\Form;


class TaxaAjuste extends AbstractForm {
    /**
     * Registros para preencher o input select
     * @var array 
     */
    protected $classes;  
    /**
     * Registros para preencher o input select
     * @var array 
     */  
    protected $seguradoras;  
    
    /**
     * Objeto para manipular dados do BD
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct($name = null, $em = null) {
        parent::__construct('taxaAjuste');
        $this->em = $em;

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new TaxaAjusteFilter);    

        $this->setInputHidden('subOpcao');
        $this->setInputHidden('id');

        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', '*Inicio da Vigência', $attributes);
        
        $this->setInputText('fim', '*Fim da Vigência', $attributes);
        
        $status = $this->getParametroSelect('status');
        $this->setInputSelect('status', '*Situação', $status);


        $this->classes = $this->em->getRepository('Livraria\Entity\Classe')->fetchPairs(['status'=>'A']);
        $this->setInputSelect('classe', '*Classe', $this->classes);
        
        $this->seguradoras = $this->em->getRepository('Livraria\Entity\Seguradora')->fetchPairs(['status'=>'A']);
        $this->setInputSelect('seguradora', '*Seguradora', $this->seguradoras);
        
        $this->administradoras = $this->em->getRepository('Livraria\Entity\Administradora')->fetchPairs(['status'=>'A']);
        $this->setInputSelect('administradora', '*Administradoras', $this->administradoras );
        
        $validade = $this->getParametroSelect('validade');
        $this->setInputSelect('validade', '*Validade', $validade);
        
        $ocupacao = $this->getParametroSelect('ocupacao');
        $this->setInputSelect('ocupacao', '*Ocupação', $ocupacao);
        
        
        $this->setInputText('contEle'      , 'TaxaAjuste p/ contEle'      , ['placeholder' => 'XXX,XX']);
        $this->setInputText('conteudo'     , 'TaxaAjuste p/ conteudo'     , ['placeholder' => 'XXX,XX']);
        $this->setInputText('eletrico'     , 'TaxaAjuste p/ eletrico'     , ['placeholder' => 'XXX,XX']);
        $this->setInputText('semContEle'   , 'TaxaAjuste p/ semContEle'   , ['placeholder' => 'XXX,XX']);
        $this->setInputText('comEletrico'  , 'TaxaAjuste p/ comEletrico'  , ['placeholder' => 'XXX,XX']);
        $this->setInputText('semEletrico'  , 'TaxaAjuste p/ semEletrico'  , ['placeholder' => 'XXX,XX']);
        $this->setInputText('unica'        , 'TaxaAjuste p/ unica'        , ['placeholder' => 'XXX,XX']);
        $this->setInputText('contEle'      , 'TaxaAjuste p/ contEle'      , ['placeholder' => 'XXX,XX']);
        
        
        $this->setInputSubmit('enviar', 'Salvar');

        $file = new \Zend\Form\Element\File('content');
        $file->setLabel('Selecione um arquivo')
             ->setAttribute('id', 'content');
        $this->add($file);
        
        $this->setInputSubmit('importar', 'Importar CSV', ['onClick'=>'importarFile();return false;']);
    }
    
    /**
     * 
     * Atualiza o form para o modo de edição bloqueando campos se necessario
     * @param boolean $isAdmin Super usuario pode alterar
     * @return void
     */ 
    public function setEdit($isAdmin=false){
        $this->isEdit = TRUE;
        if(($isAdmin)or($this->isAdmin)){
            $this->isAdmin = TRUE;
            return ;
        }
    }
    
}
