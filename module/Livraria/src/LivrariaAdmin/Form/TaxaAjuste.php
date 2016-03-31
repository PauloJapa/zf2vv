<?php

namespace LivrariaAdmin\Form;


class TaxaAjuste extends AbstractForm {
    /**
     * inputs que podem se repetir para comercio e industria
     * @var array 
     */
    protected $inputs;  
    /**
     * Label dos inputs que podem se repetir para comercio e industria
     * @var array 
     */
    protected $inputsL;  
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
        $this->setInputText('inicio', '*Inicio da Vigência', $attributes, ['value' => date('d/m/Y')]);
        
        $this->setInputText('fim', '*Fim da Vigência', $attributes);
        
        $status = $this->getParametroSelect('status');
        $this->setInputSelect('status', '*Situação', $status, ['value' => 'A']);


        $classes = $this->em->getRepository('Livraria\Entity\Classe')->fetchPairs(['status'=>'A']);
        foreach ($classes as $key => $classe) {
            if(strpos( $classe, 'CLASSE') === FALSE){
                continue;
            }
            if(strpos($classe, 'SEM') !== FALSE){
                continue;
            }
            $this->classes[$key] = $classe;
        }
        $this->setInputSelect('classe', '*Classe', $this->classes);
        
        $this->seguradoras = $this->em->getRepository('Livraria\Entity\Seguradora')->fetchPairs(['status'=>'A']);
        asort($this->seguradoras);
        $this->setInputSelect('seguradora', '*Seguradora', $this->seguradoras, ['value' => '']);
        
//        $this->setComissao();
        
        $this->administradoras = $this->em->getRepository('Livraria\Entity\Administradora')->fetchPairs(['status'=>'A']);
        asort($this->administradoras);
        $this->administradoras = ['1' => 'TODAS ADMINISTRADORAS'] + $this->administradoras;
        $this->setInputSelect('administradora', '*Administradoras', $this->administradoras );
        
        $validade = $this->getParametroSelect('validade');
        $this->setInputSelect('validade', '*Validade', $validade);
        
        $ocupacao = [
            '04' => 'Apartamento',
            '02' => 'Casa',
            '01' => 'Comercio',
            '03' => 'Industria',
        ];
        $this->setInputRadio('ocupacao', '*Ocupação', $ocupacao);
                
        $this->inputs = ['contEle'             
                   ,'conteudo'                
                   ,'eletrico'               
                   ,'semContEle'                
                   ,'comEletrico'             
                   ,'semEletrico'               
                   ,'unica'      ]; 
        $this->inputsL = ['Taxa COM Conteudo e Dano Eletrico'         
                   ,'Taxa COM Conteudo'                             
                   ,'Taxa COM Dano Eletrico'                       
                   ,'Taxa SEM Conteudo e Dano Eletrico'               
                   ,'Taxa COM Dano Eletrico'                        
                   ,'Taxa SEM Dano Eletrico'                          
                   ,'Taxa Unica'                        ];          
        // Carrega para casa e apto
        foreach ($this->inputs as $key => $value) {
            $this->setInputText($value, $this->inputsL[$key]  , ['placeholder' => 'XXX,XX', 'id' => $value, 'onChange' => 'cleanAnother(this)' ]);
        }
        // Carrega para comercio e industria
        foreach ($this->classes as $key => $classe){
            $this->setInputHidden('idArray[' . $key . ']');
            foreach ($this->inputs as $k => $value) {
                $this->setInputText(
                    $value . 'Array[' . $key . ']' , 
                    ' ' , 
                    [
                        'placeholder' => 'XXX,XX', 
                        'id' => $value . '_' . $key ,
                        'class' => 'input-small',
                        'onChange' => 'cleanAnother(this)',
                    ]
                );
            }
        }       
        $this->setInputSubmit('enviar', 'Salvar');

    }
    
    /**
     * Seta as comissões se baseando na seguradora selecionada
     * @param array $data
     * @return no return
     */
    public function setComissao($data){
        if(!$this->isAdmin){
            return;
        }
        $this->remove('comissao');
        if(isset($data['seguradora'])){
            $comissaoKey = 'comissaoParam' . str_pad($data['seguradora'], 3, '0', STR_PAD_LEFT);
        }else{
            $comissaoKey = 'comissaoParam%';
        }            
        $comissao = $this->getParametroSelect($comissaoKey, TRUE);
        $this->setInputSelect('comissao', 'Comissão da Administradora',$comissao, ['onChange'=>'setComissao(this);']);
        if(isset($data['comissao'])){
            $this->get('comissao')->setValue($data['comissao']);
        }
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
    
    public function setEditDisabled() {
        $this->get('seguradora')->setAttribute('disabled', 'true'); 
        $this->get('administradora')->setAttribute('disabled', 'true'); 
        $this->get('validade')->setAttribute('disabled', 'true'); 
        $this->get('ocupacao')->setAttribute('disabled', 'true'); 
    }
    
    public function getClasses() {
        return $this->classes;
    }
    
    public function getInputs($opt = FALSE) {
        if ($opt){
            unset($this->inputs[4]);
            unset($this->inputs[5]);
        }
        return $this->inputs;
    }
    
    public function getLabelOfInputs($opt = FALSE) {
        if ($opt){
            unset($this->inputsL[4]);
            unset($this->inputsL[5]);
        }
        return $this->inputsL;
    }
}
