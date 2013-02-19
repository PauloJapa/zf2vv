<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * MultiplosMinimos
 * Faz o CRUD da tabela MultiplosMinimos no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class MultiplosMinimos extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do multiplosMinimos
     * @var string 
     */
    protected $deParaEnd;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\MultiplosMinimos";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        
        $this->dateToObject('multVigenciaInicio');
        $this->dateToObject('multVigenciaFim');
        
        $this->data['id'] = $this->data['idMultiplos'];
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
        
        $this->setReferences();
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        if(parent::insert())
            $this->logForNew();
        
        return TRUE;      
    }
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em multiplosMinimoss
     */
    public function logForNew(){
        parent::logForNew('multiplos_minimos');
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $this->setReferences();
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        if(parent::update())
            $this->logForEdit();
        
        return TRUE;
    }
    
    /**
     * Grava no logs dados da alteção feita em multiplosMinimoss De/Para
     */
    public function logForEdit(){
        parent::logForEdit('multiplos_minimos');
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\MultiplosMinimos $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Multiplo Aluguel',  $ent->floatToStr('multAluguel'),   $this->strToFloat($this->data['multAluguel']));
        $this->dePara .= $this->diffAfterBefore('Multiplo Conteudo', $ent->floatToStr('multConteudo'), $this->strToFloat($this->data['multConteudo']));
        $this->dePara .= $this->diffAfterBefore('Multiplo Predio',   $ent->floatToStr('multPredio'),   $this->strToFloat($this->data['multPredio']));
        $this->dePara .= $this->diffAfterBefore('Multiplo Eletrico', $ent->floatToStr('multEletrico'), $this->strToFloat($this->data['multEletrico']));
        $this->dePara .= $this->diffAfterBefore('Multiplo Vendaval', $ent->floatToStr('multVendaval'), $this->strToFloat($this->data['multVendaval']));
        $this->dePara .= $this->diffAfterBefore('Minimo Aluguel',  $ent->floatToStr('minAluguel'),   $this->strToFloat($this->data['minAluguel']));
        $this->dePara .= $this->diffAfterBefore('Minimo Conteudo', $ent->floatToStr('minConteudo'), $this->strToFloat($this->data['minConteudo']));
        $this->dePara .= $this->diffAfterBefore('Minimo Predio',   $ent->floatToStr('minPredio'),   $this->strToFloat($this->data['minPredio']));
        $this->dePara .= $this->diffAfterBefore('Minimo Eletrico', $ent->floatToStr('minEletrico'), $this->strToFloat($this->data['minEletrico']));
        $this->dePara .= $this->diffAfterBefore('Minimo Vendaval', $ent->floatToStr('minVendaval'), $this->strToFloat($this->data['minVendaval']));
        $this->dePara .= $this->diffAfterBefore('Vigencia Inicio', $ent->getMultVigenciaInicio(), $this->data['multVigenciaInicio']->format('d/m/Y'));
        $check = $this->data['multVigenciaFim']->format('d/m/Y');
        if($check == '30/11/-0001'){
            $check = "vigente";
        }
        $this->dePara .= $this->diffAfterBefore('Vigencia Fim',    $ent->getMultVigenciaFim(),$check);
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getMultStatus(), $this->data['multStatus']);
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){ 
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $entitys = $repository->findBy(array('seguradora' => $this->data['seguradora'], 'multStatus' => 'A'));
        $diferenca = 3650 ;
        if(!$entitys)
            $diferenca = 0 ;
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                //Comparações com registro existentes
                if(($entity->getMultVigenciaFim() == 'vigente') and ($this->data['multVigenciaFim']->format('d/m/Y') == '30/11/-0001')){
                    $erro[] = "Alerta! Já existe um registro vigente = " . $entity->getId() ;
                }
                $fim = $entity->getMultVigenciaFim('obj');
                if($fim >= $this->data['multVigenciaInicio']){
                    $erro[] = "Alerta! Data de inicio conflita com data de registro existente! ID = " . $entity->getId() ;
                    $erro[] = "Data de inicio não pode ser menor ou igual a data final de vigencia<br>";
                }
                $diff = $fim->diff($this->data['multVigenciaInicio']);
                if($diff->days < $diferenca){
                    $diferenca = $diff->days ;
                }
            }
        }
        if(($diferenca > 3) and ($this->data['multVigenciaFim']->format('d/m/Y') == '30/11/-0001') and ($diferenca != 3650)){
            $erro[] = "Alerta! Data de inicio esta com + 3 dias da data do ultima registro valido! " ;
            $erro[] = 'Direfença de dias é ' . $diferenca;
        }
        if(!empty($erro)){
            return $erro;
        }else{
            return TRUE;
        }
    }
}
