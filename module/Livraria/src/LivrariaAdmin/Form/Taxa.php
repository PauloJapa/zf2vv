<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class Taxa extends AbstractForm {
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
        parent::__construct('taxa');
        $this->em = $em;

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new TaxaFilter);    

        $this->setInputHidden('subOpcao');
        $this->setInputHidden('id');

        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', '*Inicio da Vigência', $attributes);
        
        $this->setInputText('fim', '*Fim da Vigência', $attributes);
        
        $status = $this->getParametroSelect('status');
        $this->setInputSelect('status', '*Situação', $status);

        $this->setInputText('incendio', 'Cobertura p/ incêndio', ['placeholder' => 'XXX,XX']);

        $this->setInputText('incendioConteudo', 'Cob. incêndio conteúdo', ['placeholder' => 'XXX,XX']);

        $this->setInputText('aluguel', 'Cobertura p/ aluguel', ['placeholder' => 'XXX,XX']);
        
        $this->setInputText('eletrico', 'Cobertura p/ eletrica', ['placeholder' => 'XXX,XX']);
        
        $this->setInputText('vendaval', 'Cobertura p/ Vendaval', ['placeholder' => 'XXX,XX']);

        $this->classes = $this->em->getRepository('Livraria\Entity\Classe')->fetchPairs(['status'=>'A']);
        $this->setInputSelect('classe', '*Classe', $this->classes, ["onChange" => "buscaClasse()"] );
        
        $this->seguradoras = $this->em->getRepository('Livraria\Entity\Seguradora')->fetchPairs(['status'=>'A']);
        $this->setInputSelect('seguradora', '*Seguradora', $this->seguradoras, ["onChange" => "buscaSeguradora()"] );
        
        $validade = $this->getParametroSelect('validade');
        $this->setInputSelect('validade', '*Validade', $validade);
        
        $ocupacao = $this->getParametroSelect('ocupacao');
        $this->setInputSelect('ocupacao', '*Ocupação', $ocupacao);
        
        $comissao = $this->getParametroSelect('comissaoParam');
        $this->setInputSelect('comissao', '*Comissão', $comissao);
        
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
        //if(($isAdmin)or($this->isAdmin)){
        //    $this->isAdmin = TRUE;
        //    return ;
        //}
        $this->get('seguradora')->setAttribute('disabled', 'disabled');   
        $this->get('classe')->setAttribute('disabled', 'disabled');   
        $this->get('validade')->setAttribute('disabled', 'disabled');   
        $this->get('ocupacao')->setAttribute('disabled', 'disabled');   
        $this->get('comissao')->setAttribute('disabled', 'disabled');   
        $this->get('inicio')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('incendio')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('incendioConteudo')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('aluguel')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('eletrico')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('vendaval')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));  
    }

}
