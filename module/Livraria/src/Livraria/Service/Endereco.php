<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
use Livraria\Entity\Bairro;
use Livraria\Entity\Cidade;

class Endereco extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Endereco";
    }
    
    public function insert(array $data) {
        $entity = new $this->entity($data);
        
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
        $entity->setBairro($bairro);
        
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
        $entity->setCidade($cidade);
        
        if(empty($data['estado']))
            $data['estado'] = "28";
        $estado = $this->em->getReference("Livraria\Entity\Estado", $data['estado']);
        $entity->setEstado($estado);
        
        if(empty($data['pais']))
            $data['pais'] = "1";
        $pais = $this->em->getReference("Livraria\Entity\Pais", $data['pais']);
        $entity->setPais($pais);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
        
    }
    
    public function update(array $data2) {
        //Converter idEnde para apenas id para configurar(hidratar) a classe
        $data = $data2;
        $data['id'] = $data['idEnde'];
        unset($data['idEnde']);
        //Pega referencia do registro 
        $entity = $this->em->getReference($this->entity, $data['id']);
        //Faz todos os sets de endereco
        $entity = Configurator::configure($entity,$data);
                
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
        $entity->setBairro($bairro);
        
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
        $entity->setCidade($cidade);
        
        if(empty($data['estado']))
            $data['estado'] = "28";
        $estado = $this->em->getReference("Livraria\Entity\Estado", $data['estado']);
        $entity->setEstado($estado);
        
        if(empty($data['pais']))
            $data['pais'] = "1";
        $pais = $this->em->getReference("Livraria\Entity\Pais", $data['pais']);
        $entity->setPais($pais);
        
        $this->em->persist($entity);
        $this->em->flush();
        
        return $entity;
    }
}
