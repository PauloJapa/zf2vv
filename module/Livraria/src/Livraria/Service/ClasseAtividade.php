<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * ClasseAtividade
 * Faz o CRUD da tabela ClasseAtividade no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ClasseAtividade extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\ClasseAtividade";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('classeTaxas', 'Livraria\Entity\Classe');
        $this->idToReference('atividade', 'Livraria\Entity\Atividade');
        //Converter data string em objetos date
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
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
     * Grava em logs de quem, quando, tabela e id que inseriu o registro
     */
    public function logForNew(){
        parent::logForNew('classe_atividade');
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
     * Grava no logs dados da alteção feita na Entity
     * @return no return
     */
    public function logForEdit(){
        parent::logForEdit('classe_atividade');
    }

    public function verificaFimDeVigencia() {
        // Pega registro do banco
        $ent = $this->em->find($this->entity, $this->data['id']);
        // Se houver alteração para menor ou igual na data inicio retorna
        if($ent->getInicio('obj') >= $this->data['inicio']){
            return FALSE;
        }
        
        // Se não for vigente retorna
        if($ent->getFim() != 'vigente'){
            return FALSE;
        }
        
        //Reconfirma dados para inserção do novo registro
        $data = $this->data;
        $data['id']     = '' ;
        $data['status'] = 'A';
        $data['fim']    = 'vigente';
        
        $auxService = new ClasseAtividade($this->em);
        $auxService->notValidateNew();
        $resul = $auxService->insert($data);
        if($resul !== TRUE){
            var_dump ($data);
        }
        
        //Refazer dados
        $this->data = $ent->toArray();
        
        //Finalizar vigência e refazer dados do registro atual
        $this->data['fim'] = clone $data['inicio'];
        $this->data['fim']->sub(new \DateInterval('P1D')); 
        $this->data['status'] = 'C';
        unset($this->data['criadoEm']);
        unset($this->data['alteradoEm']);
        
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
        $filtro = [];
        if(empty($this->data['atividade']))
            return array('Atividade não pode estar vazia!!');
            
        $filtro['atividade'] = $this->data['atividade']->getId();
        
        $entitys = $repository->findBy($filtro);
        $diferenca = 3650 ;
        if(!$entitys)
            $diferenca = 0 ;
        $erro = array();
        foreach ($entitys as $entity) {
            if($this->data['id'] == $entity->getId()){
                continue;
            }
            if(($entity->getFim() == 'vigente') and ($this->data['fim']->format('d/m/Y') == '01/01/0001')){
                $erro[] = "Alerta! Já existe uma classe com esta Atividade para esta seguradora com data vigente! ID = " . $entity->getId() ;
            }
            $fim = $entity->getFim('obj');
            if($fim >= $this->data['inicio']){
                $erro[] = "Alerta! Data de inicio conflita com data de registro existente! ID = " . $entity->getId() ;
                $erro[] = "Data de inicio não pode ser menor ou igual a data final de vigencia<br>";
            }
            $diff = $fim->diff($this->data['inicio']);
            if($diff->days < $diferenca){
                $diferenca = $diff->days ;
            }
        }
        if(($diferenca > 3) and ($this->data['fim']->format('d/m/Y') == '01/01/0001') and ($diferenca != 3650)){
            $erro[] = "Alerta! Data de inicio esta com + 3 dias da data do ultima taxa valida! " ;
            $erro[] = 'Direfença de dias é ' . $diferenca;
        }
        return empty($erro) ? TRUE : $erro;
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\ClasseAtividade $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Atividade', $ent->getAtividade(), $this->data['atividade']);
        $this->dePara .= $this->diffAfterBefore('Classe', $ent->getClasseTaxas(), $this->data['classeTaxas']);
        $this->dePara .= $this->diffAfterBefore('Data inicio', $ent->getInicio(), $this->data['inicio']->format('d/m/Y'));
        $fim = $this->data['fim']->format('d/m/Y');
        if('01/01/0001' == $fim)
            $fim = 'vigente';
        $this->dePara .= $this->diffAfterBefore('Data Fim', $ent->getFim(), $fim);
        $this->dePara .= $this->diffAfterBefore('Status', $ent->getStatus(), $this->data['status']);
        $this->dePara .= $this->diffAfterBefore('Cod Antigo', $ent->getCodOld(), $this->data['codOld']);
        $this->dePara .= $this->diffAfterBefore('Codcia antigo', $ent->getCodciaOld(), $this->data['codciaOld']);
        $this->dePara .= $this->diffAfterBefore('Sequencia', $ent->getSeq(), $this->data['seq']);
    }
}
