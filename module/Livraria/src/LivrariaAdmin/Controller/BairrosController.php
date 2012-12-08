<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

class BairrosController  extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Bairro";
        $this->form = "LivrariaAdmin\Form\Bairro";
        $this->service = "Livraria\Service\Bairro";
        $this->controller = "bairros";
        $this->route = "livraria-admin";
    }
    
    public function nolayout()
    {
        // Turn off the layout, i.e. only render the view script.
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    public function autoCompAction(){
        $bairro = $this->getRequest()->getPost('bairroDesc');
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($bairro .'%');
        if(!$resultSet)// Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoComp('%'. $bairro .'%');
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
}
