<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * ImovelRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ImovelRepository extends EntityRepository {

    
    public function autoComp($locador){
        if(empty($locador))
            return false;
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Imovel', 'u')
                ->where("u.locador = :locador")
                ->setParameter('locador', $locador)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
    /**
     * Pesquisa conforme paramentro passados:
     * rua
     * @param array $filtros
     * @return array
     */
    public function pesquisa(array $filtros){
        if (empty($filtros['rua']))
            return [];
        
        //Monta clasula where e seus paramentros
        $where = "(i.rua LIKE :rua )";
        $paramentros['rua'] = $filtros['rua'] . '%';
                
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('i,ld,lt')
                ->from('Livraria\Entity\Imovel', 'i')
                ->join('i.locador', 'ld')
                ->join('i.locatario', 'lt')
                ->where($where)
                ->orderBy('i.rua')
                ->setParameters($paramentros)
                ->getQuery();
        $list = $query->getResult();
        
        if (!empty($list))
            return $list;
        
        //Nova pesquisa pesquisando por qualquer ocorrencia        
        $paramentros['rua'] = '%' . $filtros['rua'] . '%';
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('i,ld,lt')
                ->from('Livraria\Entity\Imovel', 'i')
                ->join('i.locador', 'ld')
                ->join('i.locatario', 'lt')
                ->where($where)
                ->orderBy('i.rua')
                ->setParameters($paramentros)
                ->getQuery();
        
        return $query->getResult();
    }
    
}

