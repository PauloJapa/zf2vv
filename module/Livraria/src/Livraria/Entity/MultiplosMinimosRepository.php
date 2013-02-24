<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * MultiplosMinimosRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class MultiplosMinimosRepository extends EntityRepository {

    
    public function findMultMinVigente($seguradora){
                
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('mm,s')
                ->from('Livraria\Entity\MultiplosMinimos', 'mm')
                ->join('mm.seguradora', 's')
                ->where(" mm.seguradora = :seguradora
                    AND   mm.multStatus = :status
                    ")
                ->setParameter('seguradora', $seguradora)
                ->setParameter('status', 'A')
                ->setMaxResults(5)
                ->orderBy('mm.multVigenciaInicio', 'DESC')
                ->getQuery()
                ;
        return $query->getSingleResult();
        
    }
}

