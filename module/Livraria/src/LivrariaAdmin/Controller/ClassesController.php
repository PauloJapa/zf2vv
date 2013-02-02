<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Classe
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ClassesController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Classe";
        $this->form = "LivrariaAdmin\Form\Classe";
        $this->service = "Livraria\Service\Classe";
        $this->controller = "classes";
        $this->route = "livraria-admin";
        $this->autoCompParams = array('input' => 'classeDesc');
    }
    
    public function newAction() {
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = null;
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $data['seguradora'];
            }
            $this->formData->setData($data);
        }
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
        parent::indexAction($filtro);
        
        return new ViewModel($this->getParamsForView()); 
    }

    public function editAction() {
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $repository = $this->getEm()->getRepository($this->entity);
        $filtro = null;
        
        switch ($data['subOpcao']){
        case 'editar':    
            $entity = $repository->find($data['id']);
            $filtro['seguradora'] = $entity->getSeguradora()->getId();
            $this->formData->setData($entity->toArray());
            break;
        case 'buscar':  
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $data['seguradora'];
            }
            $this->formData->setData($data);  
            break;
        case 'salvar': 
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $data['seguradora'];
            }
            $this->formData->setData($data);
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
            break;
        }
        
        $this->setRender(FALSE);
        parent::indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }

}
