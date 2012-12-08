<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;

class Endereco extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Endereco";
    }
    
    public function insert(array $data) {
        $entity = new $this->entity($data);
        
        $bairro = $this->em->getReference("Livraria\Entity\Bairro", $data['bairro']);
        $entity->setBairro($bairro);
        
        $cidade = $this->em->getReference("Livraria\Entity\Cidade", $data['cidade']);
        $entity->setCidade($cidade);
        
        $estado = $this->em->getReference("Livraria\Entity\Estado", $data['estado']);
        $entity->setEstado($estado);
        
        $pais = $this->em->getReference("Livraria\Entity\Pais", $data['pais']);
        $entity->setPais($pais);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
        
    }
    
    public function update(array $data) {
        $entity = $this->em->getReference($this->entity, $data['idEnde']);
        $entity = Configurator::configure($entity,$data);
        
        $bairro = $this->em->getReference("Livraria\Entity\Bairro", $data['bairro']);
        $entity->setBairro($bairro);
        
        $cidade = $this->em->getReference("Livraria\Entity\Cidade", $data['cidade']);
        $entity->setCidade($cidade);
        
        $estado = $this->em->getReference("Livraria\Entity\Estado", $data['estado']);
        $entity->setEstado($estado);
        
        $pais = $this->em->getReference("Livraria\Entity\Pais", $data['pais']);
        $entity->setPais($pais);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
    }
}
