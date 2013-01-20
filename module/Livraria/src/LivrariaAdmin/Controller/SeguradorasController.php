<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

class SeguradorasController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Seguradora";
        $this->form = "LivrariaAdmin\Form\Seguradora";
        $this->service = "Livraria\Service\Seguradora";
        $this->controller = "seguradoras";
        $this->route = "livraria-admin";
        
    }
    
    public function newAction() {
        $form = $this->getServiceLocator()->get($this->form);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->insert($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function editAction() {
        $form = $this->getServiceLocator()->get($this->form);
        $request = $this->getRequest();

        $repository = $this->getEm()->getRepository($this->entity);
        $entity = $repository->find($this->params()->fromRoute('id', 0));

        if ($this->params()->fromRoute('id', 0))
            $form->setData($entity->toArray());

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->update($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        return new ViewModel(array('form' => $form));
    }
    
    public function autoCompAction(){
        $administradora = $this->getRequest()->getPost('administradoraDesc');
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($administradora .'%');
        if(!$resultSet)// Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoComp('%'. $administradora .'%');
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

}
