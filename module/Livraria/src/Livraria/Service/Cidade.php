<?php

namespace Livraria\Service;

use Doctrine\ORM\EntityManager;

class Cidade extends AbstractService {

    public function __construct(EntityManager $em) {
        parent::__construct($em);
        $this->entity = "Livraria\Entity\Cidade";
    }
}
