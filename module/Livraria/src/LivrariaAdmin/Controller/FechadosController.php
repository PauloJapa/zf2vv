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
        
    public function indexAction(array $filtro = array()){
        
    }
    
    public function estornaVariosAction() {        
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        /* @var $service \Livraria\Service\Fechados */
        $service = $this->getServiceLocator()->get($this->service);
        $qtd = 0;
        foreach ($data['Checkeds'] as $id) {            
            $service->estornaFechado($id,$data['motivoEstorno'], $this);
            $qtd ++;
        }
        $service->logForSis('fechados','0','fechados','estornaVarios',"Estornou $qtd Fechados com o seguinte motivo. " . $data['motivoEstorno']);
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action'=>'listarFechados'));
    }
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function listarFechadosAction(array $filtro = array()){
        $data = $this->filtrosDaPaginacao();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setFechadosFull();
        //usuario admin pode ver tudo os outros são filtrados
        if($this->getIdentidade()->getTipo() != 'admin'){
            $sessionContainer = new SessionContainer("LivrariaAdmin");
            //Verifica se usuario tem registrado a administradora na sessao
            if(!isset($sessionContainer->administradora['id'])){
                $this->verificaUserAction();
            }
            $data['administradora'] = $sessionContainer->administradora['id'];
            $data['administradoraDesc'] = $sessionContainer->administradora['nome'];
        }
        // Se filtro datai não exitir seta como padrão para Novos.
        // Natalia pediu para tirar email em 19/02/2015 as 10:03
//        if(!isset($data['dataI'])){
//            $dataAgora = new \DateTime('now');
//            $dataAgora->sub(new \DateInterval('P1M'));
//            $data['dataI'] = '01/' . $dataAgora->format('m/Y'); 
//        }
        $this->formData->setData((is_null($data)) ? [] : $data);
        $inputs = ['id','locador','locatario','refImovel', 'administradora', 'status', 'user','dataI','dataF'];
        foreach ($inputs as $input) {
            if ((isset($data[$input])) AND (!empty($data[$input]))) {
                $filtro[$input] = $data[$input];
            }
        }
        $list = $this->getEm()
                     ->getRepository($this->entity)
                     ->findListaFechados($filtro,[]);
        
        return parent::indexAction($filtro,[],$list);
    }
    
    /**
     * Verifica Usuario que não tem permissão admin se tem administradora e carrega na sessão
     * Caso não encontre redireciona para tela de logon
     * @return empty | redireciona para tela de logon
     */
    public function verificaUserAction(){
        $sessionContainer = new SessionContainer("LivrariaAdmin");       
        $user = $this->getEm()->find('Livraria\Entity\User', $this->getIdentidade()->getId());
        
        $sessionContainer->administradora = $user->getAdministradora()->toArray();
        if(!is_array($sessionContainer->administradora))
            return $this->redirect()->toRoute($this->route, array('controller' => 'auth'));
            
        $sessionContainer->user = $user;
        $sessionContainer->seguradora = $user->getAdministradora()->getSeguradora()->toArray();
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
    
    public function delVariosAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();        
        //Cancelar a lista de seguros fechados selecionados.
        foreach ($data['Checkeds'] as $idFech) {
            //Pegar Servico de fechados $sf
            $service = $this->getServiceLocator()->get($this->service);
            $result = $service->delete($idFech, $data, $this->getServiceLocator()); 
            if($result === TRUE){
                $msg = 'Seguro Fechado ' . $idFech . ' Foi cancelado com sucesso';
                $this->flashMessenger()->addMessage($msg);
            }else{
                $msg = 'Seguro Fechado ' . $idFech . ' gerou os seguintes erros!';
                $this->flashMessenger()->addMessage($msg);
                foreach ($result as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }
        }   
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action' => 'listarFechados'));
    }   
    
    public function deleteAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        /* @var $service \Livraria\Service\Fechados */
        $service = $this->getServiceLocator()->get($this->service);
        $result = $service->delete($data['id'], $data, $this->getServiceLocator());
        if($result === TRUE){
            $this->flashMessenger()->clearMessages();
        }else{
            foreach ($result as $value) {
                $this->flashMessenger()->addMessage($value);
            }
        }
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action' => 'listarFechados'));
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
        $this->formData->setData($this->filtrosDaPaginacao());
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
        //Pega servico  
        $service = new $this->service($this->getEm());
        $viewData = $this->getParamsForView();
        $viewData['data'] = $service->montaListaAtualAnterior($this->data);
        $this->data['inicio'] = $service->getFiltroData('inicio');
        $this->data['fim']    = $service->getFiltroData('fim');
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
    
    public function editAntecessorAction(){
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        if(!isset($data['id'])){
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action' => 'listarFechados'));
        }
        // Guardar id do antecessor na sessão
        $sc = new SessionContainer("LivrariaAdmin");        
        $fechado = $this->getEm()->find($this->entity,$data['id']);
        $origemOrca = $fechado->getOrcamentoId();
        if(!is_null($origemOrca) && $origemOrca != 0){
            $sc->idOrcamento = $origemOrca;
            $sc->idOrcamentoNoPrint = TRUE;
            return $this->redirect()->toRoute($this->route, array('controller' => 'orcamentos', 'action' => 'edit'));
        }
        $origemReno = $fechado->getRenovacaoId();
        if(!is_null($origemReno) && $origemReno != 0){
            $sc->idOrcamento = $origemReno;
            $sc->idOrcamentoNoPrint = TRUE;
            return $this->redirect()->toRoute($this->route, array('controller' => 'orcamentos', 'action' => 'edit')) ;
        }
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action' => 'listarFechados'));
    }

}
