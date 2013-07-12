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
        parent::logForNew('multiplos_minimos','multiplosMinimos');
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $this->setReferences();
        
        $this->verificaFimDeVigencia();
        
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
        parent::logForEdit('multiplos_minimos','multiplosMinimos');
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\MultiplosMinimos $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Minimo Premio Anual',  $ent->floatToStr('minPremioAnual'),   $this->strToFloat($this->data['minPremioAnual']));
        $this->dePara .= $this->diffAfterBefore('Minimo Premio Mensal',  $ent->floatToStr('minPremioMensal'),   $this->strToFloat($this->data['minPremioMensal']));
        $this->dePara .= $this->diffAfterBefore('Minimo Apolice Anual',  $ent->floatToStr('minApoliceAnual'),   $this->strToFloat($this->data['minApoliceAnual']));
        $this->dePara .= $this->diffAfterBefore('Minimo Apolice Mensal',  $ent->floatToStr('minApoliceMensal'),   $this->strToFloat($this->data['minApoliceMensal']));
        $this->dePara .= $this->diffAfterBefore('Minimo Parcela Anual',  $ent->floatToStr('minParcelaAnual'),   $this->strToFloat($this->data['minParcelaAnual']));
        $this->dePara .= $this->diffAfterBefore('Minimo Parcela Mensal',  $ent->floatToStr('minParcelaMensal'),   $this->strToFloat($this->data['minParcelaMensal']));
        $this->dePara .= $this->diffAfterBefore('Minimo Aluguel',  $ent->floatToStr('minAluguel'),   $this->strToFloat($this->data['minAluguel']));
        $this->dePara .= $this->diffAfterBefore('Minimo Conteudo', $ent->floatToStr('minConteudo'), $this->strToFloat($this->data['minConteudo']));
        $this->dePara .= $this->diffAfterBefore('Minimo Incendio',   $ent->floatToStr('minIncendio'),   $this->strToFloat($this->data['minIncendio']));
        $this->dePara .= $this->diffAfterBefore('Minimo Eletrico', $ent->floatToStr('minEletrico'), $this->strToFloat($this->data['minEletrico']));
        $this->dePara .= $this->diffAfterBefore('Minimo Vendaval', $ent->floatToStr('minVendaval'), $this->strToFloat($this->data['minVendaval']));
        $this->dePara .= $this->diffAfterBefore('Maximo Aluguel',  $ent->floatToStr('maxAluguel'),   $this->strToFloat($this->data['maxAluguel']));
        $this->dePara .= $this->diffAfterBefore('Maximo Conteudo', $ent->floatToStr('maxConteudo'), $this->strToFloat($this->data['maxConteudo']));
        $this->dePara .= $this->diffAfterBefore('Maximo Incendio',   $ent->floatToStr('maxIncendio'),   $this->strToFloat($this->data['maxIncendio']));
        $this->dePara .= $this->diffAfterBefore('Maximo Eletrico', $ent->floatToStr('maxEletrico'), $this->strToFloat($this->data['maxEletrico']));
        $this->dePara .= $this->diffAfterBefore('Maximo Vendaval', $ent->floatToStr('maxVendaval'), $this->strToFloat($this->data['maxVendaval']));
        $this->dePara .= $this->diffAfterBefore('Maximo Inicio', $ent->getMultVigenciaInicio(), $this->data['multVigenciaInicio']->format('d/m/Y'));
        $check = $this->data['multVigenciaFim']->format('d/m/Y');
        if($check == '01/01/1000'){
            $check = "vigente";
        }
        $this->dePara .= $this->diffAfterBefore('Vigencia Fim',    $ent->getMultVigenciaFim(),$check);
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getMultStatus(), $this->data['multStatus']);
    }

    public function verificaFimDeVigencia() {
        // Pega registro do banco
        $ent = $this->em->find($this->entity, $this->data['id']);
        // Se houver alteração para menor ou igual na data inicio retorna
        if($ent->getMultVigenciaInicio('obj') >= $this->data['multVigenciaInicio']){
            return FALSE;
        }
        
        // Se não for vigente retorna
        if($ent->getMultVigenciaFim() != 'vigente'){
            return FALSE;
        }
        
        //Reconfirma dados para inserção do novo registro
        $data = $this->data;
        $data['id']              = '' ;
        $data['idMultiplos']     = '' ;
        $data['multStatus']      = 'A';
        $data['multVigenciaFim'] = 'vigente';
        
        $auxService = new MultiplosMinimos($this->em);
        $auxService->notValidateNew();
        $resul = $auxService->insert($data);
        if($resul !== TRUE){
            var_dump ($data);
        }
        
        //Refazer dados
        $this->data = $ent->toArray();
        
        //Finalizar vigência e refazer dados do registro atual
        $this->data['multVigenciafim'] = clone $data['multVigenciaInicio'];
        $this->data['multVigenciafim']->sub(new \DateInterval('P1D')); 
        $this->data['multStatus'] = 'C';
        
        //Refazer referencia dos registro
        $this->setReferences();
        return TRUE;
        
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){ 
        // Casos especias as validações estão dispensadas
        if(!$this->isValid)
            return TRUE;
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
