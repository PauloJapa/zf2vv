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
                    ->setParameter('status', 'A');
            return $query;
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
                    $where .= ' AND l.inicio <= :dataF';
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
                ->setParameters($parameters);
        
        if(isset($parameters['administradora'])){
            $query->orderBy('l.criadoEm', 'DESC');
        }
        
        return $query;
    }    
    
    /**
     * Faz a atualização de todos os seguros com a nova referencia
     * @param integer $id
     * @param string  $setRefImovel
     * @return boolean
     */
    public function cascateUpdateRefImovel($id='', $setRefImovel=''){
        if(empty($id) OR empty($setRefImovel)){
            return FALSE;
        }
        
        $this->getEntityManager()
        ->createQueryBuilder()
        ->update('Livraria\Entity\Orcamento', 'o')
        ->set('o.refImovel', ':ref')
        ->where('o.imovel = :id')
        ->setParameter('ref', $setRefImovel)
        ->setParameter('id', $id)
        ->getQuery()
        ->execute();
        
        return TRUE;
        
    }
}

