<?php

namespace Livraria\Entity;

/**
 * LogFechadosRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogFechadosRepository extends AbstractRepository {

    public function findLogFechados($filtros=[],$operadores=[]){
        if(empty($filtros)){
            $query = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('lf,u')
                    ->from('Livraria\Entity\LogFechados', 'lf')
                    ->join('lf.user', 'u')
                    ->orderBy('lf.data', 'DESC')
                    ;
            return $query;
        }
        
        $where = 'lf.id IS NOT NULL';
        $parameters = [];
        foreach ($filtros as $key => $filtro) {
            switch ($key) {
                case 'dataI':
                    $where .= ' AND lf.data >= :dataI';
                    $parameters['dataI'] = $this->dateToObject($filtro);
                    break;
                case 'dataF':
                    $where .= ' AND lf.data <= :dataF';
                    $parameters['dataF'] = $this->dateToObject($filtro);
                    break;
                default:
                    $op = (isset($operadores[$key])) ? $operadores[$key] : '=';
                    $where .= ' AND lf.' . $key . ' ' . $op . ' :' . $key;
                    $parameters[$key] = $filtro;
                    break;
            }
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('lf,u')
                ->from('Livraria\Entity\LogFechados', 'lf')
                ->join('lf.user', 'u')
                ->where($where)
                ->setParameters($parameters)
                ->orderBy('lf.data', 'DESC');
        return $query;
    }
}

