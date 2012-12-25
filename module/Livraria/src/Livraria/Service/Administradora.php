<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
use Livraria\Entity\Bairro;
use Livraria\Entity\Cidade;

class Administradora extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Administradora";
        $this->entityEnd = "Livraria\Entity\Endereco";
    }
    
    public function insert(array $data) {
        // Criando nova entidade endereço
        $entityEnd = new $this->entityEnd($data);
        
        //Caso a bairro não foi escolhido da lista procura o id pelo nome 
        if(empty($data['bairro'])){ 
            $repository = $this->em->getRepository("Livraria\Entity\Bairro");
            $bairro = $repository->findOneByNome($data['bairroDesc']);
            if(!$bairro){
                $bairro = new Bairro(array('nome' => $data['bairroDesc']));
                $this->em->persist($bairro);
            }
        }else{            
            $bairro = $this->em->getReference("Livraria\Entity\Bairro", $data['bairro']);
        }
        $entityEnd->setBairro($bairro);
        
        //Caso a cidade não foi escolhida da lista procura o id pelo nome 
        if(empty($data['cidade'])){ 
            $repository = $this->em->getRepository("Livraria\Entity\Cidade");
            $cidade = $repository->findOneByNome($data['cidadeDesc']);
            if(!$cidade){
                $cidade = new Cidade(array('nome' => $data['cidadeDesc']));
                $this->em->persist($cidade);
            }
        }else{            
            $cidade = $this->em->getReference("Livraria\Entity\Cidade", $data['cidade']);
        }
        $entityEnd->setCidade($cidade);
        
        $estado = $this->em->getReference("Livraria\Entity\Estado", $data['estado']);
        $entityEnd->setEstado($estado);
        
        $pais = $this->em->getReference("Livraria\Entity\Pais", $data['pais']);
        $entityEnd->setPais($pais);
        // Persistir endereço para depois criar nova administradora
        $this->em->persist($entityEnd);
        
        // Criar nova entidade administradora
        $entity = new $this->entity($data);
        
        $entity->setEndereco($entityEnd);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
        
    }
    
    public function update(array $data) {
        $entity = $this->em->getReference($this->entity, $data['id']);
        $entity = Configurator::configure($entity,$data);
        
        $endereco = $this->em->getReference($this->entityEnd, $data['idEnde']);
        $endereco = Configurator::configure($endereco,$data);
        //Caso a bairro não foi escolhido da lista procura o id pelo nome 
        if(empty($data['bairro'])){ 
            $repository = $this->em->getRepository("Livraria\Entity\Bairro");
            $bairro = $repository->findOneByNome($data['bairroDesc']);
            if(!$bairro){
                $bairro = new Bairro(array('nome' => $data['bairroDesc']));
                $this->em->persist($bairro);
            }
        }else{            
            $bairro = $this->em->getReference("Livraria\Entity\Bairro", $data['bairro']);
        }
        $endereco->setBairro($bairro);
        
        //Caso a cidade não foi escolhida da lista procura o id pelo nome 
        if(empty($data['cidade'])){ 
            $repository = $this->em->getRepository("Livraria\Entity\Cidade");
            $cidade = $repository->findOneByNome($data['cidadeDesc']);
            if(!$cidade){
                $cidade = new Cidade(array('nome' => $data['cidadeDesc']));
                $this->em->persist($cidade);
            }
        }else{            
            $cidade = $this->em->getReference("Livraria\Entity\Cidade", $data['cidade']);
        }
        $endereco->setCidade($cidade);
        
        $estado = $this->em->getReference("Livraria\Entity\Estado", $data['estado']);
        $endereco->setEstado($estado);
        
        $pais = $this->em->getReference("Livraria\Entity\Pais", $data['pais']);
        $endereco->setPais($pais);
        $this->em->persist($endereco);
        
        $entity->setEndereco($endereco);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
    }
}
