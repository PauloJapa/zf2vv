<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * LogFechadosRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogFechadosRepository extends EntityRepository {

    
    public function findLogFechados($filtro){
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('lf,u')
                ->from('Livraria\Entity\LogFechados', 'lf')
                ->join('lf.user', 'u')
                ->getQuery()
                ;
        return $query->getResult();
        
    }
}

