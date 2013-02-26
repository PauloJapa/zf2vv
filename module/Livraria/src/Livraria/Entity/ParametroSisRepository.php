<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * ParametroSisRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ParametroSisRepository extends EntityRepository {
    
    public function findKey($key){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('p')
                ->from('Livraria\Entity\ParametroSis', 'p')
                ->where(" p.key LIKE :key")
                ->setParameter('key', $key)
             //   ->setMaxResults(20)
                ->getQuery()
                ; 
        return $query->getResult();
    }
}
