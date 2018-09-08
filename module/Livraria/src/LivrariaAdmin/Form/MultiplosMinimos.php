<?php

namespace LivrariaAdmin\Form;

class MultiplosMinimos extends AbstractForm {
    /**
     * Registros para preencher o input select
     * @var array 
     */  
    protected $seguradoras;  
    
    public function __construct($name = null, $em = null, $filtro=[]) {
        parent::__construct('taxa');
        $this->em = $em;
        $this->seguradoras = $this->em->getRepository('Livraria\Entity\Seguradora')->fetchPairs();

        $this->setAttribute('method', 'post');
        $this->setInputFilter(new MultiplosMinimosFilter);  

        $this->setInputHidden('idMultiplos');
        $this->setInputHidden('subOpcao');
        $this->setInputHidden('ajaxStatus');
        $this->setInputHidden('autoComp'); 
        
        $attributos = ['class'=>'input-small'];
          
        $this->setInputText('minPremioAnual', 'Minimo Premio Anual',$attributos);
        $this->setInputText('minPremioMensal', 'Minimo Premio Mensal',$attributos);
        $this->setInputText('minApoliceAnual', 'Minimo Apolice Anual',$attributos);
        $this->setInputText('minApoliceMensal', 'Minimo Apolice Mensal',$attributos);
        $this->setInputText('minParcelaAnual', 'Minimo Parcela Anual',$attributos);
        $this->setInputText('minParcelaMensal', 'Minimo Parcela Mensal',$attributos);
        
        $this->setInputText('minAluguel', 'Minimo para Aluguel',$attributos);
        $this->setInputText('minConteudo', 'Min. p/ Incêndio Conteúdo',$attributos);
        $this->setInputText('minIncendio', 'Minimo para Incêndio',$attributos);
        $this->setInputText('minEletrico', 'Minimo para Eletrico',$attributos);
        $this->setInputText('minVendaval', 'Minimo para Vendaval',$attributos);
        $this->setInputText('minRespcivil', 'Minimo para Resp. Civil',$attributos);
        
        $this->setInputText('maxAluguel', 'Maximo para Aluguel',$attributos);
        $this->setInputText('maxConteudo', 'Max. p/ Incêndio Conteúdo',$attributos);
        $this->setInputText('maxIncendio', 'Maximo para Incêndio',$attributos);
        $this->setInputText('maxEletrico', 'Maximo para Eletrico',$attributos);
        $this->setInputText('maxVendaval', 'Maximo para Vendaval',$attributos);
        $this->setInputText('maxRespcivil', 'Maximo para Resp. Civil',$attributos);

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
