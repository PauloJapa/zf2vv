<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

/**
 * Renovacao
 * Faz o CRUD da tabela Renovacao no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Renovacao extends AbstractService {
    
    /**
     * Registra os campos monitorados e afetados do endereço do imovel
     * @var string 
     */
    protected $deParaImovel;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Renovacao";
    }
    
    public function setReferences(){
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
        
        return array(TRUE,  $this->data['id']);      
    }   
    
    /**
     * Grava em logs de quem, quando, tabela e id que inseriu o registro
     */
    public function logForNew(){
        parent::logForNew('renovacao');
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
     * Grava no logs dados da alteção feita na Entity
     * @return no return
     */
    public function logForEdit(){
        parent::logForEdit('renovacao');
    }

    /**
     * Faz a validação do registro no BD antes de incluir
     * Caso de erro retorna um array com os erros para o usuario ver
     * Caso ok retorna true
     * @return array|boolean
     */
    public function isValid(){ 
        return TRUE;
        // Valida se o registro esta conflitando com algum registro existente
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Renovacao $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
    }
    
}
