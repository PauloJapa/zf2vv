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
        $this->formData = $this->getServiceLocator()->get($this->form);
        $request = $this->getRequest();

        $filtro = null;
        
        if ($request->isPost()) {
            $this->formData->setData($request->getPost());
            $data   = $request->getPost()->toArray();
            $filtro['seguradora'] = $data['seguradora'];
            if ((empty($data['subOpcao'])) and ($this->formData->isValid())) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->insert($data);
                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }
        
        $this->setRender(FALSE);
        parent::indexAction($filtro);
        
        return new ViewModel($this->getParamsForView()); 
    }

    public function editAction() {
        $this->formData = $this->getServiceLocator()->get($this->form);
        $request = $this->getRequest();
        $repository = $this->getEm()->getRepository($this->entity);

        if ($this->params()->fromRoute('id', 0))
            $entity = $repository->find($this->params()->fromRoute('id', 0));
        else{            
            $data   = $request->getPost()->toArray();
            $entity = $repository->find($data['id']);
        }
        
        $this->formData->setData($entity->toArray());

        $filtro = null;

        if ($request->isPost()) {
            $this->formData->setData($request->getPost());
            if(isset($data['seguradora']))
                $filtro['seguradora'] = $data['seguradora'];
            else
                $filtro['seguradora'] = $entity->getSeguradora()->getId();
            if ((empty($data['subOpcao'])) and ($this->formData->isValid())) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->update($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }
        
        $this->setRender(FALSE);
        parent::indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }

}
