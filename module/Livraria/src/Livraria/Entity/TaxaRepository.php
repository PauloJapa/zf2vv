<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * TaxaRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class TaxaRepository extends EntityRepository {
/*
 * 
    // Example - $qb->innerJoin('u.Group', 'g', Expr\Join::ON, $qb->expr()->and($qb->expr()->eq('u.group_id', 'g.id'), 'g.name = ?1'))
    // Example - $qb->innerJoin('u.Group', 'g', 'ON', 'u.group_id = g.id AND g.name = ?1')
    public function innerJoin($join, $alias = null, $conditionType = null, $condition = null);

    // Example - $qb->leftJoin('u.Phonenumbers', 'p', Expr\Join::WITH, $qb->expr()->eq('p.area_code', 55))
    // Example - $qb->leftJoin('u.Phonenumbers', 'p', 'WITH', 'p.area_code = 55')
    public function leftJoin($join, $alias = null, $conditionType = null, $condition = null);

    // NOTE: ->where() overr
 */
    public function findTaxaVigente($seguradora, $atividade){
        
        
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t,c,s')
                ->from('Livraria\Entity\Taxa', 't')
                ->from('Livraria\Entity\ClasseAtividade', 'ca')
                ->join('t.classe', 'c')
                ->join('t.seguradora', 's')
                ->where(" t.seguradora = :seguradora
                    AND   t.status = :status
                    AND   c.seguradora = t.seguradora
                    AND   ca.classeTaxas = c
                    AND   ca.status = t.status
                    AND   ca.atividade = :atividade
                    AND   ca.seguradora = t.seguradora
                    ")
                ->setParameter('seguradora', $seguradora)
                ->setParameter('status', 'A')
                ->setParameter('atividade', $atividade)
                ->setMaxResults(10)
                ->getQuery()
                ;
        return $query->getSingleResult();
        
    }
}

