<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * ComissaoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ComissaoRepository extends EntityRepository {

    
    public function findComissaoVigente($administradora){
                
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('c')
                ->from('Livraria\Entity\Comissao', 'c')
                ->where(" c.administradora = :administradora
                    AND   c.status = :status
                    ")
                ->setParameter('administradora', $administradora)
                ->setParameter('status', 'A')
                ->setMaxResults(1)
                ->orderBy('c.inicio', 'DESC')
                ->getQuery()
                ;
        return $query->getSingleResult();
        
    }
    
}

