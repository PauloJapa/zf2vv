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
    
    /**
     * Faz as conversões de id para entity para o doctrine valida
     * Abstração das actions new e edit
     */
    public function setReferences(){
        //Caso a bairro não foi escolhido da lista procura o id pelo nome 
        if(!isset($this->data['cep'])) $this->data['cep'] ='';
        if(!isset($this->data['rua'])) $this->data['rua'] ='';
        if(!isset($this->data['numero'])) $this->data['numero'] ='';
        if(!isset($this->data['bairro'])) $this->data['bairro'] ='';
        if(!isset($this->data['bairroDesc'])) $this->data['bairroDesc'] ='';
        if(!isset($this->data['cidade'])) $this->data['cidade'] ='';
        if(!isset($this->data['cidadeDesc'])) $this->data['cidadeDesc'] ='';
        if(!isset($this->data['estado'])) $this->data['estado'] ='';
        if(!isset($this->data['pais'])) $this->data['pais'] ='';
        if(empty($this->data['bairro'])){ 
            $repository = $this->em->getRepository("Livraria\Entity\Bairro");
            $this->data['bairro'] = $repository->findOneByNome($this->data['bairroDesc']);
            if(!$this->data['bairro']){
                $this->data['bairro'] = new Bairro(array('nome' => $this->data['bairroDesc']));
                $this->em->persist($this->data['bairro']);
            }
        }else{            
            $this->data['bairro'] = $this->em->getReference("Livraria\Entity\Bairro", $this->data['bairro']);
        }
        
        //Caso a cidade não foi escolhida da lista procura o id pelo nome 
        if(empty($this->data['cidade'])){ 
            $repository = $this->em->getRepository("Livraria\Entity\Cidade");
            $this->data['cidade'] = $repository->findOneByNome($this->data['cidadeDesc']);
            if(!$this->data['cidade']){
                $this->data['cidade'] = new Cidade(array('nome' => $this->data['cidadeDesc']));
                $this->em->persist($this->data['cidade']);
            }
        }else{     
            if($this->data['cidade'] > 500){
                echo 'Por favor click em voltar e escolha uma cidade da lista de pesquisa do imovel!!';
                die;
            }
            $this->data['cidade'] = $this->em->getReference("Livraria\Entity\Cidade", $this->data['cidade']);
        }
        
        if(empty($this->data['estado'])) $this->data['estado'] = "1";
        //Pega uma referencia do registro da tabela estado
        $this->idToReference('estado', "Livraria\Entity\Estado");
        
        //Pega uma referencia do registro da tabela pais
        if(empty($this->data['pais'])) $this->data['pais'] = "1";
        $this->idToReference('pais', "Livraria\Entity\Pais");
    }
    
    public function insert(array $data, $flush=FALSE) {
        $this->data = $data;
        unset($this->data['id']);
        $this->data['idEnde'] = '';
        
        $this->setReferences();
        
        /* @var $entity \Livraria\Entity\Endereco */
        $entity = new $this->entity($this->data);
        $entity->getId();
        $this->em->persist($entity);
        
        if ($flush) {
            $this->em->flush();
        }

        return $entity;
        
    }
    
    public function update(array $data, $flush=FALSE) {
        $this->data = $data;
        unset($this->data['id']);
        
        if($this->data['idEnde'] == '1' AND !empty($this->data['rua'])){            
            $this->dePara .= $this->diffAfterBefore('Endereço', 'Sem Endereço', $this->data['rua']);
            return $this->insert($data);
        }        
        $this->setReferences();
        
        //Pega o registro endereço do banco para verificar modificações
        $entity = $this->em->find($this->entity, $this->data['idEnde']);
            
        $this->getDiff($entity);
        if(empty($this->dePara)) 
            return $entity;
            
        //Faz todos os sets de endereco
        $entity = Configurator::configure($entity,$this->data);    
        
        $this->em->persist($entity);
        
        if($flush)
            $this->em->flush();
        
        return $entity;
    }
    
    /**
     * Monta string de campos afetados para registrar no log
     * @param \Livraria\Entity\Endereco $ent
     */
    public function getDiff($ent){
        $this->dePara = '';
        $this->dePara .= $this->diffAfterBefore('Rua', $ent->getRua(), $this->data['rua']);
        $this->dePara .= $this->diffAfterBefore('Numero', $ent->getNumero(), $this->data['numero']);
        $this->dePara .= $this->diffAfterBefore('Complemento', $ent->getCompl(), $this->data['compl']);
        $this->dePara .= $this->diffAfterBefore('CEP', $ent->getCep(), $this->data['cep']);
        $this->dePara .= $this->diffAfterBefore('Bairro', $ent->getBairro(), $this->data['bairro']);
        $this->dePara .= $this->diffAfterBefore('Cidade', $ent->getCidade(), $this->data['cidade']);
        $this->dePara .= $this->diffAfterBefore('Estado', $ent->getEstado(), $this->data['estado']);
        $this->dePara .= $this->diffAfterBefore('Pais', $ent->getPais(), $this->data['pais']);
    }
}
