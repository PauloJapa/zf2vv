<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

class CidadesController  extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Cidade";
        $this->form = "LivrariaAdmin\Form\Cidade";
        $this->service = "Livraria\Service\Cidade";
        $this->controller = "cidades";
        $this->route = "livraria-admin";
    }
    
    public function autoCompAction(){
        $cidade = $this->getRequest()->getPost('cidadeDesc');
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($cidade .'%');
        if(!$resultSet)// Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoComp('%'. $cidade .'%');
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
}
