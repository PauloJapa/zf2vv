<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class SeguradoraRepository extends EntityRepository {

    public function fetchPairs() {
        $entities = $this->findAll();
        
        $array = array('' => 'Selecione na lista');
        
        foreach($entities as $entity) {
            $array[$entity->getId()] = $entity->getNome();
        }
        
        return $array;
    }
    
    public function autoComp($seguradora){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Seguradora', 'u')
                ->where("u.nome LIKE :seguradora")
                ->setParameter('seguradora', $seguradora)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
}

