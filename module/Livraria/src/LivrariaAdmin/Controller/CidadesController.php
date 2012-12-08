<?php

namespace LivrariaAdmin\Controller;

class CidadesController  extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Cidade";
        $this->form = "LivrariaAdmin\Form\Cidade";
        $this->service = "Livraria\Service\Cidade";
        $this->controller = "cidades";
        $this->route = "livraria-admin";
    }
    
}
