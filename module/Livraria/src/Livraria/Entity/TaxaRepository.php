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
     * Query 
     *  SELECT * FROM `comissao` 
        WHERE inicio <= 20130601 
        AND administradora_id = 1
        ORDER BY inicio DESC    
        LIMIT 1 
     */
    /**
     * Busca uma taxa para atividade e seguradora na data $date.
     * @param string $seguradora
     * @param string $atividade
     * @param string $date
     * @return boolean|Entity Livraria\Entity\Taxa
     */
    public function findTaxaVigente($seguradora, $atividade, $date){
        //Pegar classeAtividade correspondente vigente na data
        $classeAtividade = $this->getEntityManager()
                ->getRepository('Livraria\Entity\ClasseAtividade')
                ->findClasseVigente($seguradora, $atividade, $date);
        
        if(!is_object($classeAtividade)){
            return FALSE;
        }
        //Converter string data em objeto datetime
        if(!is_object($date)){
            $date = explode("/", $date);
            $date = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        }
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from('Livraria\Entity\Taxa', 't')
                ->where(" t.seguradora = :seguradora
                    AND   t.classe = :classe
                    AND   t.inicio <= :inicio
                    ")
                ->setParameter('seguradora', $seguradora)
                ->setParameter('classe', $classeAtividade->getClasseTaxas()->getId())
                ->setParameter('inicio', $date)
                ->setMaxResults(1)
                ->orderBy('t.inicio', 'DESC')
                ->getQuery()
                ;
        $resul = $query->getResult();
        if(empty($resul))
            return FALSE;
        
        return $resul[0];
        //return $query->getSingleResult();
        
    }
}

