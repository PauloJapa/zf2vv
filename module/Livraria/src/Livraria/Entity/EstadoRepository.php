<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class EstadoRepository extends EntityRepository {

    public function fetchPairs() {
        $entities = $this->findAll();
        
        $array = array('' => 'Selecione na lista');
        
        foreach($entities as $entity) {
            $array[$entity->getId()] = $entity->getSigla();
        }
        
        return $array;
    }
    
}

