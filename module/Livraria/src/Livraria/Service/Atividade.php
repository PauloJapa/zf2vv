<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;
use Livraria\Entity\Configurator;
/**
 * Atividade
 * Faz o CRUD da tabela Atividade no banco de dados
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class Atividade extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Atividade";
    }
    
}
