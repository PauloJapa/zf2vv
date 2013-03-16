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
    
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function indexAction(array $filtro = array()){
        $this->verificaSeUserAdmin();
        $orderBy = array('seguradora' => 'ASC', 'atividade' => 'ASC', 'inicio'=>'DESC');
        if(!$this->render){
            return parent::indexAction($filtro, $orderBy);
        }
        $data = $this->getRequest()->getPost()->toArray();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setForClasseAtividade();
        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
            return parent::indexAction(['status'=>'A'], $orderBy);
        }
        $filtro=[];
        if(!empty($data['atividade'])){
            $filtro['atividade'] = $data['atividade'];
        }
        
        return parent::indexAction($filtro, $orderBy);
    }
   
    /**
     * Tenta incluir o registro e exibe a listagem ou erros ao incluir
     * @return \Zend\View\Model\ViewModel
     */ 
    public function newAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $data['seguradora'];
                $filtroForm['seguradora'] = $data['seguradora'];
            }
            if(!empty($data['atividade'])) $filtro['atividade'] = $data['atividade'];
        }
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
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

    /**
     * Edita o registro, Salva o registro, exibi o registro ou a listagem
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'editar'){ 
            $repository = $this->getEm()->getRepository($this->entity);
            $entity = $repository->find($data['id']);
            if(!isset($data['seguradora'])){
                $filtro['seguradora'] = $entity->getSeguradora()->getId();
                $filtroForm['seguradora'] = $filtro['seguradora'];
            }
            $filtro['atividade']  = $entity->getAtividade()->getId();
        }
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $data['seguradora'];
                $filtroForm['seguradora'] = $filtro['seguradora'];
            }
            if(!empty($data['atividade'])) $filtro['atividade'] = $data['atividade'];
        }
        
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
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
            
        $this->setRender(FALSE);
        $this->indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }

}
