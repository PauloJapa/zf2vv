<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * ClasseRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ClasseRepository extends EntityRepository {

    /** 
     * Buscar no banco todos registros para colocar no select do form
     * @return Array com a lista de registro  
     */ 
    public function fetchPairs() {
        $entities = $this->findAll();
        
        $array = array('' => 'Selecione na lista');
        
        foreach($entities as $entity) {
            $array[$entity->getId()] = $entity->getCod() . ' - ' . $entity->getDescricao();
        }
        
        return $array;
    }

    /** 
     * Busca para o auto complete da tela em ajax
     * @param String $classe para pesquisar ocorrencias
     * @return Entidades positivas da busca ou falso se nada encontrar 
     */     
    public function autoComp($classe){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Classe', 'u')
                ->where("u.nome LIKE :classe")
                ->setParameter('classe', $classe)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
}

