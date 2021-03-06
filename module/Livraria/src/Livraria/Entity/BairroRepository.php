<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class BairroRepository extends EntityRepository {

    public function fetchPairs() {
        $entities = $this->findAll();
        
        $array = array(' ' => 'Selecione na lista');
        
        foreach($entities as $entity) {
            $array[$entity->getId()] = $entity->getNome();
        }
        
        return $array;
    }
    
    public function autoComp($bairro){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Bairro', 'u')
                ->where("u.nome LIKE :bairro")
                ->setParameter('bairro', $bairro)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
}

