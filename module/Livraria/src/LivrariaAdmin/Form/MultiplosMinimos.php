<?php

namespace LivrariaAdmin\Form;

use Zend\Form\Form,
    Zend\Form\Element\Select;

class MultiplosMinimos extends AbstractForm {
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
    
    /**
     * Para setar o form corretamente para edição de dados
     * @var bollean 
     */
    protected $isEdit = false;

    public function __construct($name = null, $em = null, $filtro=[]) {
        parent::__construct('taxa');
        $this->em = $em;
        $this->seguradoras = $this->em->getRepository('Livraria\Entity\Seguradora')->fetchPairs($filtro);

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new MultiplosMinimosFilter);  

        $this->setInputHidden('idMultiplos');
        $this->setInputHidden('subOpcao');
        $this->setInputHidden('ajaxStatus');
        $this->setInputHidden('autoComp');  

        $attributos = ['class'=>'input-small'];
        $this->setInputText('multAluguel', 'Multiplo para Aluguel',$attributos);
        $this->setInputText('multConteudo', 'Multiplo para Conteudo',$attributos);
        $this->setInputText('multPredio', 'Multiplo para Predio',$attributos);
        $this->setInputText('multEletrico', 'Multiplo para Eletrica',$attributos);
        $this->setInputText('multVendaval', 'Multiplo para Vendaval',$attributos);
        
        $this->setInputText('minAluguel', 'Minimo para Aluguel',$attributos);
        $this->setInputText('minConteudo', 'Minimo para Conteudo',$attributos);
        $this->setInputText('minPredio', 'Minimo para Predio',$attributos);
        $this->setInputText('minEletrico', 'Minimo para Eletrico',$attributos);
        $this->setInputText('minVendaval', 'Minimo para Vendaval',$attributos);
        
        $this->setInputText('maxAluguel', 'Maximo para Aluguel',$attributos);
        $this->setInputText('maxConteudo', 'Maximo para Conteudo',$attributos);
        $this->setInputText('maxPredio', 'Maximo para Predio',$attributos);
        $this->setInputText('maxEletrico', 'Maximo para Eletrico',$attributos);
        $this->setInputText('maxVendaval', 'Maximo para Vendaval',$attributos);

        $options = ['A'=>'Ativo','B'=>'Bloqueado','C'=>'Cancelado'];
        $this->setInputSelect('multStatus', '*Situação', $options,$attributos);
        
        
        $attributes = ['placeholder' => 'dd/mm/yyyy','onClick' => "displayCalendar(this,dateFormat,this)",'class'=>'input-small'];
        $this->setInputText('multVigenciaInicio', 'Inicio da Vigência', $attributes);
        
        $this->setInputText('multVigenciaFim', 'Fim da Vigência', $attributes);
        
        $this->setInputSelect('seguradora', 'Seguradora', $this->seguradoras, ['onChange'=>'buscar()']);

        $this->setInputSubmit('enviar', 'Salvar', ['onClick' => 'return salvar()']);
        
    }
    
    /**
     * Recarrega o select baseado em filtro
     * @param array $filtro
     */
    public function reloadSelectClasse(array $filtro){
        if($this->isEdit)
            $this->setEdit ();
    }
  
    /**
     * Atualiza o form para o modo de edição bloqueando campos se necessario
     */   
    public function setEdit(){
        $this->isEdit = TRUE;
        $this->get('seguradora')->setAttribute('disabled', 'disabled');   
        $this->get('multVigenciaInicio')->setAttributes(array('readOnly' => 'true', 'onClick' => ''));   
    }

}
