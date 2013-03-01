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
        $this->seguradoras = $this->em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new TaxaFilter);    

        $this->setInputHidden('subOpcao');
        $this->setInputHidden('id');

        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)"];
        $this->setInputText('inicio', '*Inicio da Vigência', $attributes);
        
        $this->setInputText('fim', '*Fim da Vigência', $attributes);
        
        $options = ['A'=>'Ativo','B'=>'Bloqueado','C'=>'Cancelado'];
        $this->setInputSelect('status', '*Situação', $options);

        $this->setInputText('incendio', '*Taxa p/ incêndio', ['placeholder' => 'XXX,XX']);

        $this->setInputText('incendioConteudo', '*Taxa p/ conteúdo', ['placeholder' => 'XXX,XX']);

        $this->setInputText('aluguel', '*Taxa p/ aluguel', ['placeholder' => 'XXX,XX']);
        
        $this->setInputText('eletrico', '*Taxa p/ eletrica', ['placeholder' => 'XXX,XX']);
        
        $this->setInputText('desastres', '*Taxa p/ desastres', ['placeholder' => 'XXX,XX']);

        $this->setInputText('incendioMen', '*Taxa p/ incêndio', ['placeholder' => 'XXX,XX']);

        $this->setInputText('incendioConteudoMen', '*Taxa p/ conteúdo', ['placeholder' => 'XXX,XX']);

        $this->setInputText('aluguelMen', '*Taxa p/ aluguel', ['placeholder' => 'XXX,XX']);
        
        $this->setInputText('eletricoMen', '*Taxa p/ eletrica', ['placeholder' => 'XXX,XX']);
        
        $this->setInputText('desastresMen', '*Taxa p/ desastres', ['placeholder' => 'XXX,XX']);
        
        $options = [];
        $this->setInputSelect('classe', '*Classe', $options, ["onChange" => "buscaClasse()"] );
        
        $this->setInputSelect('seguradora', '*Seguradora', $this->seguradoras, ["onChange" => "buscaSeguradora()"] );
        
        $this->setInputSubmit('enviar', 'Salvar');
    }
    
    /**
     * Recarrega o select baseado em filtro
     * @param array $filtro
     */
    public function reloadSelectClasse(array $filtro){
        $this->classes = $this->em->getRepository('Livraria\Entity\Classe')->fetchPairs($filtro);
        $classe = new Select();
        $classe->setLabel("*Classe")
                ->setName("classe")
                ->setAttribute("id","classe")
                ->setAttribute("onChange","buscaClasse()")
                ->setOptions(array('value_options' => $this->classes)
        );
        $this->add($classe);
        if($this->isEdit)
            $this->setEdit ();
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
        $this->get('seguradora')->setAttribute('disabled', 'disabled');   
        $this->get('classe')->setAttribute('disabled', 'disabled');   
        $this->get('inicio')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('incendio')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('incendioConteudo')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('aluguel')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('eletrico')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('desastres')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));  
        $this->get('incendioMen')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('incendioConteudoMen')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('aluguelMen')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('eletricoMen')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
        $this->get('desastresMen')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
    }

}
