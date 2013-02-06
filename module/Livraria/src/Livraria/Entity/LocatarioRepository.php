<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * LocatarioRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LocatarioRepository extends EntityRepository {

    
    /**
     * Auto complete em ajax esta função retorna as entitys encontradas
     * com a ocorrencia passada por parametro
     * @param string $locatario
     * @return \Livraria\Entity\Locatario
     */
    public function autoComp($locatario){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Locatario', 'u')
                ->where("u.nome LIKE :locatario")
                ->setParameter('locatario', $locatario)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
}

