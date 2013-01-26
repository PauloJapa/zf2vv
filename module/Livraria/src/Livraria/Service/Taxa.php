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
        //Pega uma referencia do registro da tabela classe
        $data['classe'] = $this->em->getReference("Livraria\Entity\Classe", $data['classe']);
        $date = explode("/", $data['inicio']);
        $data['inicio'] = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        if(!empty($data['fim'])){
            $date = explode("/", $data['fim']);
            $data['fim']    = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        }else{
            $data['fim']    = new \DateTime("00/00/0000");
        }
        //Pegar um referencia da classe para classe
        return parent::insert($data);       
    }
 
    /** 
     * Alterar no banco de dados o registro
     * @param Array $data com os campos do registro
     * @return entidade 
     */    
    public function update(array $data) {
        //Pega uma referencia do registro da tabela classe
        $data['classe'] = $this->em->getReference("Livraria\Entity\Classe", $data['classe']);
        $date = explode("/", $data['inicio']);
        $data['inicio'] = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        if(!empty($data['fim'])){
            $date = explode("/", $data['fim']);
            $data['fim']    = new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
        }else{
            $data['fim']    = new \DateTime("00/00/0000");
        }
        
        return parent::update($data);
    }
}
