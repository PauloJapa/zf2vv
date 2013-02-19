<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * ImovelRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ImovelRepository extends EntityRepository {

    
    public function autoComp($locador){
        if(empty($locador))
            return false;
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Imovel', 'u')
                ->where("u.locador = :locador")
                ->setParameter('locador', $locador)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
}

