<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * LogFechados
 * Faz o CRUD da tabela LogFechados no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogFechados extends AbstractService {
    
    /**
     * Data de agora da classe
     * @var \DateTime
     */
    protected $dataAgora;

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\LogFechados";
    }
    
    public function getDataAgora() {
        if(is_null($this->dataAgora)){
            $this->dataAgora = new \DateTime('now');
        }  else {
            $this->dataAgora->add(new \DateInterval('PT1S'));            
        }
        return $this->dataAgora;
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('fechados', 'Livraria\Entity\Fechados');
        $this->idToReference('user', 'Livraria\Entity\User');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
        
        $this->data['data'] = $this->getDataAgora();
        $this->data['user'] = $this->getIdentidade()->getId();
        $this->data['ip']   = $_SERVER['REMOTE_ADDR'];
        
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
