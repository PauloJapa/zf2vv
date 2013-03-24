<?php

namespace Livraria\Entity;

/**
 * OrcamentoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class OrcamentoRepository extends AbstractRepository {

    public function findOrcamento($filtros=[],$operadores=[]){
        if(empty($filtros)){
            $query = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('l,u,i,ld','lc','ad','at')
                    ->from('Livraria\Entity\Orcamento', 'l')
                    ->join('l.user', 'u')
                    ->join('l.imovel', 'i')
                    ->join('l.locador', 'ld')
                    ->join('l.locatario', 'lc')
                    ->join('l.administradora', 'ad')
                    ->join('l.atividade', 'at')
                    ->where('l.status = :status')
                    ->setParameter('status', 'A')
                    ->orderBy('l.criadoEm', 'DESC')
                    ->getQuery();
            return $query->getResult();
        }
        $where = 'l.id IS NOT NULL';
        $parameters = [];
        foreach ($filtros as $key => $filtro) {
            switch ($key) {
                case 'dataI':
                    $where .= ' AND l.inicio >= :dataI';
                    $parameters['dataI'] = $this->dateToObject($filtro);
                    break;
                case 'dataF':
                    $where .= ' AND l.fim <= :dataF';
                    $parameters['dataF'] = $this->dateToObject($filtro);
                    break;
                case 'status':
                    if($filtro != 'T'){
                        $op = (isset($operadores[$key])) ? $operadores[$key] : '=';
                        $where .= ' AND l.' . $key . ' ' . $op . ' :' . $key;
                        $parameters[$key] = $filtro;
                    }
                    break;
                default:
                    $op = (isset($operadores[$key])) ? $operadores[$key] : '=';
                    $where .= ' AND l.' . $key . ' ' . $op . ' :' . $key;
                    $parameters[$key] = $filtro;
                    break;
            }
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('l,u,i,ld','lc','ad','at')
                ->from('Livraria\Entity\Orcamento', 'l')
                ->join('l.user', 'u')
                ->join('l.imovel', 'i')
                ->join('l.locador', 'ld')
                ->join('l.locatario', 'lc')
                ->join('l.administradora', 'ad')
                ->join('l.atividade', 'at')
                ->where($where)
                ->setParameters($parameters)
                ->orderBy('l.criadoEm', 'DESC')
                ->getQuery();
        
        return $query->getResult();
    }    
}

