<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * Taxa
 * Faz o CRUD da tabela Taxa no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Taxa extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Taxa";
    }
    
    public function setReferences(){
        //Pega uma referencia do registro da tabela classe
        $this->idToReference('seguradora', 'Livraria\Entity\Seguradora');
        $this->idToReference('classe', 'Livraria\Entity\Classe');
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        $this->data = $data;
        
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        
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
        
        $this->dateToObject('inicio');
        $this->dateToObject('fim');
        
        $result = $this->isValid();
        if($result !== TRUE){
            return $result;
        }
        
        $this->setReferences();
        
        return parent::update();
    }
    
    public function isValid(){ 
        // Valida se o registro esta conflitando com algum registro existente
        $repository = $this->em->getRepository($this->entity);
        $entitys = $repository->findBy(array('seguradora' => $this->data['seguradora'], 
                                             'classe' => $this->data['classe'],
                                             'status' => 'A'
                                            )
                                      );
        $diferenca = 3650 ;
        if(!$entitys)
            $diferenca = 0 ;
        $erro = null ;
        foreach ($entitys as $entity) {
            if($this->data['id'] != $entity->getId()){
                if(($entity->getFim() == 'vigente') and ($this->data['fim']->format('d/m/Y') == '30/11/-0001')){
                    $erro[] = "Alerta! Já existe um taxa para esta seguradora e classe vigente! ID = " . $entity->getId() ;
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
        }
        if(($diferenca > 3) and ($this->data['fim']->format('d/m/Y') == '30/11/-0001')){
            $erro[] = "Alerta! Data de inicio esta com + 3 dias da data do ultima taxa valida! " ;
            $erro[] = 'Direfença de dias é ' . $diferenca;
        }
        if($erro){
            return $erro;
        }else{
            return TRUE;
        }
    }
}
