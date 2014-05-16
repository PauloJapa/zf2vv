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
        if(empty($filtros)){
            return FALSE;
        }
        //Monta clasula where e seus paramentros
        $this->where = 'i.id <> :id';
        $this->parameters['id']  = 'null';
        
        if(isset($filtros['rua'])){
            $this->where .= ' AND i.rua LIKE :rua';
            $this->parameters['rua']  = $filtros['rua'] . '%';            
        }
        
        if(isset($filtros['refImovel'])){
            $this->where .= ' AND i.refImovel LIKE :refImovel';
            $this->parameters['refImovel']  = $filtros['refImovel'] . '%';            
        }
        
        if(isset($filtros['locador'])){
            $this->where .= ' AND i.locador = :locador';
            $this->parameters['locador']  = $filtros['locador'];            
        }
        
        if(isset($filtros['locatario'])){
            $this->where .= ' AND i.locatario = :locatario';
            $this->parameters['locatario']  = $filtros['locatario'];            
        }
                
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('i,ld,lt')
                ->from('Livraria\Entity\Imovel', 'i')
                ->join('i.locador', 'ld')
                ->join('i.locatario', 'lt')
                ->where($this->where)
                ->orderBy('i.rua')
                ->setParameters($this->parameters)
                ->getQuery();
        $list = $query->getResult();
        
        if (!empty($list))
            return $query;
        
        //Nova pesquisa pesquisando por qualquer ocorrencia  
        if(isset($filtros['rua'])){
            $this->parameters['rua'] = '%' . $filtros['rua'] . '%';            
        }else{
            //Apenas para retornar uma query vazia
            $this->parameters['id'] = '0';                        
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('i,ld,lt')
                ->from('Livraria\Entity\Imovel', 'i')
                ->join('i.locador', 'ld')
                ->join('i.locatario', 'lt')
                ->where($this->where)
                ->orderBy('i.rua')
                ->setParameters($this->parameters)
                ->getQuery();
        
        return $query;
    }
    
}

