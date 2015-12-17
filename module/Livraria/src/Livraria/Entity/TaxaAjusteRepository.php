<?php

namespace Livraria\Entity;

use Doctrine\ORM\EntityRepository;
/**
 * TaxaAjusteRepository
 * Todos os metodos de consulta ao banco para esta classe
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class TaxaAjusteRepository extends EntityRepository {

    /**
     * Busca uma taxaAjuste para atividade e seguradora na data $date.
     * 
     * @param type $seguradora
     * @param type $administradora
     * @param type $date
     * @param string $validade      mensal|anual
     * @param type $classe
     * @param type $ocupacao
     * @return boolean|Entity \Livraria\Entity\TaxaAjuste
     */
    public function findTaxaAjusteVigente($seguradora, $administradora, $date, $validade, $classe, $ocupacao){

        
    }
}

