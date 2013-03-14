<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * RenovacaoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class RenovacaoRepository extends EntityRepository {

    
    public function findRenovar($ini,$fim,$adm){
        if(!empty($ini)){
            $date = explode("/", $ini);
            $ini  = $date[2] . $date[1] . $date[0];
        }
        if(!empty($fim)){
            $date = explode("/", $fim);
            $fim  = $date[2] . $date[1] . $date[0];
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('f')
            ->from('Livraria\Entity\Fechados', 'f');
        if(!empty($adm)){
            $qb->where(" f.status <> :status
                    AND   f.administradora = :administradora
                    AND   f.fim BETWEEN :inicio AND :fim
                ")
                ->setParameters([
                    'status' => 'R',
                    'administradora' => $adm,
                    'inicio' => $ini,
                    'fim' => $fim
                ]);
        }else{
            $qb->where(" f.status <> :status
                    AND   f.fim BETWEEN :inicio AND :fim
                ")
                ->setParameters([
                    'status' => 'R',
                    'inicio' => $ini,
                    'fim' => $fim
                ]);
        }
        
        return $qb->getQuery()->getResult();
    }
}

