<?php

namespace Livraria\Entity;

/**
 * LogOrcamentoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogOrcamentoRepository extends AbstractRepository {

    
    public function findLogOrcamento($filtros=[],$operadores=[]){
        if(empty($filtros)){
            $query = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('lo,u')
                    ->from('Livraria\Entity\LogOrcamento', 'lo')
                    ->join('lo.user', 'u')
                    ->orderBy('lo.data', 'DESC');
            return $query;
        }
        $where = 'lo.id IS NOT NULL';
        $parameters = [];
        foreach ($filtros as $key => $filtro) {
            switch ($key) {
                case 'dataI':
                    $where .= ' AND lo.data >= :dataI';
                    $parameters['dataI'] = $this->dateToObject($filtro);
                    break;
                case 'dataF':
                    $where .= ' AND lo.data <= :dataF';
                    $parameters['dataF'] = $this->dateToObject($filtro);
                    break;
                default:
                    $op = (isset($operadores[$key])) ? $operadores[$key] : '=';
                    $where .= ' AND lo.' . $key . ' ' . $op . ' :' . $key;
                    $parameters[$key] = $filtro;
                    break;
            }
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('lo,u')
                ->from('Livraria\Entity\LogOrcamento', 'lo')
                ->join('lo.user', 'u')
                ->where($where)
                ->setParameters($parameters)
                ->orderBy('lo.data', 'DESC');
        return $query;
    }
}

