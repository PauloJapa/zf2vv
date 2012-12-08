<?php

namespace LivrariaAdmin\Controller;

class PaisesController  extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Pais";
        $this->form = "LivrariaAdmin\Form\Pais";
        $this->service = "Livraria\Service\Pais";
        $this->controller = "paises";
        $this->route = "livraria-admin";
    }
    
}
