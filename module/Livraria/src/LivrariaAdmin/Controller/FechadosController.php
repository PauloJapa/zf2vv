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
    
    public function estornaVariosInArrayAction() {        
        $this->verificaSeUserAdmin();
        /* @var $service \Livraria\Service\Fechados */
        $service = $this->getServiceLocator()->get($this->service);
        $array = [];
/**
 * Tudo que foi fechado pelo user fabiana que não é da Robotton MES 07-2015

$array[] = '1058209';
$array[] = '1058210';
$array[] = '1058211';
$array[] = '1058214';
$array[] = '1058215';
$array[] = '1058216';
$array[] = '1058218';
$array[] = '1058220';
$array[] = '1058221';
$array[] = '1058222';
$array[] = '1058223';
$array[] = '1058224';
$array[] = '1058225';
$array[] = '1058226';
$array[] = '1058229';
$array[] = '1058230';
$array[] = '1058231';
$array[] = '1058235';
$array[] = '1058236';
$array[] = '1058237';
$array[] = '1058238';
$array[] = '1058239';
$array[] = '1058240';
$array[] = '1058241';
$array[] = '1058242';
$array[] = '1058246';
$array[] = '1058247';
$array[] = '1058248';
$array[] = '1058249';
$array[] = '1058250';
$array[] = '1058251';
$array[] = '1058253';
$array[] = '1058254';
$array[] = '1058255';
$array[] = '1058256';
$array[] = '1058257';
$array[] = '1058258';
$array[] = '1058259';
$array[] = '1058260';
$array[] = '1058263';
$array[] = '1058264';
$array[] = '1058265';
$array[] = '1058266';
$array[] = '1058267';
$array[] = '1058268';
$array[] = '1058269';
$array[] = '1058270';
$array[] = '1058271';
$array[] = '1058272';
$array[] = '1058273';
$array[] = '1058274';
$array[] = '1058275';
$array[] = '1058276';
$array[] = '1058277';
$array[] = '1058278';
$array[] = '1058279';
$array[] = '1058280';
$array[] = '1058281';
$array[] = '1058282';
$array[] = '1058283';
$array[] = '1058284';
$array[] = '1058285';
$array[] = '1058286';
$array[] = '1058287';
$array[] = '1058288';
$array[] = '1058289';
$array[] = '1058290';
$array[] = '1058291';
$array[] = '1058292';
$array[] = '1058293';
$array[] = '1058294';
$array[] = '1058295';
$array[] = '1058296';
$array[] = '1058297';
$array[] = '1058298';
$array[] = '1058299';
$array[] = '1058300';
$array[] = '1058301';
$array[] = '1058302';
$array[] = '1058303';
$array[] = '1058304';
$array[] = '1058305';
$array[] = '1058306';
$array[] = '1058307';
$array[] = '1058308';
$array[] = '1058309';
$array[] = '1058310';
$array[] = '1058311';
$array[] = '1058312';
$array[] = '1058313';
$array[] = '1058314';
$array[] = '1058315';
$array[] = '1058316';
$array[] = '1058317';
$array[] = '1058318';
$array[] = '1058319';
$array[] = '1058320';
$array[] = '1058321';
$array[] = '1058322';
$array[] = '1058323';
$array[] = '1058324';
$array[] = '1058325';
$array[] = '1058326';
$array[] = '1058327';
$array[] = '1058328';
$array[] = '1058329';
$array[] = '1058330';
$array[] = '1058331';
$array[] = '1058332';
$array[] = '1058333';
$array[] = '1058334';
$array[] = '1058335';
$array[] = '1058336';
$array[] = '1058337';
$array[] = '1058338';
$array[] = '1058339';
$array[] = '1058340';
$array[] = '1058341';
$array[] = '1058342';
$array[] = '1058343';
$array[] = '1058344';
$array[] = '1058345';
$array[] = '1058346';
$array[] = '1058347';
$array[] = '1058348';
$array[] = '1058349';
$array[] = '1058350';
$array[] = '1058351';
$array[] = '1058352';
$array[] = '1058353';
$array[] = '1058365';
$array[] = '1058366';
$array[] = '1058491';
$array[] = '1058493';
$array[] = '1058494';
$array[] = '1058495';
$array[] = '1058496';
$array[] = '1058498';
$array[] = '1058499';
$array[] = '1058500';
$array[] = '1058502';
$array[] = '1058503';
$array[] = '1058504';
$array[] = '1058505';
$array[] = '1058506';
$array[] = '1058507';
$array[] = '1058508';
$array[] = '1058509';
$array[] = '1058510';
$array[] = '1058511';
$array[] = '1058512';
$array[] = '1058514';
$array[] = '1058516';
$array[] = '1058518';
$array[] = '1058519';
$array[] = '1058520';
$array[] = '1058521';
$array[] = '1058522';
$array[] = '1058523';
$array[] = '1058524';
$array[] = '1058525';
$array[] = '1058526';
$array[] = '1058527';
$array[] = '1058528';
$array[] = '1058529';
$array[] = '1058530';
$array[] = '1058531';
$array[] = '1058532';
$array[] = '1058533';
$array[] = '1058534';
$array[] = '1058535';
$array[] = '1058536';
$array[] = '1058537';
$array[] = '1058538';
$array[] = '1058539';
$array[] = '1058540';
$array[] = '1058541';
$array[] = '1058542';
$array[] = '1058543';
$array[] = '1058544';
$array[] = '1058545';
$array[] = '1058546';
$array[] = '1058547';
$array[] = '1058548';
$array[] = '1058549';
$array[] = '1058550';
$array[] = '1058551';
$array[] = '1058552';
$array[] = '1058553';
$array[] = '1058554';
$array[] = '1058555';
$array[] = '1058556';
$array[] = '1058558';
$array[] = '1058559';
$array[] = '1058560';
$array[] = '1058561';
$array[] = '1058562';
$array[] = '1058563';
$array[] = '1058564';
$array[] = '1058565';
$array[] = '1058566';
$array[] = '1058567';
$array[] = '1058568';
$array[] = '1058569';
$array[] = '1058570';
$array[] = '1058571';
$array[] = '1058572';
$array[] = '1058573';
$array[] = '1058574';
$array[] = '1058575';
$array[] = '1058576';
$array[] = '1058577';
$array[] = '1058578';
$array[] = '1058579';
$array[] = '1058580';
$array[] = '1058581';
$array[] = '1058582';
$array[] = '1058583';
$array[] = '1058584';
$array[] = '1058586';
$array[] = '1058587';
$array[] = '1058588';
$array[] = '1058589';
$array[] = '1058590';
$array[] = '1058591';
$array[] = '1058592';
$array[] = '1058593';
$array[] = '1058594';
$array[] = '1058595';
$array[] = '1058596';
$array[] = '1058597';
$array[] = '1058599';
$array[] = '1058601';
$array[] = '1058602';
$array[] = '1058603';
$array[] = '1058604';
$array[] = '1058605';
$array[] = '1058606';
$array[] = '1058607';
$array[] = '1058608';
$array[] = '1058609';
$array[] = '1058610';
$array[] = '1058611';
$array[] = '1058613';
$array[] = '1058614';
$array[] = '1058615';
$array[] = '1058616';
$array[] = '1058618';
$array[] = '1058620';
$array[] = '1058621';
$array[] = '1058622';
$array[] = '1058624';
$array[] = '1058625';
$array[] = '1058626';
$array[] = '1058627';
$array[] = '1058628';
$array[] = '1058629';
$array[] = '1058630';
$array[] = '1058631';
$array[] = '1058632';
$array[] = '1058633';
$array[] = '1058634';
$array[] = '1058635';
$array[] = '1058636';
$array[] = '1058637';
$array[] = '1058638';
$array[] = '1058640';
$array[] = '1058641';
$array[] = '1058642';
$array[] = '1058643';
$array[] = '1058644';
$array[] = '1058645';
$array[] = '1058646';
$array[] = '1058647';
$array[] = '1058648';
$array[] = '1058649';
$array[] = '1058650';
$array[] = '1058651';
$array[] = '1058652';
$array[] = '1058653';
$array[] = '1058654';
$array[] = '1058655';
$array[] = '1058656';
$array[] = '1058657';
$array[] = '1058658';
$array[] = '1058659';
$array[] = '1058660';
$array[] = '1058661';
$array[] = '1058662';
$array[] = '1058663';
$array[] = '1058664';
$array[] = '1058665';
$array[] = '1058666';
$array[] = '1058667';
$array[] = '1058668';
$array[] = '1058669';
$array[] = '1058670';
$array[] = '1058671';
$array[] = '1058672';
$array[] = '1058673';
$array[] = '1058674';
$array[] = '1058675';
$array[] = '1058676';
$array[] = '1058677';
$array[] = '1058678';
$array[] = '1058679';
$array[] = '1058680';
$array[] = '1058681';
$array[] = '1058682';
$array[] = '1058683';
$array[] = '1058684';
$array[] = '1058685';
$array[] = '1058686';
$array[] = '1058687';
$array[] = '1058688';
$array[] = '1058689';
$array[] = '1058690';
$array[] = '1058691';
$array[] = '1058692';
$array[] = '1058693';
$array[] = '1058694';
$array[] = '1058695';
$array[] = '1058696';
$array[] = '1058697';
$array[] = '1058698';
$array[] = '1058699';
$array[] = '1058700';
$array[] = '1058701';
$array[] = '1058702';
$array[] = '1058703';
$array[] = '1058704';
$array[] = '1058705';
$array[] = '1058706';
$array[] = '1058707';
$array[] = '1058708';
$array[] = '1058709';
$array[] = '1058710';
$array[] = '1058711';
$array[] = '1058712';
$array[] = '1058713';
$array[] = '1058714';
$array[] = '1058715';
$array[] = '1058716';
$array[] = '1058717';
$array[] = '1058718';
$array[] = '1058719';
$array[] = '1058720';
$array[] = '1058721';
$array[] = '1058722';
$array[] = '1058723';
$array[] = '1058724';
$array[] = '1058725';
$array[] = '1058726';
$array[] = '1058727';
$array[] = '1058728';
$array[] = '1058729';
$array[] = '1058730';
$array[] = '1058731';
$array[] = '1058732';
$array[] = '1058733';
$array[] = '1058734';
$array[] = '1058735';
$array[] = '1058736';
$array[] = '1058737';
$array[] = '1058738';
$array[] = '1058739';
$array[] = '1058740';
$array[] = '1058741';
$array[] = '1058742';
$array[] = '1058743';
$array[] = '1058744';
$array[] = '1058745';
$array[] = '1058746';
$array[] = '1058747';
$array[] = '1058748';
$array[] = '1058749';
$array[] = '1058750';
$array[] = '1058751';
$array[] = '1058752';
$array[] = '1058753';
$array[] = '1058754';
$array[] = '1058755';
$array[] = '1058756';
$array[] = '1058757';
$array[] = '1058758';

 */
        
        
        $qtd = 0;
        $mot = 'Usuario Fabiana fechou este seguro equivocadamente!!!';
        echo 'Inicio Estornou '. $mot ;
        foreach ($array as $id) {            
            $resul = $service->estornaFechado($id,$mot, $this);
            if($resul){
                echo 'Estornou com sucesso id ' , $id  , '<br>';
            }else{
                echo '<h2>ATENÇÃO ERRO AO Estornar id ' , $id , '</h2>';
            }
            $qtd ++;
        }
        $service->logForSis('fechados','0','fechados','estornaVarios',"Estornou $qtd Fechados com o seguinte motivo. " . $mot);
    }
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function listarFechadosAction(array $filtro = array()){
        $data = $this->filtrosDaPaginacao();
        if(!isset($data['dataI'])){
            $data['dataI'] = date('01/m/Y');
        }
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
        // Se for robotton colocar data inicial para nao travar server
        if($data['administradora'] == '196' && (!isset($data['dataI']) OR empty($data['dataI']))){
            $dataAgora = new \DateTime('now');
            $dataAgora->sub(new \DateInterval('P1M'));
            $data['dataI'] = '01/' . $dataAgora->format('m/Y'); 
        }
        $this->formData->setData((is_null($data)) ? [] : $data);
        $inputs = ['id','locador','locatario','refImovel', 'administradora', 'status', 'user','dataI','dataF'];
        if ((isset($data['id'])) AND (!empty($data['id']))) {
            $filtro['id'] = $data['id'];
            isset($data['administradora']) && $filtro['administradora'] = $data['administradora'];
        }  else {
            foreach ($inputs as $input) {
                if ((isset($data[$input])) AND (!empty($data[$input]))) {
                    $filtro[$input] = $data[$input];
                }
                if ((isset($data[$input])) AND (!empty($data[$input]))) {
                    $filtro[$input] = $data[$input];
                }
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
    
    public function checkFechadosAction() {
        /* @var $service \Livraria\Service\Fechados */
        $service = new $this->service($this->getEm());
        $service->checkFechados();
        die;
    }

}
