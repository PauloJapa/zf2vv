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
                ->select('i,e')
                ->from('Livraria\Entity\Imovel', 'i')
                ->join('i.endereco', 'e')
                ->join('e.bairro', 'b')
                ->join('e.cidade', 'c')
                ->join('e.estado', 'uf')
                ->join('e.pais', 'p')
                ->where("i.locador = :locador")
                ->setParameter('locador', $locador)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
    public function autoCompRua($rua){
        if(empty($rua))
            return false;
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('i,e')
                ->from('Livraria\Entity\Imovel', 'i')
                ->join('i.endereco', 'e')
                ->join('e.bairro', 'b')
                ->join('e.cidade', 'c')
                ->join('e.estado', 'uf')
                ->join('e.pais', 'p')
                ->where("i.rua LIKE :rua")
                ->setParameter('rua', $rua)
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
            return $query;
        
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
        
        return $query;
    }
    
}

