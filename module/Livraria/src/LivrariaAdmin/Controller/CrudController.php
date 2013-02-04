<?php

namespace LivrariaAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator,
    Zend\Paginator\Adapter\ArrayAdapter;

abstract class CrudController extends AbstractActionController {

    /**
     *
     * @var EntityManager
     */
    protected $em;
    protected $service;
    protected $entity;
    protected $form;
    protected $formData;
    protected $route;
    protected $controller;
    protected $paginator;
    protected $page;
    protected $route2;
    protected $autoCompParams;
    protected $render = TRUE;

    public function indexAction(array $filtro = null,array $orderBy = null) {
        if(is_null($filtro)){
            $filtro = array();
        }
        if(is_null($orderBy)){
            $orderBy = array();
        }
        $list = $this->getEm()
                     ->getRepository($this->entity)
                     ->findBy($filtro,$orderBy);

        $this->page = $this->params()->fromRoute('page');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        $this->paginator = new Paginator(new ArrayAdapter($list));
        $this->paginator->setCurrentPageNumber($this->page);
        $this->paginator->setDefaultItemCountPerPage(20);
        $this->paginator->setPageRange(15);
        if($this->render)
            return new ViewModel($this->getParamsForView());
    }

    public function newAction() {
        $this->formData = new $this->form();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->formData->setData($request->getPost());
            if ($this->formData->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->insert($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        if($this->render)
            return new ViewModel(array('form' => $this->formData));
    }

    public function editAction() {
        $this->formData = new $this->form();
        $request = $this->getRequest();

        $repository = $this->getEm()->getRepository($this->entity);
        $entity = $repository->find($this->params()->fromRoute('id', 0));

        if ($this->params()->fromRoute('id', 0))
            $this->formData->setData($entity->toArray());

        if ($request->isPost()) {
            $this->formData->setData($request->getPost());
            if ($this->formData->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->update($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        if($this->render)
            return new ViewModel(array('form' => $this->formData));
    }

    public function deleteAction() {
        $service = $this->getServiceLocator()->get($this->service);
        
        if($this->params()->fromRoute('id', 0))
            $data['id'] = $this->params()->fromRoute('id', 0);
        else
            $data = $this->getRequest()->getPost()->toArray();
        
        $result = $service->delete($data['id']);
        if ($result === TRUE){
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
        }
        foreach ($result as $value) {
            $this->flashMessenger()->addMessage($value);
        }
    }

    /**
     * @return EntityManager
     */
    protected function getEm() {
        if (null === $this->em)
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        return $this->em;
    }
    
    /**
     * Setar o controller para retorna ou não um view para tela
     * @param boolean $render default TRUE
     */
    public function setRender($render) {
        $this->render = $render;
    }

    /**
     * Junta os paramentros basicos para as actions new ou edit
     * @return array 
     */
    public function getParamsForView() {
        if($this->formData){
            $viewData['form'] = $this->formData;
            $viewData['formName'] = $this->formData->getName();
        }
        $viewData['data'] = $this->paginator ;
        $viewData['page'] = $this->page;
        $viewData['route'] = $this->route2;
        $viewData['params'] = $this->route2->getParams();
        $viewData['matchedRouteName'] = $this->route2->getMatchedRouteName();
        $viewData['flashMessages']    = $this->flashMessenger()->getMessages();
        return $viewData;    
    }
    
    /**
     * 
     * Configura um chamada para o repositorio que
     * Faz uma busca no BD pela requisição Ajax com parametro de busca
     * Na view retorna os dados no formato texto para o js exibir para o usuario
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function autoCompAction(){
        $autoComp = $this->getRequest()->getPost('autoComp');
        $param = $this->getRequest()->getPost($autoComp);
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($param .'%');
        if(!$resultSet)// Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoComp('%'. $param .'%');
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

}
