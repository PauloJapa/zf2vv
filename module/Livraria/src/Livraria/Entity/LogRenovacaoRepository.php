<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * LogRenovacaoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogRenovacaoRepository extends EntityRepository {

    
    public function findLogRenovacao($filtro){
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('lr,u')
                ->from('Livraria\Entity\LogRenovacao', 'lr')
                ->join('lr.user', 'u')
                ->getQuery()
                ;
        return $query->getResult();
        
    }
}

