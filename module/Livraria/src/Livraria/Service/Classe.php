<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * Classe
 * Faz o CRUD da tabela Classe no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Classe extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Classe";
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        //Pegar um referencia da seguradora para classe
        $seguradora = $this->em->getReference("Livraria\Entity\Seguradora", $data['seguradora']);
        unset($data['seguradora']);
        
        if ($user = $this->getIdentidade())
            $data['userIdCriado'] = $user->getId();
        // Criar nova entidade classe
        $entity = new $this->entity($data);
        
        $entity->setSeguradora($seguradora);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;        
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */    
    public function update(array $data) {
        //Pegar um referencia da seguradora para classe
        $seguradora = $this->em->getReference("Livraria\Entity\Seguradora", $data['seguradora']);
        unset($data['seguradora']);
        
        if ($user = $this->getIdentidade())
            $data['userIdAlterado'] = $user->getId();
        
        $entity = $this->em->getReference($this->entity, $data['id']);
        $entity = Configurator::configure($entity,$data);
        
        $entity->setSeguradora($seguradora);
        
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
    }
}
