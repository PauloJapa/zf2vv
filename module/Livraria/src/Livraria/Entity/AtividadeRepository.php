<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * AtividadeRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class AtividadeRepository extends EntityRepository {

    /** 
     * Buscar no banco todos registros para colocar no select do form
     * @return Array com a lista de registro  
     */ 
    public function fetchPairs() {
        $entities = $this->findAll();
        
        $array = array('' => 'Selecione na lista');
        
        foreach($entities as $entity) {
            $array[$entity->getId()] = $entity->getDescricao();
        }
        
        return $array;
    }
    
}

