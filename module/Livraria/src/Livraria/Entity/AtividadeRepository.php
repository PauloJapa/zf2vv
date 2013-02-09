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
    public function autoComp($atividade){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Atividade', 'u')
                ->where("u.descricao LIKE :atividade")
                ->setParameter('atividade', $atividade)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }   
}

