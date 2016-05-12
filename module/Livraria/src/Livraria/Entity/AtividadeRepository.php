<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * AtividadeRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class AtividadeRepository extends EntityRepository {

    /** 
     * Buscar no banco todos registros para colocar no select do form
     * @return Array com a lista de registro  
     */ 
    public function fetchPairs() {
        $entities = $this->findAll();
        
        $array = array('' => 'Selecione na lista');
        
        foreach($entities as $entity) {
            $array[$entity->getId()] = $entity->getDescricao();
        }
        
        return $array;
    }
     
    /**
     * Auto complete em ajax esta função retorna as entitys encontradas
     * com a ocorrencia passada por parametro
     * @param string $atividade
     * @return \Livraria\Entity\Atividade
     */
    public function autoComp($atividade,$ocupacao='',$seguradora=''){
        if(!empty($atividade)){
            $where = 'u.descricao LIKE :atividade';
            $param['atividade'] = $atividade;            
        }
        if(!empty($ocupacao)){
            $where .= ' AND u.ocupacao LIKE :ocupacao';
            $param['ocupacao'] = $ocupacao;
        }
        if(!empty($seguradora)){
            $where .= ' AND u.seguradoraId = :seguradora';
            $param['seguradora'] = $seguradora;
        }
        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from('Livraria\Entity\Atividade', 'u')
            ->where($where)
            ->setParameters($param)
            ->andWhere('u.status LIKE :status')->setParameter('status', 'A')
            ->getQuery();
        
        return $query->getResult();
    }   
    
    /**
     * Pesquisa conforme paramentro passados:
     * Descrição
     * @param array $filtros
     * @return array
     */
    public function pesquisa(array $filtros){
        //Monta busca por descrição acresenta o coringa '%'
        $where = 'u.id IS NOT NULL';
        if(isset($filtros['nome']) AND !empty($filtros['nome'])){
            $where .= ' AND u.descricao LIKE :descricao';
            $paramentros['descricao'] = '%'. $filtros['nome'] . '%';
        }
        
        if(isset($filtros['ocupacao']) AND !empty($filtros['ocupacao'])){
            $where .= ' AND u.ocupacao = :ocupacao';
            $paramentros['ocupacao'] = $filtros['ocupacao'];
        }
        
        if(isset($filtros['status']) AND !empty($filtros['status'])){
            $where .= ' AND u.status = :status';
            $paramentros['status'] = $filtros['status'];
        }
        
        if(isset($filtros['seguradoraId']) AND !empty($filtros['seguradoraId'])){
            $where .= ' AND u.seguradoraId = :seguradoraId';
            $paramentros['seguradoraId'] = $filtros['seguradoraId'];
        }
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Atividade', 'u')
                ->orderBy('u.descricao');
        
        if(isset($paramentros)){
            $query->where($where)->setParameters($paramentros);
        }
        
        return $query;
    }
}

