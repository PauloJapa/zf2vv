<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class AdministradoraRepository extends EntityRepository {

    
    public function autoComp($adminis){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Administradora', 'u')
                ->where("u.nome LIKE :adminis")
                ->setParameter('adminis', $adminis)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
}
