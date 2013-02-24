<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * LogOrcamento
 * Faz o CRUD da tabela LogOrcamento no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogOrcamento extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\LogOrcamento";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('orcamento', 'Livraria\Entity\Orcamento');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
        
        $this->data['data']         = new \DateTime('now');
        $this->data['userIdCriado'] = $this->getIdentidade()->getId();
        $this->data['ip']           = $_SERVER['REMOTE_ADDR'];
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->setReferences();

        return parent::insert();       
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return boolean|array 
     */    
    public function update(array $data) {
        $this->data = $data;
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->setReferences();
        
        return parent::update();
    }
    
    public function isValid(){ 
        // Valida se o registro esta conflitando com algum registro existente
        return TRUE;
    }
}
