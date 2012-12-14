<?php 


namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class CidadeRepository extends EntityRepository {

    public function fetchPairs() {
        $entities = $this->findAll();
        
        $array = array();
        
        foreach($entities as $entity) {
            $array[$entity->getId()] = $entity->getNome();
        }
        
        if(empty($array))$array[' ']= '';
        
        return $array;
    }
    
    public function autoComp($cidade){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Cidade', 'u')
                ->where("u.nome LIKE :cidade")
                ->setParameter('cidade', $cidade)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
}
