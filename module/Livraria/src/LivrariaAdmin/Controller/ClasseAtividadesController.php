<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator,
    Zend\Paginator\Adapter\ArrayAdapter;
/**
 * ClasseAtividade
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ClasseAtividadesController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\ClasseAtividade";
        $this->form = "LivrariaAdmin\Form\ClasseAtividade";
        $this->service = "Livraria\Service\ClasseAtividade";
        $this->controller = "classeAtividades";
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
        
        $this->setRender(FALSE);
        parent::indexAction();

        return new ViewModel(array('form' => $form,'data' => $this->paginator, 'page' => $this->page, 'route' => $this->route2));
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
        
        
        $this->setRender(FALSE);
        parent::indexAction();
        
        return new ViewModel(array('form' => $form,'data' => $this->paginator, 'page' => $this->page, 'route' => $this->route2));

    }

}
