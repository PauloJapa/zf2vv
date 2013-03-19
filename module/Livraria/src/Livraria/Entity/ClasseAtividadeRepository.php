<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * ClasseAtividadeRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ClasseAtividadeRepository extends EntityRepository {

    /**
     * Busca a Classe correspondente a seguradora, atividade e data
     * @param string $seguradora
     * @param string $atividade
     * @param string $date
     * @return array|Livraria\Entity\ClasseAtividade
     */
    public function findClasseVigente($seguradora, $atividade, $date){
        //Converter string data em objeto datetime
        if(!is_object($date)){
            $date = explode("/", $date);
            $date = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        }
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('ca')
                ->from('Livraria\Entity\ClasseAtividade', 'ca')
                ->where(" ca.seguradora = :seguradora
                    AND   ca.atividade = :atividade
                    AND   ca.inicio <= :inicio
                    ")
                ->setParameter('seguradora', $seguradora)
                ->setParameter('atividade', $atividade)
                ->setParameter('inicio', $date)
                ->setMaxResults(1)
                ->orderBy('ca.inicio', 'DESC')
                ->getQuery()
                ;
        return $query->getSingleResult();
    }
}

