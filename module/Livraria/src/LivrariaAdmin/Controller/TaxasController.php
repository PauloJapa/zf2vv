<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Taxa
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class TaxasController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Taxa";
        $this->form = "LivrariaAdmin\Form\Taxa";
        $this->service = "Livraria\Service\Taxa";
        $this->controller = "taxas";
        $this->route = "livraria-admin";
        
    }
    
    public function newAction() {
        $this->formData = $this->getServiceLocator()->get($this->form);
        $request = $this->getRequest();

        $filtro = null;
        
        if ($request->isPost()) {
            $this->formData->setData($request->getPost());
            $data   = $request->getPost()->toArray();
            if(!empty($data['seguradora']))$filtro['seguradora'] = $data['seguradora'];
            if(!empty($data['classe']))    $filtro['classe']     = $data['classe'];
            if(isset($filtro['seguradora']))
                $this->formData->reloadSelectClasse(array('seguradora' => $filtro['seguradora'])) ;
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
            if(isset($data['seguradora'])){
                if(!empty($data['seguradora']))$filtro['seguradora'] = $data['seguradora'];
                if(!empty($data['classe']))    $filtro['classe']     = $data['classe'];
            }else{
                $filtro['seguradora'] = $entity->getSeguradora()->getId();
                $filtro['classe']     = $entity->getClasse()->getId();
            }
            //Filtrar classes a serem exibidas conforme seguradora selecionada
            if(isset($filtro['seguradora'])){
                $this->formData->reloadSelectClasse(
                    array('filtro' => array('seguradora' => $filtro['seguradora']),
                          'data'   => array('classe'     => $filtro['classe']),
                    )
                );
            }
            if ((empty($data['subOpcao'])) and ($this->formData->isValid())) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->update($data);

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }
        
        $this->setRender(FALSE);
        parent::indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }

}
