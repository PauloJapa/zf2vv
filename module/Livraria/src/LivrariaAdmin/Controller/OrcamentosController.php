<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

use Zend\Session\Container as SessionContainer;
/**
 * Orcamento
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class OrcamentosController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Orcamento";
        $this->form = "LivrariaAdmin\Form\Orcamento";
        $this->service = "Livraria\Service\Orcamento";
        $this->controller = "orcamentos";
        $this->route = "livraria-admin";
        
    }
    
    public function verificaUserAction(){
        $user = $this->getIdentidade();
        $data = $this->getRequest()->getPost()->toArray();
        
        $sessionContainer = new SessionContainer("LivrariaAdmin");
       
        if(($user->getIsAdmin()) and (!isset($sessionContainer->administradora['id'])))
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'escolheAdm'));
        
        if(isset($sessionContainer->administradora['id']))
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'new'));
        
        $id = $user->getId();
        $user = $this->getEm()->getReference('Livraria\Entity\User', $id);
        if(!is_array($sessionContainer->administradora))
            return $this->redirect()->toRoute($this->route, array('controller' => 'auth'));
        
        $sessionContainer->administradora = $user->getAdministradora->toArray();
        
        if(!is_array($sessionContainer->administradora))
            return $this->redirect()->toRoute($this->route, array('controller' => 'auth'));
            
        $seguradora = $this->getEm()->getRepository('Livraria\Entity\Seguradora')->findById($sessionContainer->administradora['seguradora']);
        $sessionContainer->user = $user;
        $sessionContainer->administradora = $administradora[0]->toArray();
        $sessionContainer->seguradora = $seguradora[0]->toArray();
        
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'new'));
    }
    
    public function escolheAdmAction(){
        $user = $this->getIdentidade();
        $data = $this->getRequest()->getPost()->toArray();
        
        if(!$user->getIsAdmin())
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'verificaUser'));
        
        if(!empty($data['administradora'])){
            $administradora = $this->getEm()->getRepository('Livraria\Entity\Administradora')->findById($data['administradora']);
            if(empty($administradora))
                return $this->redirect()->toRoute($this->route, array('controller' => 'auth'));
            
            $seguradora = $this->getEm()->getRepository('Livraria\Entity\Seguradora')->findById($administradora[0]->getSeguradora()->getId());
            $sessionContainer = new SessionContainer("LivrariaAdmin");
            $sessionContainer->user = $user;
            $sessionContainer->administradora = $administradora[0]->toArray();
            $sessionContainer->seguradora = $seguradora[0]->toArray();
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'new'));
        }
        
        $this->form = "LivrariaAdmin\Form\EscolheAdm";
        $this->formData = new $this->form();        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView()); 
    }

    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function indexAction(array $filtro = array()){
        return parent::indexAction($filtro,array('seguradora' => 'ASC', 'atividade' => 'ASC'));
    }
   
    /**
     * Tenta incluir o registro e exibe a listagem ou erros ao incluir
     * @return \Zend\View\Model\ViewModel
     */ 
    public function newAction() {
        
        $sessionContainer = new SessionContainer("LivrariaAdmin");
       
        if(!isset($sessionContainer->administradora['id'])){
            $this->flashMessenger()->addMessage('Escolha a Administradora !!!');
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'verificaUser'));
        }
        
        $data = $this->getRequest()->getPost()->toArray();
        $data['administradora'] = $sessionContainer->administradora['id'];
        $data['seguradora'] = $sessionContainer->seguradora['id'];
        $data['criadoEm']       = (empty($data['criadoEm']))? (new \DateTime('now'))->format('d/m/Y') : $data['criadoEm'];
        
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
                if($result[0] === TRUE){
                    $this->flashMessenger()->addMessage('Registro salvo com sucesso!!!');
                    return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action'=>'edit', 'id'=>$result[1]));
                }else{
                    foreach ($result as $value) {
                        $this->flashMessenger()->addMessage($value);
                    }
                }
            }
        }
        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        
        return new ViewModel($this->getParamsForView()); 
    }

    /**
     * Edita o registro, Salva o registro, exibi o registro ou a listagem
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction() {
        //Pegar id se for redirecionado da action new
        $id = $this->params()->fromRoute('id', '0');
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //Verifica se o id veio por meio de get
        if($id != '0'){
            $data['id'] = $id;
        }
        
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'editar'){ 
            $repository = $this->getEm()->getRepository($this->entity);
            $entity = $repository->find($data['id']);
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $entity->getSeguradora()->getId();
                $filtroForm['seguradora'] = $filtro['seguradora'];
            }
        }
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['seguradora'])){
                $filtro['seguradora'] = $data['seguradora'];
                $filtroForm['seguradora'] = $filtro['seguradora'];
            }
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
