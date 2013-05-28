<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

use Zend\Session\Container as SessionContainer;
/**
 * Fechados
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class FechadosController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Fechados";
        $this->form = "LivrariaAdmin\Form\Orcamento";
        $this->service = "Livraria\Service\Fechados";
        $this->controller = "fechados";
        $this->route = "livraria-admin";
        
    }
    
    public function verificaUserAction(){
        return new ViewModel();
    }
    
    public function indexAction(array $filtro = array()){
        
    }
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function listarFechadosAction(array $filtro = array()){
        return parent::indexAction($filtro,array('criadoEm' => 'DESC'));
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
    
    public function imprimiSeguroAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        if(!isset($data['id']))
            $data['id'] = '1';
        
        $this->getServiceLocator()->get($this->service)->getPdfSeguro($data['id']);
    }
    
    public function buscarAction(){
        $this->verificaSeUserAdmin();
        $this->formData = new \LivrariaAdmin\Form\Renovacao();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());        
    }
    
    public function listarAction(){
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $this->data = $this->getRequest()->getPost()->toArray();
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        $sessionContainer->data = $this->data;
        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        $viewData = $this->getParamsForView();
        $viewData['data'] = $this->getEm()
                     ->getRepository($this->entity)
                     ->findFechados($this->data);
        if(empty($this->data['fim'])){
            $this->data['fim'] = '1 mês subsequente';
        }
        $viewData['date'] = $this->data;
        return new ViewModel($viewData);
        
    }
    
    /**
     * Tela com Filtor para gerar listagem de registros
     * @return \Zend\View\Model\ViewModel
     */
    public function listaToEmailAction(){
        $this->verificaSeUserAdmin();
        $this->formData = new \LivrariaAdmin\Form\Relatorio($this->getEm());
        $this->formData->setEmailFechados();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView()); 
    }            
    
    /**
     * Tela que lista os registros para enviar email ou gerar excel
     * @return \Zend\View\Model\ViewModel
     */
    public function gerarToEmailAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->gerarListaEmail($data);
        $data['inicio'] = $service->getFiltroTratado('inicio')->format('d/m/Y');
        $data['fim'] = $service->getFiltroTratado('fim')->format('d/m/Y');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data])); 
    }
    
    /**
     * Envia os emails com os registros separando por ADM
     */
    public function sendEmalFatAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //Pegar servico que gerou os registro 
        $service = new $this->service($this->getEm());
        //Passa o localizador de serviço para pegar o servico de email e fazer o envio de email
        $resul = $service->sendEmailFaturados($this->getServiceLocator(),$data['id']);
        if($resul){
            echo '<h2>Email(s) enviado com Sucesso!!!<br><br><br> Feche esta janela para continuar !!</h2>';
        }else{
            echo '<h2>Erro !! Feche esta janela e tente novamente !!</h2>';
        }         
    }
    
    /**
     * Enviar excel com listagem dos seguros fechados a confirmar para usuario fazer download.
     * @return \Zend\View\Model\ViewModel
     */
    public function toExcelFatAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        //ler Dados do cacheado da ultima consulta.
        $sc = new SessionContainer("LivrariaAdmin");
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $sc->faturados,'admFiltro' => $data['id']));
        $viewModel->setTerminal(true);
        return $viewModel;        
    }

}
