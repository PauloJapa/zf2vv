<?php

namespace Livraria\Entity;

/**
 * LogRenovacaoRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogRenovacaoRepository extends AbstractRepository {

    
    public function findLogRenovacao($filtros=[],$operadores=[]){
        if(empty($filtros)){
            $query = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('lr,u')
                    ->from('Livraria\Entity\LogRenovacao', 'lr')
                    ->join('lr.user', 'u')
                    ->orderBy('lr.data', 'DESC');
            return $query;
        }

        $where = 'lr.id IS NOT NULL';
        $parameters = [];
        foreach ($filtros as $key => $filtro) {
            switch ($key) {
                case 'dataI':
                    $where .= ' AND lr.data >= :dataI';
                    $parameters['dataI'] = $this->dateToObject($filtro);
                    break;
                case 'dataF':
                    $where .= ' AND lr.data <= :dataF';
                    $parameters['dataF'] = $this->dateToObject($filtro);
                    break;
                default:
                    $op = (isset($operadores[$key])) ? $operadores[$key] : '=';
                    $where .= ' AND lr.' . $key . ' ' . $op . ' :' . $key;
                    $parameters[$key] = $filtro;
                    break;
            }
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('lr,u')
                ->from('Livraria\Entity\LogRenovacao', 'lr')
                ->join('lr.user', 'u')
                ->where($where)
                ->setParameters($parameters)
                ->orderBy('lr.data', 'DESC');        
        return $query;
    }
}

