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
    public function findTaxaVigente($seguradora, $atividade, $date, $comissao, $validade='anual', $cob='01', $adm=0){

        
        //Pegar classeAtividade correspondente vigente na data
        /* @var $classeAtividade \Livraria\Entity\ClasseAtividade */
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
        // somente para lello ajustar data de inicio para mes 06 que a vigencia das taxas corretas para o calculo
        $date2 = false;
        if($adm == 3234 and $date->format('Y') == '2016' and $date->format('m') < '06'){
            $date2 = clone $date;
            $add = 6 - (int)$date->format('m');
            $date2->add(new \DateInterval('P' . $add . 'M'));
        }
        
        //Acertar tipo de cobertura
        if ($classeAtividade->getAtividade()->getOcupacao() == 2 AND $cob != '02'){
            $cob = '02';
//            echo 'Tipo de cobertura para residencial somente predio + conteudo <br />';
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
                ->setParameter('inicio', (is_object($date2))? $date2 : $date)
                ->setParameter('ocupacao', $classeAtividade->getAtividade()->getOcupacao())
                ->setParameter('validade', $validade)
                ->setParameter('comissao', $comissao)
                ->setParameter('cobertura', $cob)
                ->setMaxResults(1)
                ->orderBy('t.inicio', 'DESC')
                
                ;
        $resul = $query->getQuery()->getOneOrNullResult();
        
        if($resul){
//            echo '<pre>'
//            ,var_dump($seguradora)
//            ,var_dump($classeAtividade->getClasseTaxas()->getId())
//            ,var_dump((is_object($date2))? $date2 : $date)
//            ,var_dump($classeAtividade->getAtividade()->getOcupacao())
//            ,var_dump($validade)
//            ,var_dump($comissao)
//            ,var_dump($cob)
//            ,var_dump($resul->toArray())
//            ,'</pre>';
            
            return $resul;
        }else{
            return FALSE;
        }
    }
}

