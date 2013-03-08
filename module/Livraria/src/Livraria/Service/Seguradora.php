<?php


namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;

class Seguradora extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Seguradora";
    }
    
    public function insert(array $data) {
        // Criando novo entidade seguradora
        $entity = new $this->entity($data);        
        
        //Pegando o servico endereco e inserindo endereco da seguradora
        $servicender = new Endereco($this->em);        
        $endereco = $servicender->insert($data);
        //Fazendo a ligação do endereco inserido com cad seguradora
        $entity->setEnderecos($endereco);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return true;
        
    }

    public function update(array $data) {
        $entity = $this->em->getReference($this->entity, $data['id']);
        
        //Pegando o servico endereco e inserindo endereco do usuario
        $servicender = new Endereco($this->em);        
        $endereco = $servicender->update($data);
        //Fazendo a ligação do endereco inserido com cad usuario
        $entity->setEnderecos($endereco);
        
        $entity = Configurator::configure($entity, $data);

        $this->em->persist($entity);
        $this->em->flush();

        return true;
    }
}