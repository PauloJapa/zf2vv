<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * Comissao
 * Faz o CRUD da tabela Comissao no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Comissao extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Comissao";
    }

    /** 
     * Inserir no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */     
    public function insert(array $data) { 
        //Pega uma referencia do registro da tabela administradora
        $data['administradora'] = $this->em->getReference("Livraria\Entity\Administradora", $data['administradora']);
        $date = explode("/", $data['inicio']);
        $data['inicio'] = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        if(!empty($data['fim'])){
            $date = explode("/", $data['fim']);
            $data['fim']    = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        }else{
            $data['fim']    = new \DateTime("00/00/0000");
        }
        //Pegar um referencia da administradora para administradora
        return parent::insert($data);       
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */    
    public function update(array $data) {
        //Pega uma referencia do registro da tabela administradora
        $data['administradora'] = $this->em->getReference("Livraria\Entity\Administradora", $data['administradora']);
        $date = explode("/", $data['inicio']);
        $data['inicio'] = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        if((!empty($data['fim'])) && ($data['fim'] != "vigente")){
            $date = explode("/", $data['fim']);
            $data['fim']    = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        }else{
            $data['fim']    = new \DateTime("00/00/0000");
        }
        
        return parent::update($data);
    }
}
