<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class SeguradoraRepository extends EntityRepository {

    public function fetchPairs(array $filtros=[]) {
        $entities = $this->findBy($filtros);
        
        $array = array('' => 'Selecione na lista');
        
        foreach($entities as $entity) {
            if($entity->getId() == 1)continue; //pula o id 1 ele é uma referencia vazia
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

