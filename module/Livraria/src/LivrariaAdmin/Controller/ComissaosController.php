<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Comissao
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ComissaosController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Comissao";
        $this->form = "LivrariaAdmin\Form\Comissao";
        $this->service = "Livraria\Service\Comissao";
        $this->controller = "comissaos";
        $this->route = "livraria-admin";
        
    }
    
    public function indexAction(){
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setForAdministradora();
        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
            return parent::indexAction(['status'=>'A'], ['administradora'=>'ASC', 'inicio'=>'DESC']);
        }
        $filtro=[];
        if(!empty($data['administradora'])){
            $filtro['administradora'] = $data['administradora'];
        }
        
        return parent::indexAction($filtro, ['administradora'=>'ASC', 'inicio'=>'DESC']);
    }
    
    public function newAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        $this->formData = new $this->form(null, $this->getEm());
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
        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        return new ViewModel($this->getParamsForView()); 
    }

    public function editAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'editar'){ 
            $repository = $this->getEm()->getRepository($this->entity);
            $entity = $repository->find($data['id']);
        }
        
        $this->formData = new $this->form(null, $this->getEm());
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
        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        return new ViewModel($this->getParamsForView()); 
    }

}
