<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Http\Header\CacheControl;

use Zend\Session\Container as SessionContainer;
/**
 * Orcamento
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class OrcamentosController extends CrudController {
    
    /**
     * Endereço para instaciar serviço de fechados.
     * @var Objeto
     */
    private $serviceFechado;

    public function __construct() {
        $this->entity = "Livraria\Entity\Orcamento";
        $this->form = "LivrariaAdmin\Form\Orcamento";
        $this->service = "Livraria\Service\Orcamento";
        $this->serviceFechado = "Livraria\Service\Fechados";
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
    public function listarOrcamentosAction(array $filtro=[], $operadores=[]){
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        //usuario admin pode ver tudo os outros são filtrados
        if($this->getIdentidade()->getTipo() != 'admin'){
            //Verifica se usuario tem registrado a administradora na sessao
            if(!isset($sessionContainer->administradora['id'])){
                $this->verificaUserAction(FALSE);
            }
            $filtro['administradora'] = $sessionContainer->administradora['id'];
        }
        $data = $this->filtrosDaPaginacao();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setOrcamento();
        $this->formData->setData((is_null($data)) ? [] : $data);
        $inputs = ['id', 'administradora', 'status', 'user','dataI','dataF'];
        foreach ($inputs as $input) {
            if ((isset($data[$input])) AND (!empty($data[$input]))) {
                $filtro[$input] = $data[$input];
            }
        }
        
        $this->verificaSeUserAdmin();
        $list = $this->getEm()
                     ->getRepository($this->entity)
                     ->findOrcamento($filtro,$operadores);
        
        if(empty($list))$list[0] = FALSE;
        
        return parent::indexAction($filtro,['criadoEm' => 'DESC'],$list);
    }
   
    /**
     * Tenta incluir o registro e exibe a listagem ou erros ao incluir
     * @return \Zend\View\Model\ViewModel
     */ 
    public function newAction() {
        
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        
        if(!isset($sessionContainer->administradora['id'])){
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'verificaUser'));
        }
       
        $data = $this->getRequest()->getPost()->toArray();
        if((!isset($data['subOpcao'])) OR ($data['subOpcao'] == 'novo')){
            $data['subOpcao']     = '';
            $data['seguroEmNome'] = '02';
            $data['pais']         = '1';
            if(($this->getIdentidade()->getTipo() == 'admin')and(!isset($sessionContainer->expiraSessaoMontada))){
                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'escolheAdm'));
            }
            $data['administradora'] = $sessionContainer->administradora['id'];
            $data['seguradora']     = $sessionContainer->seguradora['id'];
            $data['criadoEm']       = (empty($data['criadoEm']))? (new \DateTime('now'))->format('d/m/Y') : $data['criadoEm'];
            //Buscar paramentros de comissão e seus multiplos
            $comissaoEnt = $this->getEm()
                ->getRepository('Livraria\Entity\Comissao')
                ->findComissaoVigente($data['administradora'],  $data['criadoEm']);
            $data['comissaoEnt'] = $comissaoEnt->getId();
            $data['comissao'] = $comissaoEnt->floatToStr('comissao');
            $data['formaPagto'] = $sessionContainer->administradora['formaPagto'];
            $data['validade'] = $sessionContainer->administradora['validade'];
            $data['tipoCobertura'] = $sessionContainer->administradora['tipoCobertura'];
            //Se houver forma de pagamento dafult somente o usuario admin pode alterar
            if($this->getIdentidade()->getTipo() != 'admin'){
                if($data['formaPagto'] != ''){
                    $sessionContainer->userNotAdmin = true;
                }
            }
            //Expira montagem da sessao do usuario admin
            unset($sessionContainer->expiraSessaoMontada);
        }
        
        $filtroForm = array();
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        $this->formData->setData($data);
        //Bloquear campos para os usuarios não Admin
        if($sessionContainer->userNotAdmin){
            $this->formData->bloqueiaCampos();
        }
        
        
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

    public function fecharSegurosAction() {
        $data = $this->getRequest()->getPost()->toArray();
        //Pegar Servico de fechados $sf
        //Fechar a lista de orçamentos selecionados.
        foreach ($data['Checkeds'] as $idOrc) {
            $sf = new $this->serviceFechado($this->getEm());
            $resul = $sf->fechaOrcamento($idOrc,FALSE);
            if($resul[0] === TRUE){
                $fechou = $sf->getEntity();
                $msg = 'Orçamento ' . $idOrc . ' gerou o fechado nº' . $fechou->getId() . '/' . $fechou->getCodano();
                $this->flashMessenger()->addMessage($msg);
            }else{
                $msg = 'Orçamento ' . $idOrc . ' gerou os seguintes erros!';
                $this->flashMessenger()->addMessage($msg);
                unset($resul[0]);
                foreach ($resul as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }            
        }
        return $this->redirect()->toRoute($this->route, array('controller' => 'fechados', 'action'=>'listarFechados'));
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
            $servicoFechado = new $this->serviceFechado($this->getEm());
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
        
        //Se houver forma de pagamento dafult somente o usuario admin pode alterar
        if($this->getIdentidade()->getTipo() != 'admin'){
            if($sessionContainer->administradora['formaPagto'] != ''){
                $this->formData->bloqueiaCampos();
            }
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
    
    
    public function printPropostaAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        if(!isset($data['id']))
            $data['id'] = '1';
        
        $this->getServiceLocator()->get($this->service)->getPdfOrcamento($data['id']);
    }
    
}
