<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * ComissaoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ComissaoRepository extends EntityRepository {

    /*
     * Query que vai trazer a comissão da data passada por parametro
     *  SELECT * FROM `comissao` 
        WHERE inicio <= 20130601 
        AND administradora_id = 1
        ORDER BY inicio DESC    
        LIMIT 1 
     */
    public function findComissaoVigente($administradora, $date){
        //Converter string data em objeto datetime
        if(!is_object($date)){
            $date = explode("/", $date);
            $date = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        }
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('c')
                ->from('Livraria\Entity\Comissao', 'c')
                ->where(" c.administradora = :administradora")
                ->setParameter('administradora', $administradora)
                ->orderBy('c.inicio', 'DESC')
                ->getQuery()
        ;
        $rs = $query->getResult();
        $msg = '<h2>Erro ao procura comissao; </h2>';
        $msg .= ' adm codigo ' . ($administradora) . '<br>';
        $msg .= ' data de inicio ' . $date->format('d/m/Y') ;
        /* @var $ent \Livraria\Entity\Comissao */
        foreach ($rs as $ent) {
            if($date >= $ent->getInicio('obj')){
                return $ent;
            }            
        }
        
        throw new \Exception($msg);
    }
    
}

