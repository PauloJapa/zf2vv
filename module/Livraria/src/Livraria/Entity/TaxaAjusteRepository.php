<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * TaxaAjusteRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class TaxaAjusteRepository extends EntityRepository {

    /**
     * Busca uma taxaAjuste para atividade e seguradora na data $date.
     * 
     * @param type $seguradora
     * @param type $administradora
     * @param type $date
     * @param string $validade      mensal|anual
     * @param type $classe
     * @param type $ocupacao
     * @return boolean|Entity \Livraria\Entity\TaxaAjuste
     */
    public function findTaxaAjusteVigente($seguradora, $administradora, $date, $validade, $classe, $ocupacao){
        
    }
    
    public function getData($id='') {
        if(empty($id)){
            return [];
        }
        $entity = $this->find($id);
        
        switch($entity->getOcupacao()){
            case '04': //apto
            case '02': // casa
                return $entity->toArray();
            case '01': // comercio
            case '03': // industria
                return $this->allExistClassesOf($entity);
            default:    
                return [];
        } 
    }

    public function allExistClassesOf($entity) {
        $data = $entity->toArray();
        
        $filters = ['inicio' => $entity->getInicio('obj'), 'ocupacao' => $entity->getOcupacao(),'validade' => $entity->getValidade()];
        if(!is_null($entity->getAdministradora())){
            $filters['administradora'] = $entity->getAdministradora()->getId(); 
        }
        $entitys = $this->findBy($filters);
        $inputs = ['contEle'             
                   ,'conteudo'                
                   ,'eletrico'               
                   ,'semContEle'                
                   ,'unica'      ]; 
        foreach ($inputs as $input) {
            $data[$input] = '';
        }
        foreach ($entitys as $ent) {
            $data['idArray[' . $ent->getClasse()->getId() . ']'] = $ent->getId();
            foreach ($inputs as $input) {
                $data[$input . 'Array[' . $ent->getClasse()->getId() . ']'] = $ent->floatToStr($input);
            }
        }
        return $data;
    }

}

