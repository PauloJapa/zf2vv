<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
class UsersController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\User";
        $this->form = "LivrariaAdmin\Form\User";
        $this->service = "Livraria\Service\User";
        $this->controller = "users";
        $this->route = "livraria-admin";
    }
    
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function indexAction(array $filtro = array()){
        return parent::indexAction($filtro,array('administradora' => 'ASC'));
    }

    public function newAction() {
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
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
        }
        
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        //Metodo que bloqueia campos da edição caso houver
        //$this->formData->setEdit();
        if($data['subOpcao'] == 'editar'){ 
            $array = $entity->toArray();
            unset($array['password']); // retirar o password da edição
            $this->formData->setData($array);
        }else{
            $this->formData->setData($data);
        }
        
        if($data['subOpcao'] == 'salvar'){
            if ($this->formData->isValid()){
                /* @var $service \Livraria\Service\User */
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
    
    public function alteraSenhaAction(){
        $data = $this->getRequest()->getPost()->toArray();
        if(isset($data['subOpcao'])){
            $service = $this->getServiceLocator()->get($this->service);
            $result = $service->updateSenha($data);
            if($result === TRUE){
                $this->flashMessenger()->addMessage('Alterado Com Sucesso!!!');
            }else{
                foreach ($result as $value) {
                    $this->flashMessenger()->addMessage($value);
                }            
            }
        }else{
            $this->flashMessenger()->clearMessages();
        }        
        $this->formData = new $this->form(null, $this->getEm(),[]);
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();        
        return new ViewModel($this->getParamsForView()); 
    }
}
