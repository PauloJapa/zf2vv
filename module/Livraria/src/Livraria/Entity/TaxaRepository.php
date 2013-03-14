<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * TaxaRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class TaxaRepository extends EntityRepository {

    /**
     * Busca um taxa valida para atividade e seguradora vigente.
     * @param type $seguradora
     * @param type $atividade
     * @return boolean|Entity Livraria\Entity\Taxa
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
        $resul = $query->getResult();
        if(empty($resul))
            return FALSE;
        
        return $resul[0];
        //return $query->getSingleResult();
        
    }
}

