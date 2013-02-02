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
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = null;
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['classe']))    $filtro['classe']     = $data['classe'];
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $data['seguradora'];
                $this->formData->reloadSelectClasse(array('seguradora' => $filtro['seguradora'])) ;
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
            $filtro['classe']     = $entity->getClasse()->getId();
            $this->formData->reloadSelectClasse(array('seguradora' => $filtro['seguradora']));
            $this->formData->setData($entity->toArray());
            break;
        case 'buscar':  
            if(!empty($data['classe']))    
                $filtro['classe']     = $data['classe'];
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $data['seguradora'];
                $this->formData->reloadSelectClasse(array('seguradora' => $filtro['seguradora']));
            }
            $this->formData->setData($data);  
            break;
        case 'salvar': 
            if(!empty($data['classe']))    
                $filtro['classe']     = $data['classe'];
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $data['seguradora'];
                $this->formData->reloadSelectClasse(array('seguradora' => $filtro['seguradora']));
            }
            $this->formData->setData($data);
            if ($this->formData->isValid()){
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->update($data);
                if($result === TRUE){
                    return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
                    $this->flashMessenger()->addMessage('Registro salvo com sucesso!!!');
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
