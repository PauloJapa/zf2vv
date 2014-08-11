<?php

namespace LivrariaAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator,
    Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;

class ParametroSisController  extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\ParametroSis";
        $this->form = "LivrariaAdmin\Form\ParametroSis";
        $this->service = "Livraria\Service\ParametroSis";
        $this->controller = "parametroSis";
        $this->route = "livraria-admin";
    }
    
    /**
     * Faz listagem dos dados baseado nos parametros passados
     * @param array $filtro
     * @param array $orderBy
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function indexAction(array $filtro = [],array $orderBy = ['key' => 'ASC']) {
        $this->verificaSeUserAdmin();
        
        $list = $this->getEm()
                     ->getRepository($this->entity)
                     ->findBy($filtro,$orderBy);

        $this->page = $this->params()->fromRoute('page');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        $this->paginator = new Paginator(new ArrayAdapter($list));
        $this->paginator->setCurrentPageNumber($this->page);
        $this->paginator->setDefaultItemCountPerPage(100);
        $this->paginator->setPageRange(15);
        if ($this->render) {
            return new ViewModel($this->getParamsForView());
        }
    }
    
    /**
     * Tenta incluir o registro e exibe a listagem ou erros ao incluir
     * @return \Zend\View\Model\ViewModel
     */ 
    public function newAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['key'])) $filtro['key'] = $data['key'] . '%';
        }
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        $this->formData->setData($data);
        if($data['subOpcao'] == 'salvar'){
            if ($this->formData->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->insert($data);
                if($result === TRUE){
                    $this->flashMessenger()->addMessage('Registro salvo com sucesso!!!');
                    return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
                }
                foreach ($result as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }
        }
        
        $this->setRender(FALSE);
        $this->indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }

    /**
     * Edita o registro, Salva o registro, exibi o registro ou a listagem
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'editar'){ 
            $repository = $this->getEm()->getRepository($this->entity);
            $entity = $repository->find($data['id']);
            $filtro['key']  = $entity->getKey();
        }
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['key'])) $filtro['key'] = $data['key'] . '%';
        }
        
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        //Metodo que bloqueia campos da edição caso houver
        $this->formData->setEdit($this->getIdentidade()->getIsAdmin());
        if($data['subOpcao'] == 'editar'){ 
            $this->formData->setData($entity->toArray());
        }else{
            $this->formData->setData($data);
        }
        
        if($data['subOpcao'] == 'salvar'){
            if ($this->formData->isValid()){
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->update($data);
                if($result === TRUE){
                    $this->flashMessenger()->addMessage('Registro salvo com sucesso!!!');
                    return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
                }
                foreach ($result as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }  
        }
            
        $this->setRender(FALSE);
        $this->indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }
}
