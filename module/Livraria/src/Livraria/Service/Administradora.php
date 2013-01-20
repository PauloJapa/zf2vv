<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;

class Administradora extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Administradora";
        $this->entityEnd = "Livraria\Entity\Endereco";
    }
    
    public function insert(array $data) {
        
        // Criar nova entidade administradora
        $entity = new $this->entity($data);
        
        //Pegar um referencia da seguradora para administradora
        $seguradora = $this->em->getReference("Livraria\Entity\Seguradora", $data['seguradora']);
        $entity->setSeguradora($seguradora);
        
        //Pegando o servico endereco e inserindo endereco do Administradora
        $servicender = new Endereco($this->em);        
        $endereco = $servicender->insert($data);
        //Fazendo a ligação do endereco inserido com cad Administradora
        $entity->setEndereco($endereco);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
        
    }
    
    public function update(array $data) {
        $entity = $this->em->getReference($this->entity, $data['id']);
        $entity = Configurator::configure($entity,$data);
        
        //Pegar um referencia da seguradora para administradora
        $seguradora = $this->em->getReference("Livraria\Entity\Seguradora", $data['seguradora']);
        $entity->setSeguradora($seguradora);
        
        //Pegando o servico endereco e inserindo endereco do Administradora
        $servicender = new Endereco($this->em);        
        $endereco = $servicender->update($data);
        //Fazendo a ligação do endereco inserido com cad Administradora
        $entity->setEndereco($endereco);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
    }
}
