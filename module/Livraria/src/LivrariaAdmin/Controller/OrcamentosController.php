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
    
    public function verificaUserAction($redirect=true){
        $user = $this->getIdentidade();
        $data = $this->getRequest()->getPost()->toArray();
        
        $sessionContainer = new SessionContainer("LivrariaAdmin");
       
        if(($user->getTipo() == 'admin') and (!isset($sessionContainer->administradora['id'])) and ($redirect))
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'escolheAdm'));
        
        if(isset($sessionContainer->administradora['id']))
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'new'));
        
        $id = $user->getId();
        $user = $this->getEm()->getReference('Livraria\Entity\User', $id);
        
        $sessionContainer->administradora = $user->getAdministradora()->toArray();
        if(!is_array($sessionContainer->administradora))
            return $this->redirect()->toRoute($this->route, array('controller' => 'auth'));
            
        $sessionContainer->user = $user;
        $sessionContainer->seguradora = $user->getAdministradora()->getSeguradora()->toArray();
        
        if($redirect)
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'new'));
        else
            return TRUE;
    }
    
    public function escolheAdmAction(){
        $user = $this->getIdentidade();
        $data = $this->getRequest()->getPost()->toArray();
        
        if($user->getTipo() != 'admin')
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
            $sessionContainer->expiraSessaoMontada = true;
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'new'));
        }
        
        $this->form = "LivrariaAdmin\Form\EscolheAdm";
        $this->formData = new $this->form();        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView()); 
    }

    public function indexAction(){
        return new ViewModel();
    }
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function listarOrcamentosAction(array $filtro = array()){
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        //usuario admin pode ver tudo os outros são filtrados
        if($this->getIdentidade()->getTipo() != 'admin'){
            //Verifica se usuario tem registrado a administradora na sessao
            if(!isset($sessionContainer->administradora['id'])){
                $this->verificaUserAction(FALSE);
            }
            $filtro['administradora'] = $sessionContainer->administradora['id'];
        }
        return parent::indexAction($filtro,array('criadoEm' => 'DESC'));
    }
   
    /**
     * Tenta incluir o registro e exibe a listagem ou erros ao incluir
     * @return \Zend\View\Model\ViewModel
     */ 
    public function newAction() {
        
        $sessionContainer = new SessionContainer("LivrariaAdmin");
       
        $data = $this->getRequest()->getPost()->toArray();
        if((!isset($data['subOpcao'])) OR ($data['subOpcao'] == 'novo')){
            $data['subOpcao'] = '';
            $data['seguroEmNome'] = '02';
            $data['pais'] = '1';
            if(($this->getIdentidade()->getTipo() == 'admin')and(!isset($sessionContainer->expiraSessaoMontada))){
                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'escolheAdm'));
            }
            $data['formaPagto'] = $sessionContainer->administradora['formaPagto'];
            $data['validade'] = $sessionContainer->administradora['validade'];
            $data['tipoCobertura'] = $sessionContainer->administradora['tipoCobertura'];
            //Expira montagem da sessao do usuario admin
            unset($sessionContainer->expiraSessaoMontada);
        }
        
        
        if(!isset($sessionContainer->administradora['id'])){
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'verificaUser'));
        }
        
        $data['administradora'] = $sessionContainer->administradora['id'];
        $data['seguradora'] = $sessionContainer->seguradora['id'];
        $data['criadoEm']       = (empty($data['criadoEm']))? (new \DateTime('now'))->format('d/m/Y') : $data['criadoEm'];
        
        $filtroForm = array();
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        $this->formData->setData($data);
        
        
        if($data['subOpcao'] == 'calcular'){
            if ($this->formData->isValid()){
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->insert($data,'OnlyCalc');
                $this->formData->setData($service->getNewInputs());
                foreach ($result as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }else{
                $this->flashMessenger()->addMessage('Primeiro Acerte os erros antes de calcular!!!');
            }
        }

        
        if($data['subOpcao'] == 'salvar'){
            if ($this->formData->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->insert($data);
                if($result[0] === TRUE){
                    $this->flashMessenger()->addMessage('Registro salvo com sucesso!!!');
                    $sessionContainer->idOrcamento = $result[1];
                    unset($sessionContainer->administradora);
                    return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action'=>'edit'));
                }else{
                    foreach ($result as $value) {
                        $this->flashMessenger()->addMessage($value);
                    }
                }
                $this->formData->setData($service->getNewInputs());
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
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        
        //Verifica se usuario tem registrado a administradora na sessao
        if(!isset($sessionContainer->administradora['id']) && ($this->getIdentidade()->getTipo() != 'admin')){
            if(!$this->verificaUserAction(FALSE))
                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
        }
        
        //Verifica se o id veio registrado na sessão
        if(isset($sessionContainer->idOrcamento)){
            $data['id'] = $sessionContainer->idOrcamento;
            unset($sessionContainer->idOrcamento);
            $data['subOpcao'] = 'editar';
        }
        
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'fechar'){ 
            $servicoFechado = new \Livraria\Service\Fechados($this->getEm());
            $resul = $servicoFechado->fechaOrcamento($data['id']);
            if($resul[0] === TRUE){
                $this->flashMessenger()->addMessage('Registro fechado com sucesso!!!');
                return;
            }else{
                unset($resul[0]);
                foreach ($resul as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }
        }
        
        $filtroForm = array();
        
        if($data['subOpcao'] == 'editar'){ 
            $repository = $this->getEm()->getRepository($this->entity);
            $entity = $repository->find($data['id']);
        }
        
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        //Metodo que bloqueia campos da edição caso houver
        //$this->formData->setEdit();
        if($data['subOpcao'] == 'editar'){ 
            $this->formData->setData($entity->toArray());
            $data['administradora'] = $entity->getAdministradora()->getId();
            $data['status'] = $entity->getStatus();
        }else{
            $this->formData->setData($data);
        }
        
        // Verificar se usuario pode editar esse orçamento
        if(($data['administradora'] != $sessionContainer->administradora['id'] && ($this->getIdentidade()->getTipo() != 'admin'))){
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
        }
        
        $this->formData->setEdit();
        if($data['subOpcao'] == 'calcular'){
            if ($this->formData->isValid()){
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->update($data,'OnlyCalc');
                $this->formData->setData($service->getNewInputs());
                foreach ($result as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }else{
                $this->flashMessenger()->addMessage('Primeiro Acerte os erros antes de calcular!!!');
            }
        }
        
        if($data['subOpcao'] == 'salvar'){
            if ($this->formData->isValid()){
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->update($data);
                if($result === TRUE){
                    $this->flashMessenger()->addMessage('Registro salvo com sucesso!!!');
                    //return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
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
    
    public function imprimiSeguroAction(){
        $this->getServiceLocator()->get('Livraria\Service\Fechados')->getPdfSeguro('12');
    }

}
