<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {

    public function findByEmailAndPassword($email, $password) {
        $user = $this->findOneByEmail($email);
        if ($user) {
            $hashSenha = $user->encryptPassword($password);
            if ($hashSenha == $user->getPassword()) {
                return $user;
            }
        }        
        return false;
    }
    
    public function autoComp($user){
        $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('u')
                ->from('Livraria\Entity\User', 'u')
                ->where("u.nome LIKE :user")
                ->setParameter('user', $user)
                ->setMaxResults(20)
                ->getQuery()
                ;
        return $query->getResult();
    }

}
