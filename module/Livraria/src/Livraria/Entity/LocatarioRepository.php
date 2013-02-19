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
     * @param string $locador
     * @param string $cpf
     * @param string $cnpj
     * @return array
     */
    public function autoComp($locatario, $cpf='', $cnpj=''){
        
        $locatario = trim($locatario);
        
        if(empty($cpf))
            $cpf = $locatario;
        
        if(empty($cnpj))
            $cnpj = $locatario;
        
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\Locatario', 'u')
                ->where("u.nome LIKE :locatario OR u.cpf LIKE :cpf OR u.cnpj LIKE :cnpj")
                ->setParameter('locatario', $locatario)
                ->setParameter('cpf', $cpf)
                ->setParameter('cnpj', $cnpj)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }
    
}

