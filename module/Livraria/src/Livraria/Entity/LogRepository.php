<?php

namespace Livraria\Entity;

/**
 * LogRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogRepository extends AbstractRepository {

    
    public function findLogs($filtros=[],$operadores=[]){
        if(empty($filtros)){
            $query = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('l,u')
                    ->from('Livraria\Entity\Log', 'l')
                    ->join('l.user', 'u')
                    ->orderBy('l.data', 'DESC');
            return $query;
        }
        $where = 'l.id IS NOT NULL';
        $parameters = [];
        foreach ($filtros as $key => $filtro) {
            switch ($key) {
                case 'dataI':
                    $where .= ' AND l.data >= :dataI';
                    $parameters['dataI'] = $this->dateToObject($filtro);
                    break;
                case 'dataF':
                    $where .= ' AND l.data <= :dataF';
                    $parameters['dataF'] = $this->dateToObject($filtro);
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
                ->select('l,u')
                ->from('Livraria\Entity\Log', 'l')
                ->join('l.user', 'u')
                ->where($where)
                ->setParameters($parameters)
                ->orderBy('l.data', 'DESC');
        
        return $query;
    }
    
    public function findLikeDePara($obs) {
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('e')
                ->from('Livraria\Entity\Log', 'e')
                ->where('e.dePara like :dePara')
                ->setParameter('dePara', $obs . '%')
                ;
        
        $resul = $query->getQuery()->getResult();
        if(empty($resul)){
            return false;
        }
        return $resul[0];
    }
}

