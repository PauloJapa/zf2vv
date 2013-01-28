<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Atividade
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class AtividadesController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Atividade";
        $this->form = "LivrariaAdmin\Form\Atividade";
        $this->service = "Livraria\Service\Atividade";
        $this->controller = "atividades";
        $this->route = "livraria-admin";
        
    }
    
}
