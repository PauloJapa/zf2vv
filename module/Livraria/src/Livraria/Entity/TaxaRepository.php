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
     * @param string $comissao
     * @param string $validade      mensal|anual
     * @param string $validade      mensal|anual
     * @return boolean|Entity \Livraria\Entity\Taxa
     */
    public function findTaxaVigente($seguradora, $atividade, $date, $comissao, $validade='anual', $cob='01'){
        //Pegar classeAtividade correspondente vigente na data
        $classeAtividade = $this->getEntityManager()
                ->getRepository('Livraria\Entity\ClasseAtividade')
                ->findClasseVigente($seguradora, $atividade, $date);
        
        if(!is_object($classeAtividade)){
            return FALSE;
        }
        //Converter string data em objeto datetime
        if(!is_object($date)){
            $d = explode("/", $date);
            $date = new \DateTime($d[1] . '/' . $d[0] . '/' . $d[2]);
        }
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('t,s,c')
                ->from('Livraria\Entity\Taxa', 't')
                ->join('t.seguradora', 's')
                ->join('t.classe', 'c')
                ->where(" t.seguradora = :seguradora
                    AND   t.classe = :classe
                    AND   t.inicio <= :inicio
                    AND   t.ocupacao = :ocupacao
                    AND   t.validade = :validade
                    AND   t.comissao = :comissao
                    AND   t.tipoCobertura = :cobertura
                    ")
                ->setParameter('seguradora', $seguradora)
                ->setParameter('classe', $classeAtividade->getClasseTaxas()->getId())
                ->setParameter('inicio', $date)
                ->setParameter('ocupacao', $classeAtividade->getAtividade()->getOcupacao())
                ->setParameter('validade', $validade)
                ->setParameter('comissao', $comissao)
                ->setParameter('cobertura', $cob)
                ->setMaxResults(1)
                ->orderBy('t.inicio', 'DESC')
                ->getQuery()
                ;
        $resul = $query->getResult();
        
        return empty($resul) ? FALSE : $resul[0];
    }
}

