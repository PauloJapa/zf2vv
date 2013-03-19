<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * MultiplosMinimosRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class MultiplosMinimosRepository extends EntityRepository {

    
    public function findMultMinVigente($seguradora, $date){
        //Converter string data em objeto datetime
        if(!is_object($date)){
            $date = explode("/", $date);
            $date = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        }
                
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('mm')
                ->from('Livraria\Entity\MultiplosMinimos', 'mm')
                ->where(" mm.seguradora = :seguradora
                    AND   mm.multVigenciaInicio <= :inicio
                    ")
                ->setParameter('seguradora', $seguradora)
                ->setParameter('inicio', $date)
                ->setMaxResults(1)
                ->orderBy('mm.multVigenciaInicio', 'DESC')
                ->getQuery()
                ;
        return $query->getSingleResult();
        
    }
}

