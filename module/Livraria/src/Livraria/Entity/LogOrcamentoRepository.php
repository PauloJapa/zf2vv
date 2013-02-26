<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * LogOrcamentoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogOrcamentoRepository extends EntityRepository {

    
    public function findLogOrcamento($filtro){
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('lo,u')
                ->from('Livraria\Entity\LogOrcamento', 'lo')
                ->join('lo.user', 'u')
                ->getQuery()
                ;
        return $query->getResult();
        
    }
}

