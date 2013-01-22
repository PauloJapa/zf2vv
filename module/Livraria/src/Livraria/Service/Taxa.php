<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * Taxa
 * Faz o CRUD da tabela Taxa no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Taxa extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Taxa";
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        //Pegar um referencia da classe para classe
        $classe = $this->em->getReference("Livraria\Entity\Classe", $data['classe']);
        $inicio = new \DateTime($data['inicio']);
        $fim    = new \DateTime($data['fim']);
        unset($data['classe']);
        unset($data['inicio']);
        unset($data['fim']);
        // Criar nova entidade classe
        $entity = new $this->entity($data);
        
        $entity->setClasse($classe);
        $entity->setInicio($inicio);
        $entity->setFim($fim);
        
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
        //Pegar um referencia da classe para classe
        $classe = $this->em->getReference("Livraria\Entity\Classe", $data['classe']);
        $inicio = new \DateTime($data['inicio']);
        $fim    = new \DateTime($data['fim']);
        unset($data['classe']);
        unset($data['inicio']);
        unset($data['fim']);
        
        $entity = $this->em->getReference($this->entity, $data['id']);
        $entity = Configurator::configure($entity,$data);
        $entity->setClasse($classe);
        $entity->setInicio($inicio);
        $entity->setFim($fim);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
    }
}
