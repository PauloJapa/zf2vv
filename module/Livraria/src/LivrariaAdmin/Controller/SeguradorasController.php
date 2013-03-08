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
    
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function indexAction(array $filtro = array()){
        return parent::indexAction($filtro,array());
    }
    
    public function newAction() {
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
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

    public function editAction() {
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'editar'){ 
            $repository = $this->getEm()->getRepository($this->entity);
            $entity = $repository->find($data['id']);
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $entity->getSeguradora()->getId();
                $filtroForm['seguradora'] = $filtro['seguradora'];
            }
        }
        
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        //Metodo que bloqueia campos da edição caso houver
        //$this->formData->setEdit();
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
