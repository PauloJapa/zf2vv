<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Imovel
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ImovelsController extends CrudController {

    /**
     * Parmetros do Crudcontroller
     */
    public function __construct() {
        $this->entity = "Livraria\Entity\Imovel";
        $this->form = "LivrariaAdmin\Form\Imovel";
        $this->service = "Livraria\Service\Imovel";
        $this->controller = "imovels";
        $this->route = "livraria-admin";
        
    }
    
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function indexAction(array $filtro = array()){
        $this->verificaSeUserAdmin();
        $orderBy = array('rua' => 'ASC', 'numero' => 'ASC');
        if(!$this->render){
            return parent::indexAction($filtro, $orderBy);
        }
        $data = $this->getRequest()->getPost()->toArray();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
            return parent::indexAction($filtro, $orderBy);
        }
        $filtro=[];
        if(!empty($data['rua'])){
            $filtro['rua'] = $data['rua'];
        }
        
        $list = $this->getEm()
                    ->getRepository($this->entity)
                    ->pesquisa($data);
        
        return parent::indexAction($filtro, $orderBy, $list);
    }

    /**
     * Tenta incluir o registro e exibe a listagem ou erros ao incluir
     * @return \Zend\View\Model\ViewModel
     */
    public function newAction() {
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            $filtro['locador']= $data['locador'];
            if(!empty($data['rua']))   $filtro['rua']    = $data['rua'];
            if(!empty($data['numero']))$filtro['numero'] = $data['numero'];
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

    /**
     * Edita o registro, Salva o registro, exibi o registro ou a listagem
     * @return \Zend\View\Model\ViewModel
     */
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
            $filtro['locador'] = $entity->getLocador()->getId();
            $filtro['rua'] = $entity->getRua();
            $filtro['numero'] = $entity->getNumero();
            $this->formData->setData($entity->toArray());
            break;
        case 'buscar':  
            $filtro['locador']= $data['locador'];
            if(!empty($data['rua']))   $filtro['rua']    = $data['rua'];
            if(!empty($data['numero']))$filtro['numero'] = $data['numero'];
            $this->formData->setData($data);  
            break;
        case 'salvar':   
            $filtro['locador']= $data['locador'];
            if(!empty($data['rua']))   $filtro['rua']    = $data['rua'];
            if(!empty($data['numero']))$filtro['numero'] = $data['numero'];
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

}