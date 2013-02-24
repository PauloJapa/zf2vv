<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Locador
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LocadorsController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Locador";
        $this->form = "LivrariaAdmin\Form\Locador";
        $this->service = "Livraria\Service\Locador";
        $this->controller = "locadors";
        $this->route = "livraria-admin";
        
    }
    
    public function indexAction(array $filtro = array()){
        return parent::indexAction($filtro);
    }

    public function newAction() {
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['cpf']))           $filtro['cpf']           = $data['cpf'];
            if(!empty($data['cnpj']))          $filtro['cnpj']          = $data['cnpj'];
            if(!empty($data['administradora']))$filtro['administradora']= $data['administradora'];
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
        $this->indexAction($filtro);
        
        return new ViewModel($this->getParamsForView()); 
    }

    public function editAction() {
        $this->formData = new $this->form(null, $this->getEm());
        $this->formData->setEdit();
        $data = $this->getRequest()->getPost()->toArray();
        $repository = $this->getEm()->getRepository($this->entity);
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        switch ($data['subOpcao']){
        case 'editar':    
            $entity = $repository->find($data['id']);
            $filtro['cpf']   = $entity->getCpf();
            $filtro['cnpj']  = $entity->getCnpj();
            $this->formData->setData($entity->toArray());
            break;
        case 'buscar': 
            if(!empty($data['cpf']))       $filtro['cpf']           = $data['cpf'];
            if(!empty($data['cnpj']))      $filtro['cnpj']          = $data['cnpj'];
            $this->formData->setData($data);  
            break;
        case 'salvar': 
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
        $this->indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }
    
    /**
     * 
     * Configura um chamada para o repositorio que
     * Faz uma busca no BD pela requisição Ajax com parametro de busca
     * Na view retorna os dados no formato texto para o js exibir para o locators
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function autoCompAction(){
        
        $subOpcao = $this->getRequest()->getPost('subOpcao','');
        $locadorNome = trim($this->getRequest()->getPost('locadorNome'));
        $administradora = trim($this->getRequest()->getPost('administradora',''));
        
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($locadorNome .'%',$administradora);
        if(!$resultSet)// Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoComp('%'. $locadorNome .'%',$administradora);
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet, 'subOpcao'=>$subOpcao));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

}
