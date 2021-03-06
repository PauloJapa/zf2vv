<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Locatario
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LocatariosController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Locatario";
        $this->form = "LivrariaAdmin\Form\Locatario";
        $this->service = "Livraria\Service\Locatario";
        $this->controller = "locatarios";
        $this->route = "livraria-admin";
        
    }
    public function indexAction(array $filtro = array(), array $orderBy = array(), $list = array()) {
        $this->verificaSeUserAdmin();
        $orderBy = array('nome' => 'ASC');
        if(!$this->render){
            return parent::indexAction($filtro, $orderBy);
        }
        $data = $this->filtrosDaPaginacao();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
            return parent::indexAction(['status'=>'A'], $orderBy);
        }
        $this->formData->setData($data);
        $filtro=[];
        if(!empty($data['nome'])){
            $filtro['nome'] = $data['nome'];
        }
        if(!empty($data['documento'])){
            $filtro[$data['cpfOuCnpj']] = $data['documento'];
        }
        
        $list = $this->getEm()
                    ->getRepository($this->entity)
                    ->pesquisa($filtro);
        return parent::indexAction($filtro, $orderBy, $list);
    }

    public function newAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['cpf']))       $filtro['cpf']           = $data['cpf'];
            if(!empty($data['cnpj']))      $filtro['cnpj']          = $data['cnpj'];
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
        $this->verificaSeUserAdmin();
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
     * Função que altera ou inclui o Locatario recebendo os dados via ajax.
     * @return \Zend\View\Model\ViewModel
     */    
    public function saveAction(){
        /* @var $service \Livraria\Service\Locatario */
        $service = $this->getServiceLocator()->get($this->service);
        $data = $this->getRequest()->getPost()->toArray();
        
        $data['nome'] = $data['locatarioNome'];
        if(empty($data['locatario'])){
            $data['id'] = '';
            $resul = $service->insert($data);            
            $ret['ok']['msg'] = "Locatario incluido com sucesso!!";
            if($resul === TRUE){
                $ret['ok']['locatario'] = $service->getEntity()->getId();
            }
        }else{
            $data['id'] = $data['locatario'];
            $resul = $service->update($data);
            $ret['ok']['msg'] = "Locatario alterado com sucesso!!";
        }
        if($resul !== TRUE){
            $ret = [];
            $ret['msg'] = 'Não foi possivel salvar este Locatario';
            $ret['erro'] = $resul;
        }
        
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(['ret' => $ret]);
        $viewModel->setTerminal(true);
        return $viewModel;        
    }

}
