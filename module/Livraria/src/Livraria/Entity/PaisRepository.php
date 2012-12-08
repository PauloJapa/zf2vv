<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class PaisRepository extends EntityRepository {

    public function fetchPairs() {
        $entities = $this->findAll();
        
        $array = array();
        
        foreach($entities as $entity) {
            $array[$entity->getId()] = $entity->getNome();
        }
        
        if(empty($array))$array[' ']= '';
        
        return $array;
    }
    
}

