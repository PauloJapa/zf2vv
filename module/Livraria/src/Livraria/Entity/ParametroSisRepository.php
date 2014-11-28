<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * ParametroSisRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ParametroSisRepository extends EntityRepository {
    
    public function findKey($key){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('p')
                ->from('Livraria\Entity\ParametroSis', 'p')
                ->where(" p.key LIKE :key")
                ->setParameter('key', $key)
             //   ->setMaxResults(20)
                ->getQuery()
                ; 
        return $query->getResult();
    }
    
    public function fetchPairs($key, $first = true) {
        $entities = $this->findKey($key);
        
        if ($first){
            $array = array('' => 'Selecione na lista');
        }else{
            $array = array();            
        }
        if(!is_array($entities)){
            return $array;
        }
        
        foreach($entities as $entity) {
            $array[$entity->getConteudo()] = $entity->getDescricao();
        }
        
        return $array;
    }
}

