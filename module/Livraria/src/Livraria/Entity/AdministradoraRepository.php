<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class AdministradoraRepository extends EntityRepository {

    
    public function autoComp($adminis){
        $clean = preg_replace("/[^0-9]/", "", $adminis);
        $dif = strlen($adminis) - strlen($clean);
        if(is_numeric($clean) AND $dif <= 3){
            $where = "u.id >= :adminis";
        }else{
            $where = "u.nome LIKE :adminis";
        }
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Administradora', 'u')
                ->where($where)
                ->setParameter('adminis', $adminis)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
    
    public function fetchPairs() {
        $entities = $this->findAll();
        
        $array = array();
        
        foreach($entities as $entity) {
            if($entity->getId() == 1)continue; //pula o id 1 ele é uma referencia vazia
            $array[$entity->getId()] = $entity->getNome();
        }
        
        return $array;
    }
    
}
