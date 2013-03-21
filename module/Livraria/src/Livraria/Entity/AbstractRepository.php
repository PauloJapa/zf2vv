<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * AbstractRepository
 * Todos os metodos de auxilio para consulta
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class AbstractRepository extends EntityRepository {
    
    /**
     * Converte uma data string em data object no indice apontado.
     * @param string $date
     * @return \DateTime
     */
    public function dateToObject($date){
        //Trata as variveis data string para data objetos
        if(!isset($date)){
           return FALSE;
        }
        
        if(is_object($date)){
            if($date instanceof \DateTime)
                return $date;
            else
                return FALSE;
        }
       
        $date = explode("/", $date);
        return new \DateTime($date[1] . '/' . $date[0] . '/' . $date[2]);
    }    
}