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
            $d = explode("/", $date);
            $date = new \DateTime($d[1] . '/' . $d[0] . '/' . $d[2]);
        }
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('ca')
                ->from('Livraria\Entity\ClasseAtividade', 'ca')
                ->where(" ca.atividade = :atividade
                    AND   ca.inicio <= :inicio
                    AND   ca.fim    > :fim
                    ")
                ->setParameter('atividade', $atividade)
                ->setParameter('inicio', $date)
                ->setParameter('fim', $date)
                ->setMaxResults(1)
                ->orderBy('ca.inicio', 'DESC')
                ->getQuery()
                ;
        
        $rs = $query->getResult();
        if(!empty($rs)){
            return $rs[0];
        }
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('ca')
                ->from('Livraria\Entity\ClasseAtividade', 'ca')
                ->where(" ca.atividade = :atividade
                    AND   ca.inicio <= :inicio
                    AND   ca.status    = :ativo
                    ")
                ->setParameter('atividade', $atividade)
                ->setParameter('inicio', $date)
                ->setParameter('ativo', 'A')
                ->setMaxResults(1)
                ->orderBy('ca.inicio', 'DESC')
                ->getQuery()
                ;
        
        $rs = $query->getResult();
        if(!empty($rs)){
            return $rs[0];
        }
        
        echo '<h2>Erro ao procurar classe na data especificada</h2><pre>';
        var_dump($atividade);
        var_dump($date);
        var_dump($ativo);
        echo '</pre>';
        
        //Procurar novamente mas com data de hoje
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('ca')
                ->from('Livraria\Entity\ClasseAtividade', 'ca')
                ->where(" ca.atividade = :atividade
                    ")
                ->setParameter('atividade', $atividade)
                ->setMaxResults(1)
                ->orderBy('ca.inicio', 'ASC')
                ->getQuery()
                ;
        
        return $query->getSingleResult();
    }
}

