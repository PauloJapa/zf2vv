<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;

class User extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\User";
        $this->entityEnd = "Livraria\Entity\Endereco";
    }

    public function update(array $data) {
        $entity = $this->em->getReference($this->entity, $data['id']);
        
        //Pegando o servico endereco e inserindo endereco do usuario
        $servicender = new Endereco($this->em);        
        $endereco = $servicender->update($data);
        //Fazendo a ligação do endereco inserido com cad usuario
        $entity->setEndereco($endereco);
        
        //Pegar um referencia da adiministrado do usuario
        $administradora = $this->em->getReference("Livraria\Entity\Administradora", $data['administradora']);
        $entity->setAdministradora($administradora);

        if (empty($data['password']))
            unset($data['password']);

        unset($data['administradora']);
        $entity = Configurator::configure($entity, $data);

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

}
