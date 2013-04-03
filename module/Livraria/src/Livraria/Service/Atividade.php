<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
/**
 * Atividade
 * Faz o CRUD da tabela Atividade no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Atividade extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Atividade";
    }
    
    /**
     * Faz as conversões de id para entity para o doctrine valida
     * Abstração das actions new e edit
     */
    public function setReferences(){
        //Caso a bairro não foi escolhido da lista procura o id pelo nome 
        if(!isset($this->data['danosEletricos'])) $this->data['danosEletricos'] ='';
        if(!isset($this->data['equipEletro']))    $this->data['equipEletro']    ='';
        if(!isset($this->data['vendavalFumaca'])) $this->data['vendavalFumaca'] ='';
        if(!isset($this->data['basica']))         $this->data['basica']         ='';
        if(!isset($this->data['roubo']))          $this->data['roubo']          ='';
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
     * Grava em logs de quem, quando, tabela e id que inseriu o registro em taxas
     */
    public function logForNew(){
        parent::logForNew('atividade');
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
     * Grava no logs dados da alteção feita em locadors De/Para
     */
    public function logForEdit($tabela='atividade'){
        parent::logForEdit($tabela);
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
        $entitys = $repository->findBy(array(
            'descricao' => $this->data['descricao']
        ));
        $erro = [] ;
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                $erro[] = "Alerta! Já existe esta Atividade cadastrada!!";
            }
        }
        return empty($erro) ? TRUE : $erro;
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Atividade $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Nome', $ent->getDescricao(), $this->data['descricao']);
        $this->dePara .= $this->diffAfterBefore('Referencia', $ent->getCodSeguradora(), $this->data['codSeguradora']);
        $this->dePara .= $this->diffAfterBefore('Ocupação', $ent->getOcupacao(), $this->data['ocupacao']);
        $this->dePara .= $this->diffAfterBefore('Situação', $ent->getStatus(), $this->data['status']);
        $this->dePara .= $this->diffAfterBefore('Danos Eletricos', $ent->getDanosEletricos(), $this->data['danosEletricos']);
        $this->dePara .= $this->diffAfterBefore('Equipamento eletronico', $ent->getEquipEletro(), $this->data['equipEletro']);
        $this->dePara .= $this->diffAfterBefore('Vendaval Fumaca', $ent->getVendavalFumaca(), $this->data['vendavalFumaca']);
        $this->dePara .= $this->diffAfterBefore('Basica', $ent->getBasica(), $this->data['basica']);
        $this->dePara .= $this->diffAfterBefore('Roubo', $ent->getRoubo(), $this->data['roubo']);
    }
}
