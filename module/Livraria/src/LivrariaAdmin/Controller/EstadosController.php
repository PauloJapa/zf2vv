<?php

namespace LivrariaAdmin\Controller;

class EstadosController  extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Estado";
        $this->form = "LivrariaAdmin\Form\Estado";
        $this->service = "Livraria\Service\Estado";
        $this->controller = "estados";
        $this->route = "livraria-admin";
    }
    
}
