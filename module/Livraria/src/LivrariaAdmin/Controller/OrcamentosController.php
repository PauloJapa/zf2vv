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
    
    /**
     * Altera os Registro selecionados para data(vigência inicio) ou validade(anual, mensal) comuns entre esses registro.
     * @return objeto que redireciona para listar Orçamentos.
     */
    public function changeDateValidityAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        // redireciona dados se vir de uma listagem de fechados que podem sem de array de fechados
        if(isset($data['CheckedsFechados'])){
            $data['Checkeds'] = $data['CheckedsFechados'];
        }
        /* @var $service \Livraria\Service\Orcamento */
        $service = $this->getServiceLocator()->get($this->service);
        $service->changeDateValidity($this,$data);
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'listarOrcamentos'));
    }
    
    /**
     * Verifica:
     * Usuario do tipo admin redireciona para tela de escolha de administradora
     * Usuario nao é admin carrega administradora na sessão e redireciona para fazer novo Orçamento
     * Caso não encontre os paramentros no usuairo redireciona para tela de logon
     * @param boolean $redirect
     * @return boolean | redireciona para  novo orçamento | redireciona para tela de logon
     */   
    public function verificaUserAction($redirect=true){
        $user = $this->getIdentidade();
        $data = $this->getRequest()->getPost()->toArray();
      //  var_dump($user);
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        $adm = $sessionContainer->administradora;
       
        if(($user->getTipo() == 'admin') and (!isset($adm['id'])) and ($redirect))
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'escolheAdm'));
        
        if(isset($adm['id']))
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'new'));
        
        $id = $user->getId();
        $user = $this->getEm()->getReference('Livraria\Entity\User', $id);
        
        $sessionContainer->administradora = $user->getAdministradora()->toArray();
//        echo '<pre>';        var_dump($sessionContainer->administradora); die;
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
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        if(!isset($data['subOpcao'])){
            $data['subOpcao'] =  '';
        }
        
        if($user->getTipo() != 'admin')
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'verificaUser'));
        
        if(!empty($data['administradora']) AND $data['subOpcao'] != 'editar'){
            $administradora = $this->getEm()->getRepository('Livraria\Entity\Administradora')->findById($data['administradora']);
            if(empty($administradora)){
                return $this->redirect()->toRoute($this->route, array('controller' => 'auth'));
            }            
            $seguradora = $this->getEm()->getRepository('Livraria\Entity\Seguradora')->findById($administradora[0]->getSeguradora()->getId());
            $sessionContainer->user = $user;
            $sessionContainer->administradora = $administradora[0]->toArray();
            $sessionContainer->seguradora = $seguradora[0]->toArray();
            $sessionContainer->expiraSessaoMontada = true;
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'new','page'=> rand(1, 100)));
        }
        
        $this->formData = new \LivrariaAdmin\Form\EscolheAdm();  
        $this->formData->setData($this->filtrosDaPaginacao());      
        
        if($data['subOpcao'] == 'editar'){
            $this->formData->get('administradora')->setValue($sessionContainer->administradora['id']);
            $this->formData->get('administradoraDesc')->setValue($sessionContainer->administradora['nome']);
        }
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
        $post = $this->getRequest()->getPost()->toArray();
        if($this->getRequest()->isPost() AND isset($post['subOpcao']) AND $post['subOpcao'] == 'buscar'){
            $data = $post;    
            $this->sc = new SessionContainer("LivrariaAdmin");
            $this->sc->data = $post;
        }else{
            $data = $this->filtrosDaPaginacao();  
        }
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setOrcamento($this->getIdentidade()->getTipo());
        //usuario admin pode ver tudo os outros são filtrados
        if($this->getIdentidade()->getTipo() != 'admin'){
            $sessionContainer = new SessionContainer("LivrariaAdmin");
            //Verifica se usuario tem registrado a administradora na sessao
            if(!isset($sessionContainer->administradora['id'])){
                $this->verificaUserAction(FALSE);
            }
            $data['administradora'] = $sessionContainer->administradora['id'];
            $data['administradoraDesc'] = $sessionContainer->administradora['nome'];
        }
        // Se filtro Status não exitir seta como padrão para Novos.
        if(!isset($data['status'])){
            $data['status'] = "T";
        }
        $this->formData->setData((is_null($data)) ? [] : $data);
        $inputs = ['id','locador','locatario','refImovel', 'administradora', 'status', 'user','dataI','dataF','validade','fechadoId'];
        $operadores['refImovel'] = 'LIKE';
        foreach ($inputs as $input) {
            if ((isset($data[$input])) AND (!empty($data[$input]))) {
                if($input == 'refImovel'){
                    $filtro[$input] = '%' . $data[$input];
                }else{
                    $filtro[$input] = $data[$input];
                }
            }
        }
        $list = $this->getEm()
                     ->getRepository($this->entity)
                     ->findOrcamento($filtro,$operadores,$data);
        
        return parent::indexAction($filtro,[],$list);
    }
    
    public function buscarAbertosAction(){
        $data = $this->filtrosDaPaginacao();
        //usuario admin pode ver tudo os outros são filtrados
        if($this->getIdentidade()->getTipo() != 'admin'){
            $sessionContainer = new SessionContainer("LivrariaAdmin");
            //Verifica se usuario tem registrado a administradora na sessao
            if(!isset($sessionContainer->administradora['id'])){
                $this->verificaUserAction(FALSE);
            }
            $filtro['administradora'] = $sessionContainer->administradora['id'];
            $data['administradora'] = $sessionContainer->administradora['id'];
            $data['administradoraDesc'] = $sessionContainer->administradora['nome'];
        }
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setOrcamento();
        $this->formData->setLocadorLocatario();
        $this->formData->setEndereco();
        $this->formData->setData((is_null($data)) ? [] : $data);
      //  $this->formData->setIsAdmin(); 
        $this->formData->setEdit();
        
        return new ViewModel(['form'=>$this->formData, 'formName'=>$this->formData->getName()]);        
    }
    
    public function listarAbertosAction(){
        $data = $this->filtrosDaPaginacao();
        $inputs = ['id', 'administradora', 'endereco', 'locador', 'locatario', 'user', 'end','dataI','dataF'];
        $filtro = ['status'=>'A'];
        foreach ($inputs as $input) {
            if ((isset($data[$input])) AND (!empty($data[$input]))) {
                $filtro[$input] = $data[$input];
            }
        }
        $list = $this->getEm()
                     ->getRepository($this->entity)
                     ->findOrcamento($filtro)->getQuery()->getResult();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        $viewData = $this->getParamsForView();
        $viewData['data'] = $list;
        return new ViewModel($viewData);
    }

    /**
     * Tenta incluir o registro e exibe a listagem ou erros ao incluir
     * @return \Zend\View\Model\ViewModel
     */ 
    public function newAction() {
        
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        $adm = $sessionContainer->administradora;
        if(!isset($adm['id'])){
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'verificaUser'));
        }
       
        $data = $this->getRequest()->getPost()->toArray();
        if((!isset($data['subOpcao'])) OR ($data['subOpcao'] == 'novo')){
            $data['subOpcao']     = '';
            $data['fechadoOrigemId']     = '0';
            $data['mensalSeq']     = '0';
            $data['orcaReno']     = 'orca';
            $data['gerado']       = 'N';
            $data['seguroEmNome'] = '02';
            $data['pais']         = '1';
            if(($this->getIdentidade()->getTipo() == 'admin')and(!isset($sessionContainer->expiraSessaoMontada))){
                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'escolheAdm'));
            }
            $data['administradora'] = $adm['id'];
            $data['seguradora']     = $sessionContainer->seguradora['id'];
            $data['criadoEm']       = (empty($data['criadoEm']))? (new \DateTime('now'))->format('d/m/Y') : $data['criadoEm'];
            $data['inicio']         = (empty($data['inicio']))?   (new \DateTime('now'))->format('d/m/Y') : $data['inicio'];            
            //Buscar paramentros de comissão e seus multiplos
            $comissaoEnt = $this->getEm()
                ->getRepository('Livraria\Entity\Comissao')
                ->findComissaoVigente($data['administradora'],  $data['criadoEm']);
            $data['comissaoEnt'] = $comissaoEnt->getId();
            $data['comissao']    = $comissaoEnt->floatToStr('comissao');
            if(!empty($adm['formaPagto'])){
                $data['formaPagto']    = $adm['formaPagto'];
            }
            if(!empty($adm['validade'])){
                $data['validade']      = $adm['validade'];
            }
//            if(!empty($adm['tipoCobertura'])){
//                $data['tipoCobertura'] = $adm['tipoCobertura'];
//            }
            if(!empty($adm['assist24'])){
                $data['assist24']      = $adm['assist24'];
            }
            //Se houver forma de pagamento dafult somente o usuario admin pode alterar
            if($this->getIdentidade()->getTipo() != 'admin'){
                if(isset($data['formaPagto']) AND $data['formaPagto'] != ''){
                    $sessionContainer->userNotAdmin = true;
                }
            }
            //Expira montagem da sessao do usuario admin
            unset($sessionContainer->expiraSessaoMontada);
        }
        
        $filtroForm = array();
        if(isset($data['seguradora'])){
            $filtroForm['seguradora'] = $data['seguradora'];
        }
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        $this->formData->setData($data);
        $this->formData->setComissao($data);
        // Abrir novo orçamento colocando o focu no campos do locadorNome        
        $this->formData->get('locadorNome')->setAttribute('autofocus', 'autofucus');
        //Bloquear campos para os usuarios não Admin
        if($sessionContainer->userNotAdmin){
            $this->formData->bloqueiaCampos();
        }
        
        $aviso = '0';
        if($data['subOpcao'] == 'calcular'){
            if ($this->formData->isValid()){
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->insert($data, TRUE);
                if($result === TRUE){
                    $this->formData->setData($service->getNewInputs());
                    $this->flashMessenger()->clearMessages();
                    $aviso = '1';
                }else{
                    foreach ($result as $value) {
                        $this->flashMessenger()->addMessage($value);
                    }
                }
            }else{
                $this->flashMessenger()->addMessage('Primeiro Acerte os erros antes de calcular!!!');
            }
        }

        if($data['subOpcao'] == 'salvar' AND $this->formData->isValid()){
            /* @var $service \Livraria\Service\Orcamento */
            $service = $this->getServiceLocator()->get($this->service);
            $result = $service->insert($data);
            if($result[0] === TRUE){
                $sessionContainer->idOrcamento = $result[1];
                unset($sessionContainer->administradora);
                $this->flashMessenger()->clearMessages();
                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action'=>'edit'));
            }
            foreach ($result as $value) {
                $this->flashMessenger()->addMessage($value);
            }
            $this->formData->setData($service->getNewInputs());
        }
        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        $arrayParam = $this->getCalculoParams($adm['id']);
        $arrayParam['administradora'] = $sessionContainer->administradora ;
        $arrayParam['imprimeProp'] = '0' ;
        $arrayParam['avisaCalc'] = $aviso ;
        if(isset($data['comissao'])){
            $arrayParam['comissao'] = $data['comissao'] ;
        }
        if(isset($data['mesNiver'])){
            $arrayParam['mesNiver'] = $data['mesNiver'] ;
        }
        return new ViewModel(array_merge($this->getParamsForView(), $arrayParam)); 
    }
    
    /**
     * Retorna os parametros para calculo 
     * - Comissão relacionadas a cadas seguradora
     * - Comissão para residencial e comercial parametrizado na tabela comissão
     * - Cobertura para residencial e comercial parametrizado na tabela Administradora
     */
    public function getCalculoParams($idAdm) {
     // - Comissão relacionadas a cadas seguradora
        $seguradoras = $this->getEm()->getRepository('Livraria\Entity\Seguradora')->fetchPairs();
        $param = [];
        foreach ($seguradoras as $key => $value) {           
            $comissaoKey = 'comissaoParam' . str_pad($key, 3, '0', STR_PAD_LEFT);
            $comissao = $this->getEm()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs($comissaoKey);
            $retira = array_shift($comissao);
            foreach ($comissao as $vlr => $txt) {
                $param[$key][$vlr] = $txt ;
            } 
        }
        // - Comissão para residencial e comercial parametrizado na tabela comissão     
        /* @var $entComissao \Livraria\Entity\Comissao */
        $entComissao = $this->getEm()->getRepository('Livraria\Entity\Comissao')->findOneBy(['administradora' => $idAdm],['inicio'=>'DESC']);
        if($entComissao){
            $param['comissaoComercial'] = $entComissao->floatToStr('comissao');
            $param['comissaoResidencial'] = $entComissao->floatToStr('comissaoRes');
        } 
      
        // - Cobertura para residencial e comercial parametrizado na tabela Administradora   
        /* @var $entAdm \Livraria\Entity\Administradora */
        $entAdm = $this->getEm()->find('Livraria\Entity\Administradora', $idAdm);
        if($entAdm){
            $param['coberturaComercial'] = $entAdm->getTipoCobertura();
            $param['coberturaResidencial'] = $entAdm->getTipoCoberturaRes();
            $param['seguradora'] = $entAdm->getSeguradora()->getId();
            $param['validade'] = $entAdm->getValidade();                
        } 
        $parray['param'] = $param ;
//        echo '<pre>'; var_dump($parray);die;
        return $parray;
    }
    
    /**
     * Fecha o orçamento e copia os dados para a tabela de fechados
     * @return View
     */
    public function fecharSegurosAction() {
        $data = $this->getRequest()->getPost()->toArray();
        //Pegar Servico de fechados $sf
        //Fechar a lista de orçamentos selecionados.
        $sf = new $this->serviceFechado($this->getEm());
        foreach ($data['Checkeds'] as $idOrc) {
            $resul = $sf->fechaOrcamento($idOrc,FALSE, $this->getServiceLocator());
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
        return $this->redirect()->toRoute($this->route, array('controller' => 'orcamentos', 'action'=>'listarOrcamentos'));
    }
    
    /**
     * Fecha o todos os orçamento e renovaçoes e copia os dados para a tabela de fechados
     * @return View
     */
    public function fecharTodosSegurosAction() {
        $data = $this->getRequest()->getPost()->toArray();
        // Se filtro Status não exitir seta como padrão para Novos.
        if(!isset($data['status'])){
            $data['status'] = "T";
        }
        $inputs = ['id','locador','locatario','refImovel', 'administradora', 'status', 'user','dataI','dataF','validade'];
        foreach ($inputs as $input) {
            if ((isset($data[$input])) AND (!empty($data[$input]))) {
                $filtro[$input] = $data[$input];
            }
        }   
        
        $list = $this->getEm()
                     ->getRepository($this->entity)
                     ->findOrcamento($filtro,[])
                     ->getQuery()
                     ->getResult();
        
        $sf = new $this->serviceFechado($this->getEm());
        
        $sf->setServiceLocator($this->getServiceLocator());  
        $contador = 0 ;
        $indFlush = 50;
        foreach ($list as $obj) {
            $resul = $sf->fechaRapido($obj);
            $this->setMsgSeguroForUser($resul, $obj->getOrcaReno(),$obj->getId()); 
            $contador++;
            if($contador == $indFlush){
                $this->getEm()->flush();
                $contador = 0 ;
            }
        } 
        $this->getEm()->flush();
        
        $sf->logFechaRapido($data);
        
        return $this->redirect()->toRoute($this->route, array('controller' => 'orcamentos', 'action'=>'listarOrcamentos'));
    }
    
    public function setMsgSeguroForUser($resul, $org, $id){
        $origem = ($org == 'orca')? 'Orçamento ' : 'Renovação ';
        if($resul[0] === TRUE){
            $msg = $origem . $id . ' Fechado com sucesso.';
            $this->flashMessenger()->addMessage($msg);
        }else{
            $msg = $origem . $id . ' gerou os seguintes erros!';
            $this->flashMessenger()->addMessage($msg);
            unset($resul[0]);
            foreach ($resul as $value) {
                $this->flashMessenger()->addMessage($value);
            }
        }  
    }


    /**
     * Cancela varios orcamentos selecionados.
     * Exibe alerta com a resultado da ação de cada orçamento.
     * @return object View
     */    
    public function delVariosAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();        
        //Fechar a lista de orçamentos selecionados.
        foreach ($data['Checkeds'] as $idOrc) {        
            //Pegar Servico de orcamento 
            $service = $this->getServiceLocator()->get($this->service);
            $result = $service->delete($idOrc, $data);
            if($result === TRUE){
                $msg = 'Orçamento ' . $idOrc . ' Foi cancelado com sucesso';
                $this->flashMessenger()->addMessage($msg);
            }else{
                $msg = 'Orçamento ' . $idOrc . ' gerou os seguintes erros!';
                $this->flashMessenger()->addMessage($msg);
                foreach ($result as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }         
        }    
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action' => 'listarOrcamentos'));
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
        $imprimeProp = '0'; 
        if(isset($sessionContainer->idOrcamento)){
            $data['id'] = $sessionContainer->idOrcamento;
            $data['subOpcao'] = 'editar';
            if(!isset($sessionContainer->idOrcamentoNoPrint)){
                $imprimeProp = '1';
            }
            unset($sessionContainer->idOrcamento);
            unset($sessionContainer->idOrcamentoNoPrint);
        }
        
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'fechar'){ 
            $servicoFechado = new $this->serviceFechado($this->getEm());
            $resul = $servicoFechado->fechaOrcamento($data['id'], TRUE, $this->getServiceLocator());
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
            /* @var $entity \Livraria\Entity\Orcamento */
            $entity = $repository->find($data['id']);
        }
        
        if(isset($data['seguradora'])){
            $filtroForm['seguradora'] = $data['seguradora'];
        }
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        //Metodo que bloqueia campos da edição caso houver
        //$this->formData->setEdit();
        if($data['subOpcao'] == 'editar'){ 
            $data = $entity->toArray();
            $data['subOpcao'] = 'editar';
            $data['administradora'] = $entity->getAdministradora()->getId();
            $data['status'] = $entity->getStatus();
            $this->formData->setData($data);
            $sessionContainer->administradora = $this->getEm()->getRepository("Livraria\Entity\Administradora")->find($data['administradora'])->toArray();
        }else{
            $this->formData->setData($data);
        }
        $this->formData->setComissao($data);
        
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
        $aviso = '0';
        $this->formData->setEdit();
        if($data['subOpcao'] == 'calcular'){
            if ($this->formData->isValid()){
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->update($data, TRUE);
                if($result === TRUE){
                    $this->formData->setData($service->getNewInputs());
                    $this->flashMessenger()->clearMessages();
                    $aviso = '1';
                }else{
                    foreach ($result as $value) {
                        $this->flashMessenger()->addMessage($value);
                    }
                }
            }else{
                $this->flashMessenger()->addMessage('Primeiro Acerte os erros antes de calcular!!!');
            }
        }
               
        if($data['subOpcao'] == 'salvar'){
            if ($this->formData->isValid()){
                $entity = $this->getEm()->find($this->entity,$data['id']);
                if($entity AND $data['status'] != $entity->getStatus()){
                    $data['status'] = $entity->getStatus();
                    if($data['fechadoId'] != $entity->getFechadoId()){
                        $data['fechadoId'] = $entity->getFechadoId();
                    }
                }
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->update($data);
                if($result === TRUE){
                    $this->formData->setData($service->getNewInputs());
                    $imprimeProp = '1';
                    $this->flashMessenger()->clearMessages();
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
                
        $arrayParam = $this->getCalculoParams($data['administradora']);
        $arrayParam['administradora'] = $sessionContainer->administradora ;
        $arrayParam['imprimeProp'] = $imprimeProp ;
        $arrayParam['avisaCalc'] = $aviso ;
        if(isset($data['comissao'])){
            $arrayParam['comissao'] = $data['comissao'] ;
        }
        if(isset($data['mesNiver'])){
            $arrayParam['mesNiver'] = $data['mesNiver'] ;
        }
        return new ViewModel(array_merge($this->getParamsForView(),$arrayParam)); 
    }
    
    public function deleteAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = $this->getServiceLocator()->get($this->service);
        $result = $service->delete($data['id'], $data);
        if($result === TRUE){
            $this->flashMessenger()->clearMessages();
        }else{
            foreach ($result as $value) {
                $this->flashMessenger()->addMessage($value);
            }
        }
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action' => 'listarOrcamentos'));
        
    }

    public function printPropostaAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        if(!isset($data['id']))
            $data['id'] = '1';
        
        $this->getServiceLocator()->get($this->service)->getPdfOrcamento($data['id']);
    }
    
    public function imprimiSeguroAction(){
        //Nome diferente mas com a mesma função
        $this->printPropostaAction();
    }
    
    public function popupTestAction(){
        echo "<!DOCTYPE html>",
             "<html><head></head><body>teste popup</body></html>";
        die;
    }

    public function reativaAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        /* @var $service \Livraria\Service\Orcamento */
        $service = $this->getServiceLocator()->get($this->service);
        $service->reativar($this,$data);
        
        //Guardar dados dos filtros para paginação
        $this->sc = new SessionContainer("LivrariaAdmin");
        $this->sc->data = $data;
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action' => 'listarOrcamentos'));
        
    }
    public function atualizaSegAction() {
        /* @var $service \Livraria\Service\Orcamento */
        /* @var $orca \Livraria\Entity\Orcamento */
        $service = $this->getServiceLocator()->get($this->service);
        
        $array = [];
/**        
//        $array[] = '1037253';
//        $array[] = '1032361';
//        $array[] = '1032362';
//        $array[] = '1032363';
//        $array[] = '1032369';
//        $array[] = '1032370';
//        $array[] = '1032371';
//        $array[] = '1032372';
//        $array[] = '1032373';
//        $array[] = '1032374';
//        $array[] = '1032548';  
        
//        $array[] = '1032549';
//        $array[] = '1032550';
//        $array[] = '1032558';
//        $array[] = '1032560';
//        $array[] = '1032561';
//        $array[] = '1032565';
//        $array[] = '1032566';
//        $array[] = '1032574';
//        $array[] = '1032575';
//        $array[] = '1032576';
//        $array[] = '1032577';
//        $array[] = '1032580';
//        $array[] = '1032581';
//        $array[] = '1032582';
//        $array[] = '1032583';
//        $array[] = '1032597';
//        $array[] = '1032598';
//        $array[] = '1032599';
//        $array[] = '1032600';
//        $array[] = '1032601';
//        $array[] = '1032603';
//        $array[] = '1032606';
//        $array[] = '1032607';
//        $array[] = '1032608';
//        $array[] = '1032609';
//        $array[] = '1032610';
//        $array[] = '1032611';
//        $array[] = '1032612';
//        $array[] = '1032613';
//        $array[] = '1032615';
//        $array[] = '1032616';
//        $array[] = '1032619';
//        $array[] = '1032620';
//        $array[] = '1032623';
//        $array[] = '1032633';
//        $array[] = '1032634';
//        $array[] = '1032635';
//        $array[] = '1032636';
//        $array[] = '1032637';
//        $array[] = '1032638';
//        $array[] = '1032639';
//        $array[] = '1032640';
//        $array[] = '1032641';
//        $array[] = '1032642';
//        $array[] = '1032643';
//        $array[] = '1032644';
//        $array[] = '1032645';
//        $array[] = '1032646';
//        $array[] = '1032647';
//        $array[] = '1032648';
//        $array[] = '1032649';
//        $array[] = '1032650';
//        $array[] = '1032651';
//        $array[] = '1032652';
//        $array[] = '1032653';
//        $array[] = '1032654';
//        $array[] = '1032655';
//        $array[] = '1032656';
//        $array[] = '1032657';
//        $array[] = '1032658';
//        $array[] = '1032659';
//        $array[] = '1032660';
//        $array[] = '1032661';
//        $array[] = '1032662';
//        $array[] = '1032663';
//        $array[] = '1032664';
//        $array[] = '1032665';
//        $array[] = '1032666';
//        $array[] = '1032667';
//        $array[] = '1032668';
//        $array[] = '1032669';
//        $array[] = '1032670';
//        $array[] = '1032671';
//        $array[] = '1032672';
//        $array[] = '1032673';
//        $array[] = '1032674';
//        $array[] = '1032675';
//        $array[] = '1032676';
//        $array[] = '1032677';
//        $array[] = '1032678';
//        $array[] = '1032681';
//        $array[] = '1032682';
//        $array[] = '1032685';
//        $array[] = '1032686';
//        $array[] = '1032687';
//        $array[] = '1032688';
//        $array[] = '1032692';
//        $array[] = '1032696';
//        $array[] = '1032697';
//        $array[] = '1032698';
//        $array[] = '1032700';
//        $array[] = '1032701';
//        $array[] = '1032702';
//        $array[] = '1032703';
//        $array[] = '1032706';
//        $array[] = '1032707';
//        $array[] = '1032708';
//        $array[] = '1032720';
//        $array[] = '1032724';
//        $array[] = '1032731';
//        $array[] = '1032745';
//        $array[] = '1032760';
//        $array[] = '1032771';
//        $array[] = '1032778';
//        $array[] = '1032781';
//        $array[] = '1032783';
//        $array[] = '1032785';
//        $array[] = '1032786';
//        $array[] = '1032789';
//        $array[] = '1032795';
//        $array[] = '1032797';
//        $array[] = '1032804';
//        $array[] = '1032805';
//        $array[] = '1032806';
//        $array[] = '1032807';
//        $array[] = '1032808';
//        $array[] = '1032809';
//        $array[] = '1032810';
//        $array[] = '1032811';
//        $array[] = '1032813';
//        $array[] = '1032814';
//        $array[] = '1032815';
//        $array[] = '1032816';
//        $array[] = '1032817';
//        $array[] = '1032818';
//        $array[] = '1032819';
//        $array[] = '1032820';
//        $array[] = '1032821';
//        $array[] = '1032822';
//        $array[] = '1032823';
//        $array[] = '1032824';
//        $array[] = '1032825';
//        $array[] = '1032826';
//        $array[] = '1032827';
//        $array[] = '1032828';
//        $array[] = '1032829';
//        $array[] = '1032830';
//        $array[] = '1032831';
//        $array[] = '1032832';
//        $array[] = '1032836';
//        $array[] = '1032839';
//        $array[] = '1032842';
//        $array[] = '1032843';
//        $array[] = '1032845';
//        $array[] = '1032854';
//        $array[] = '1032857';
//        $array[] = '1032861';
//        $array[] = '1032865';
//        $array[] = '1032867';
//        $array[] = '1032868';
//        $array[] = '1032869';
//        $array[] = '1032872';
//        $array[] = '1032875';
//        $array[] = '1032876';
//        $array[] = '1032877';
//        $array[] = '1032879';
//        $array[] = '1032880';
//        $array[] = '1032882';
//        $array[] = '1032883';
//        $array[] = '1032885';
//        $array[] = '1032887';
//        $array[] = '1032888';
//        $array[] = '1032889';
//        $array[] = '1032891';
//        $array[] = '1032892';
//        $array[] = '1032895';
//        $array[] = '1032896';
//        $array[] = '1032898';
//        $array[] = '1032899';
//        $array[] = '1032900';
//        $array[] = '1032903';
//        $array[] = '1032906';
//        $array[] = '1032908';
//        $array[] = '1032910';
//        $array[] = '1032914';
//        $array[] = '1032922';
//        $array[] = '1032926';
//        $array[] = '1032927';
//        $array[] = '1032929';
//        $array[] = '1032931';
//        $array[] = '1032934';
//        $array[] = '1032935';
//        $array[] = '1032936';
//        $array[] = '1032937';
//        $array[] = '1032938';
//        $array[] = '1032940';
//        $array[] = '1032944';
//        $array[] = '1032945';
//        $array[] = '1032946';
//        $array[] = '1032950';
//        $array[] = '1032951';
//        $array[] = '1032952';
//        $array[] = '1032954';
//        $array[] = '1032956';
//        $array[] = '1032960';
//        $array[] = '1032961';
//        $array[] = '1032962';
//        $array[] = '1032963';
//        $array[] = '1032964';
//        $array[] = '1032965';
//        $array[] = '1032966';
//        $array[] = '1032967';
//        $array[] = '1032968';
//        $array[] = '1032969';
//        $array[] = '1032970';
//        $array[] = '1032971';
//        $array[] = '1032972';
//        $array[] = '1032973';
//        $array[] = '1032974';
//        $array[] = '1032975';
//        $array[] = '1032976';
//        $array[] = '1033008';
//        $array[] = '1033009';
//        $array[] = '1033018';
//        $array[] = '1033020';
//        $array[] = '1033021';
//        $array[] = '1033025';
//        $array[] = '1033027';
//        $array[] = '1033029';
//        $array[] = '1033035';
//        $array[] = '1033036';
//        $array[] = '1035128';
//        $array[] = '1035129';
//        $array[] = '1035161';
//        $array[] = '1036079';
//        $array[] = '1036080';
//        $array[] = '1036081';
//        $array[] = '1036082';
//        $array[] = '1036175';
//        $array[] = '1036877';
//        $array[] = '1036878';
//        $array[] = '1036879';
//        $array[] = '1036880';
//        $array[] = '1036881';
//        $array[] = '1036882';
//        $array[] = '1036883';
//        $array[] = '1036884';
//        $array[] = '1036885';
//        $array[] = '1036886';
//        $array[] = '1036887';
//        $array[] = '1036888';
//        $array[] = '1036889';
//        $array[] = '1036890';
//        $array[] = '1036891';
//        $array[] = '1036892';
//        $array[] = '1036893';
//        $array[] = '1036894';
//        $array[] = '1036895';
//        $array[] = '1036896';
//        $array[] = '1036897';
//        $array[] = '1036898';
//        $array[] = '1036899';
//        $array[] = '1036900';
//        $array[] = '1036901';
//        $array[] = '1036902';
//        $array[] = '1036903';
//        $array[] = '1036904';
//        $array[] = '1036905';
//        $array[] = '1036906';
//        $array[] = '1036907';
//        $array[] = '1036908';
//        $array[] = '1036909';
//        $array[] = '1036910';
//        $array[] = '1036911';
//        $array[] = '1036912';
//        $array[] = '1036913';
//        $array[] = '1036915';
//        $array[] = '1036916';
//        $array[] = '1036918';
//        $array[] = '1036919';
//        $array[] = '1036920';
//        $array[] = '1036921';
//        $array[] = '1036922';
//        $array[] = '1036923';
//        $array[] = '1036924';
//        $array[] = '1036925';
//        $array[] = '1036926';
//        $array[] = '1036927';
//        $array[] = '1036928';
//        $array[] = '1036929';
//        $array[] = '1036931';
//        $array[] = '1036932';
//        $array[] = '1036933';
//        $array[] = '1036934';
//        $array[] = '1036935';
//        $array[] = '1036936';
//        $array[] = '1036938';
//        $array[] = '1036939';
//        $array[] = '1036940';
//        $array[] = '1036941';
//        $array[] = '1036942';
//        $array[] = '1036944';
//        $array[] = '1036945';
//        $array[] = '1036947';
//        $array[] = '1036948';
//        $array[] = '1036949';
//        $array[] = '1036950';
//        $array[] = '1036951';
//        $array[] = '1036952';
//        $array[] = '1036953';
//        $array[] = '1036954';
//        $array[] = '1036955';
//        $array[] = '1036956';
//        $array[] = '1036957';
//        $array[] = '1036958';
//        $array[] = '1036959';
//        $array[] = '1036960';
//        $array[] = '1036961';
//        $array[] = '1036962';
//        $array[] = '1036963';
//        $array[] = '1036964';
//        $array[] = '1036965';
//        $array[] = '1036967';
//        $array[] = '1036968';
//        $array[] = '1036969';
//        $array[] = '1036970';
//        $array[] = '1036971';
//        $array[] = '1036972';
//        $array[] = '1036973';
//        $array[] = '1036974';
//        $array[] = '1036975';
//        $array[] = '1036976';
//        $array[] = '1036977';
//        $array[] = '1036978';
//        $array[] = '1036979';
//        $array[] = '1036980';
//        $array[] = '1036981';
//        $array[] = '1036982';
//        $array[] = '1036983';
//        $array[] = '1036984';
//        $array[] = '1036985';
//        $array[] = '1036987';
//        $array[] = '1036988';
//        $array[] = '1036989';
//        $array[] = '1036990';
//        $array[] = '1036991';
//        $array[] = '1036992';
//        $array[] = '1036993';
//        $array[] = '1036994';
//        $array[] = '1036995';
//        $array[] = '1036997';
//        $array[] = '1036998';
//        $array[] = '1036999';
//        $array[] = '1037000';
//        $array[] = '1037001';
//        $array[] = '1037002';
//        $array[] = '1037003';
//        $array[] = '1037004';
//        $array[] = '1037005';
//        $array[] = '1037006';
//        $array[] = '1037007';
//        $array[] = '1037008';
//        $array[] = '1037009';
//        $array[] = '1037010';
//        $array[] = '1037011';
//        $array[] = '1037012';
//        $array[] = '1037013';
//        $array[] = '1037014';
//        $array[] = '1037015';
//        $array[] = '1037016';
//        $array[] = '1037017';
//        $array[] = '1037018';
//        $array[] = '1037019';
//        $array[] = '1037020';
//        $array[] = '1037021';
//        $array[] = '1037022';
//        $array[] = '1037023';
//        $array[] = '1037024';
//        $array[] = '1037025';
//        $array[] = '1037027';
//        $array[] = '1037028';
//        $array[] = '1037029';
//        $array[] = '1037030';
//        $array[] = '1037031';
//        $array[] = '1037033';
//        $array[] = '1037034';
//        $array[] = '1037035';
//        $array[] = '1037036';
//        $array[] = '1037038';
//        $array[] = '1037039';
//        $array[] = '1037040';
//        $array[] = '1037041';
//        $array[] = '1037042';
//        $array[] = '1037043';
//        $array[] = '1037044';
//        $array[] = '1037045';
//        $array[] = '1037046';
//        $array[] = '1037047';
//        $array[] = '1037048';
//        $array[] = '1037049';
//        $array[] = '1037050';
//        $array[] = '1037051';
//        $array[] = '1037052';
//        $array[] = '1037053';
//        $array[] = '1037054';
//        $array[] = '1037055';
//        $array[] = '1037058';
//        $array[] = '1037060';
//        $array[] = '1037063';
//        $array[] = '1037064';
//        $array[] = '1037065';
//        $array[] = '1037067';
//        $array[] = '1037068';
//        $array[] = '1037069';
//        $array[] = '1037070';
//        $array[] = '1037071';
//        $array[] = '1037072';
//        $array[] = '1037073';
//        $array[] = '1037074';
//        $array[] = '1037075';
//        $array[] = '1037076';
//        $array[] = '1037077';
//        $array[] = '1037078';
//        $array[] = '1037079';
//        $array[] = '1037080';
//        $array[] = '1037081';
//        $array[] = '1037082';
//        $array[] = '1037083';
//        $array[] = '1037084';
//        $array[] = '1037085';
//        $array[] = '1037086';
//        $array[] = '1037087';
//        $array[] = '1037088';
//        $array[] = '1037089';
//        $array[] = '1037090';
//        $array[] = '1037091';
//        $array[] = '1037092';
//        $array[] = '1037093';
//        $array[] = '1037094';
//        $array[] = '1037095';
//        $array[] = '1037097';
//        $array[] = '1037098';
//        $array[] = '1037099';
//        $array[] = '1037101';
//        $array[] = '1037102';
//        $array[] = '1037103';
//        $array[] = '1037104';
//        $array[] = '1037105';
//        $array[] = '1037106';
//        $array[] = '1037107';
//        $array[] = '1037108';
//        $array[] = '1037109';
//        $array[] = '1037110';
//        $array[] = '1037111';
//        $array[] = '1037112';
//        $array[] = '1037113';
//        $array[] = '1037114';
//        $array[] = '1037115';
//        $array[] = '1037116';
//        $array[] = '1037117';
//        $array[] = '1037118';
//        $array[] = '1037119';
//        $array[] = '1037120';
//        $array[] = '1037121';
//        $array[] = '1037122';
//        $array[] = '1037123';
//        $array[] = '1037124';
//        $array[] = '1037125';
//        $array[] = '1037126';
//        $array[] = '1037127';
//        $array[] = '1037128';
//        $array[] = '1037129';
//        $array[] = '1037130';
//        $array[] = '1037131';
//        $array[] = '1037132';
//        $array[] = '1037133';
//        $array[] = '1037134';
//        $array[] = '1037135';
//        $array[] = '1037137';
//        $array[] = '1037138';
//        $array[] = '1037139';
//        $array[] = '1037140';
//        $array[] = '1037141';
//        $array[] = '1037142';
//        $array[] = '1037144';
//        $array[] = '1037146';
//        $array[] = '1037147';
//        $array[] = '1037148';
//        $array[] = '1037149';
//        $array[] = '1037150';
//        $array[] = '1037151';
//        $array[] = '1037152';
//        $array[] = '1037153';
//        $array[] = '1037154';
//        $array[] = '1037155';
//        $array[] = '1037156';
//        $array[] = '1037157';
//        $array[] = '1037158';
//        $array[] = '1037159';
//        $array[] = '1037160';
//        $array[] = '1037161';
//        $array[] = '1037162';
//        $array[] = '1037163';
//        $array[] = '1037164';
//        $array[] = '1037168';
//        $array[] = '1037169';
//        $array[] = '1037170';
//        $array[] = '1037171';
//        $array[] = '1037172';
//        $array[] = '1037173';
//        $array[] = '1037174';
//        $array[] = '1037175';
//        $array[] = '1037176';
//        $array[] = '1037177';
//        $array[] = '1037178';
//        $array[] = '1037179';
//        $array[] = '1037180';
//        $array[] = '1037181';
//        $array[] = '1037182';
//        $array[] = '1037183';
//        $array[] = '1037184';
//        $array[] = '1037185';
//        $array[] = '1037186';
//        $array[] = '1037187';
//        $array[] = '1037188';
//        $array[] = '1037189';
//        $array[] = '1037190';
//        $array[] = '1037191';
//        $array[] = '1037192';
//        $array[] = '1037193';
//        $array[] = '1037194';
//        $array[] = '1037196';
//        $array[] = '1037197';
//        $array[] = '1037198';
//        $array[] = '1037199';
//        $array[] = '1037200';
//        $array[] = '1037201';
//        $array[] = '1037202';
//        $array[] = '1037203';
//        $array[] = '1037204';
//        $array[] = '1037205';
//        $array[] = '1037206';
//        $array[] = '1037207';
//        $array[] = '1037208';
//        $array[] = '1037209';
//        $array[] = '1037210';
//        $array[] = '1037211';
//        $array[] = '1037212';
//        $array[] = '1037213';
//        $array[] = '1037215';
//        $array[] = '1037216';
//        $array[] = '1037217';
//        $array[] = '1037218';
//        $array[] = '1037219';
//        $array[] = '1037220';
//        $array[] = '1037221';
//        $array[] = '1037222';
//        $array[] = '1037223';
//        $array[] = '1037224';
//        $array[] = '1037225';
//        $array[] = '1037226';
//        $array[] = '1037227';
//        $array[] = '1037228';
//        $array[] = '1037229';
//        $array[] = '1037230';
//        $array[] = '1037231';
//        $array[] = '1037232';
//        $array[] = '1037233';
//        $array[] = '1037234';
//        $array[] = '1037235';
//        $array[] = '1037236';
//        $array[] = '1037237';
//        $array[] = '1037238';
//        $array[] = '1037239';
//        $array[] = '1037240';
//        $array[] = '1037242';
//        $array[] = '1037243';
//        $array[] = '1037244';
//        $array[] = '1037245';
//        $array[] = '1037246';
//        $array[] = '1037247';
//        $array[] = '1037248';
//        $array[] = '1037249';
//        $array[] = '1037250';
//        $array[] = '1037251';
//        $array[] = '1037252';
//        $array[] = '1037254';
//        $array[] = '1037255';
//        $array[] = '1037256';
//        $array[] = '1037257';
//        $array[] = '1037258';
//        $array[] = '1037259';
//        $array[] = '1037260';
//        $array[] = '1037261';
//        $array[] = '1037262';
//        $array[] = '1037263';
//        $array[] = '1037264';
//        $array[] = '1037265';
//        $array[] = '1037266';
//        $array[] = '1037267';
//        $array[] = '1037268';
//        $array[] = '1037269';
//        $array[] = '1037270';
//        $array[] = '1037271';
//        $array[] = '1037272';
//        $array[] = '1037274';
//        $array[] = '1037277';
//        $array[] = '1037278';
//        $array[] = '1037279';
//        $array[] = '1037281';
//        $array[] = '1037282';
//        $array[] = '1037283';
//        $array[] = '1037284';
//        $array[] = '1037285';
//        $array[] = '1037286';
//        $array[] = '1037287';
//        $array[] = '1037288';
//        $array[] = '1037289';
//        $array[] = '1037290';
//        $array[] = '1037292';
//        $array[] = '1037293';
*/
        
/**
 * 
//$array[] = '1032718';
//$array[] = '1032812';
//$array[] = '1032878';
//$array[] = '1032881';
//$array[] = '1036914';
//$array[] = '1036917';
//$array[] = '1036930';
//$array[] = '1036937';
//$array[] = '1036943';
//$array[] = '1036946';
//$array[] = '1036966';
//$array[] = '1036986';
//$array[] = '1036996';
//$array[] = '1037026';
//$array[] = '1037032';
//$array[] = '1037037';
//$array[] = '1037056';
//$array[] = '1037057';
//$array[] = '1037059';
//$array[] = '1037061';
//$array[] = '1037062';
//$array[] = '1037066';
//$array[] = '1037096';
//$array[] = '1037100';
//$array[] = '1037136';
//$array[] = '1037143';
//$array[] = '1037145';
//$array[] = '1037165';
//$array[] = '1037166';
//$array[] = '1037167';
//$array[] = '1037195';
//$array[] = '1037214';
//$array[] = '1037241';
//$array[] = '1037273';
//$array[] = '1037275';
//$array[] = '1037276';
//$array[] = '1037280';
//$array[] = '1037291';
*/
        
/**
 *         
//$array[] = '1036510';
//$array[] = '1036511';
//$array[] = '1036512';
//$array[] = '1036513';
//$array[] = '1036514';
//$array[] = '1036515';
//$array[] = '1036516';
//$array[] = '1036517';
//$array[] = '1036518';
//$array[] = '1036519';
//$array[] = '1036520';
//$array[] = '1036521';
//$array[] = '1036522';
//$array[] = '1036523';
//$array[] = '1036524';
//$array[] = '1036525';
//$array[] = '1036526';
//$array[] = '1036527';
//$array[] = '1036528';
//$array[] = '1036529';
//$array[] = '1036530';
//$array[] = '1036531';
//$array[] = '1036532';
//$array[] = '1036533';
//$array[] = '1036534';
//$array[] = '1036535';
//$array[] = '1036536';
//$array[] = '1036537';
//$array[] = '1036538';
//$array[] = '1036539';
//$array[] = '1036540';
//$array[] = '1036541';
//$array[] = '1036542';
//$array[] = '1036543';
//$array[] = '1036544';
//$array[] = '1036545';
//$array[] = '1036546';
//$array[] = '1036547';
//$array[] = '1036548';
//$array[] = '1036549';
//$array[] = '1036550';
//$array[] = '1036551';
//$array[] = '1036552';
//$array[] = '1036553';
//$array[] = '1036554';
//$array[] = '1036555';
//$array[] = '1036556';
//$array[] = '1036557';
//$array[] = '1036558';
//$array[] = '1036559';
//$array[] = '1036560';
//$array[] = '1036561';
//$array[] = '1036562';
//$array[] = '1036563';
//$array[] = '1036564';
//$array[] = '1036565';
//$array[] = '1036566';
//$array[] = '1036567';
//$array[] = '1036568';
//$array[] = '1036569';
//$array[] = '1036570';
//$array[] = '1036571';
//$array[] = '1036572';
//$array[] = '1036573';
//$array[] = '1036574';
//$array[] = '1036575';
//$array[] = '1036576';
//$array[] = '1036577';
//$array[] = '1036578';
//$array[] = '1036579';
//$array[] = '1036580';
//$array[] = '1036581';
//$array[] = '1036582';
//$array[] = '1036583';
//$array[] = '1036584';
//$array[] = '1036585';
//$array[] = '1036586';
//$array[] = '1036587';
//$array[] = '1036588';
//$array[] = '1036589';
//$array[] = '1036590';
//$array[] = '1036591';
//$array[] = '1036592';
//$array[] = '1036593';
//$array[] = '1036594';
//$array[] = '1036595';
//$array[] = '1036596';
//$array[] = '1036597';
//$array[] = '1036598';
//$array[] = '1036599';
//$array[] = '1036600';
//$array[] = '1036601';
//$array[] = '1036602';
//$array[] = '1036603';
//$array[] = '1036604';
//$array[] = '1036605';
//$array[] = '1036606';
//$array[] = '1036607';
//$array[] = '1036608';
//$array[] = '1036609';
//$array[] = '1036610';
//$array[] = '1036611';
//$array[] = '1036612';
//$array[] = '1036613';
//$array[] = '1036614';
//$array[] = '1036615';
//$array[] = '1036616';
//$array[] = '1036617';
//$array[] = '1036618';
//$array[] = '1036619';
//$array[] = '1036620';
//$array[] = '1036621';
//$array[] = '1036622';
//$array[] = '1036623';
//$array[] = '1036624';
//$array[] = '1036625';
//$array[] = '1036626';
//$array[] = '1036627';
//$array[] = '1036628';
//$array[] = '1036629';
//$array[] = '1036630';
//$array[] = '1036631';
//$array[] = '1036632';
//$array[] = '1036633';
//$array[] = '1036634';
//$array[] = '1036635';
//$array[] = '1036636';
//$array[] = '1036637';
//$array[] = '1036638';
//$array[] = '1036639';
//$array[] = '1036640';
//$array[] = '1036641';
//$array[] = '1036642';
//$array[] = '1036643';
//$array[] = '1036644';
//$array[] = '1036645';
//$array[] = '1036646';
//$array[] = '1036647';
//$array[] = '1036648';
//$array[] = '1036649';
//$array[] = '1036650';
//$array[] = '1036651';
//$array[] = '1036652';
//$array[] = '1036653';
//$array[] = '1036654';
//$array[] = '1036655';
//$array[] = '1036656';
//$array[] = '1036657';
//$array[] = '1036658';
//$array[] = '1036659';
//$array[] = '1036660';
//$array[] = '1036661';
//$array[] = '1036662';
//$array[] = '1036663';
//$array[] = '1036664';
//$array[] = '1036665';
//$array[] = '1036666';
//$array[] = '1036667';
//$array[] = '1036668';
//$array[] = '1036669';
//$array[] = '1036670';
//$array[] = '1036671';
//$array[] = '1036672';
//$array[] = '1036673';
//$array[] = '1036674';
//$array[] = '1036675';
//$array[] = '1036676';
//$array[] = '1036677';
//$array[] = '1036678';
//$array[] = '1036679';
//$array[] = '1036680';
//$array[] = '1036681';
//$array[] = '1036682';
//$array[] = '1036683';
//$array[] = '1036684';
//$array[] = '1036685';
//$array[] = '1036686';
//$array[] = '1036687';
//$array[] = '1036688';
//$array[] = '1036689';
//$array[] = '1036690';
//$array[] = '1036691';
//$array[] = '1036692';
//$array[] = '1036693';
//$array[] = '1036694';
//$array[] = '1036695';
//$array[] = '1036696';
//$array[] = '1036697';
//$array[] = '1036698';
//$array[] = '1036699';
//$array[] = '1036700';
//$array[] = '1036701';
//$array[] = '1036702';
//$array[] = '1036703';
//$array[] = '1036704';
//$array[] = '1036705';
//$array[] = '1036706';
//$array[] = '1036707';
//$array[] = '1036708';
//$array[] = '1036709';
//$array[] = '1036710';
//$array[] = '1036711';
//$array[] = '1036712';
//$array[] = '1036713';
//$array[] = '1036714';
//$array[] = '1036715';
//$array[] = '1036716';
//$array[] = '1036717';
//$array[] = '1036718';
//$array[] = '1036719';
//$array[] = '1036720';
//$array[] = '1036721';
//$array[] = '1036722';
//$array[] = '1036723';
//$array[] = '1036724';
//$array[] = '1036725';
//$array[] = '1036726';
//$array[] = '1036727';
//$array[] = '1036728';
//$array[] = '1036729';
//$array[] = '1036730';
//$array[] = '1036731';
//$array[] = '1036732';
//$array[] = '1036733';
//$array[] = '1036734';
//$array[] = '1036735';
//$array[] = '1036736';
//$array[] = '1036737';
//$array[] = '1036738';
//$array[] = '1036739';
//$array[] = '1036740';
//$array[] = '1036741';
//$array[] = '1036742';
//$array[] = '1036743';
//$array[] = '1036744';
//$array[] = '1036745';
//$array[] = '1036746';
//$array[] = '1036747';
//$array[] = '1036748';
//$array[] = '1036749';
//$array[] = '1036750';
//$array[] = '1036751';
//$array[] = '1036752';
//$array[] = '1036753';
//$array[] = '1036754';
//$array[] = '1036755';
//$array[] = '1036756';
//$array[] = '1036757';
//$array[] = '1036758';
//$array[] = '1036759';
//$array[] = '1036760';
//$array[] = '1036761';
//$array[] = '1036762';
//$array[] = '1036763';
//$array[] = '1036764';
//$array[] = '1036765';
//$array[] = '1036766';
//$array[] = '1036767';
//$array[] = '1036768';
//$array[] = '1036769';
//$array[] = '1036770';
//$array[] = '1036771';
//$array[] = '1036772';
//$array[] = '1036773';
//$array[] = '1036774';
//$array[] = '1036775';
//$array[] = '1036776';
//$array[] = '1036777';
//$array[] = '1036778';
//$array[] = '1036779';
//$array[] = '1036780';
//$array[] = '1036781';
//$array[] = '1036782';
//$array[] = '1036783';
//$array[] = '1036784';
//$array[] = '1036785';
//$array[] = '1036786';
//$array[] = '1036787';
//$array[] = '1036788';
//$array[] = '1036789';
//$array[] = '1036790';
//$array[] = '1036791';
//$array[] = '1036792';
//$array[] = '1036793';
//$array[] = '1036794';
//$array[] = '1036795';
//$array[] = '1036796';
//$array[] = '1036797';
//$array[] = '1036798';
//$array[] = '1036799';
//$array[] = '1036800';
//$array[] = '1036801';
//$array[] = '1036802';
//$array[] = '1036803';
//$array[] = '1036804';
//$array[] = '1036805';
//$array[] = '1036806';
//$array[] = '1036807';
//$array[] = '1036808';
//$array[] = '1036809';
//$array[] = '1036810';
//$array[] = '1036811';
//$array[] = '1036812';
//$array[] = '1036813';
//$array[] = '1036814';
//$array[] = '1036815';
//$array[] = '1036816';
//$array[] = '1036817';
//$array[] = '1036818';
//$array[] = '1036819';
//$array[] = '1036820';
//$array[] = '1036821';
//$array[] = '1036822';
//$array[] = '1036823';
//$array[] = '1036824';
//$array[] = '1036825';
//$array[] = '1036826';
//$array[] = '1036827';
//$array[] = '1036828';
//$array[] = '1036829';
//$array[] = '1036830';
//$array[] = '1036831';
//$array[] = '1036832';
//$array[] = '1036833';
//$array[] = '1036834';
//$array[] = '1036835';
//$array[] = '1036836';
//$array[] = '1036837';
//$array[] = '1036838';
//$array[] = '1036839';
//$array[] = '1036840';
//$array[] = '1036841';
//$array[] = '1036842';
//$array[] = '1036843';
//$array[] = '1036844';
//$array[] = '1036845';
//$array[] = '1036846';
//$array[] = '1036847';
//$array[] = '1036848';
//$array[] = '1036849';
//$array[] = '1036850';
//$array[] = '1036851';
//$array[] = '1036852';
//$array[] = '1036853';
//$array[] = '1036854';
//$array[] = '1036855';
//$array[] = '1036856';
//$array[] = '1036857';
//$array[] = '1036858';
//$array[] = '1036859';
//$array[] = '1036860';
//$array[] = '1036861';
//$array[] = '1036862';
//$array[] = '1036863';
//$array[] = '1036864';
//$array[] = '1036865';
//$array[] = '1036866';
//$array[] = '1036867';
//$array[] = '1036868';
//$array[] = '1036869';
//$array[] = '1036870';
//$array[] = '1036871';
//$array[] = '1036872';
//$array[] = '1036873';
//$array[] = '1036874';
//$array[] = '1036875';
//$array[] = '1036876';   
*/        

/**
 *  NDN        
//        					$array[] = '1026812';
//						$array[] = '1032238';
//						$array[] = '1032239';
//						$array[] = '1035194';
//						$array[] = '1035742';
//						$array[] = '1035746';
//						$array[] = '1037294';
//						$array[] = '1037295';
//						$array[] = '1037296';
//						$array[] = '1037297';
//						$array[] = '1037298';
//						$array[] = '1037299';
//						$array[] = '1037301';
//						$array[] = '1037302';
//						$array[] = '1037303';
//						$array[] = '1037304';
//						$array[] = '1037305';
//						$array[] = '1037306';
//						$array[] = '1037307';
//						$array[] = '1037308';
//						$array[] = '1037309';
//						$array[] = '1037310';
//						$array[] = '1037311';
//						$array[] = '1037312';
//						$array[] = '1037313';
//						$array[] = '1037314';
//						$array[] = '1037315';
//						$array[] = '1037316';
//						$array[] = '1037317';
//						$array[] = '1037318';
//						$array[] = '1037319';
//						$array[] = '1037320';
//						$array[] = '1037321';
//						$array[] = '1037322';
//						$array[] = '1037323';
//						$array[] = '1037324';
//						$array[] = '1037325';
//						$array[] = '1037326';
//						$array[] = '1037327';
//						$array[] = '1037328';
//						$array[] = '1037329';
//						$array[] = '1037330';
//						$array[] = '1037331';
//						$array[] = '1037332';
//						$array[] = '1037333';
//						$array[] = '1037334';
//						$array[] = '1037335';
//						$array[] = '1037336';
//						$array[] = '1037337';
//						$array[] = '1037338';
//						$array[] = '1037339';
//						$array[] = '1037340';
//						$array[] = '1037341';
//						$array[] = '1037342';
//						$array[] = '1037343';
//						$array[] = '1037344';
//						$array[] = '1037345';
//						$array[] = '1037346';
//						$array[] = '1037347';
//						$array[] = '1037348';
//						$array[] = '1037349';
//						$array[] = '1037350';
//						$array[] = '1037351';
//						$array[] = '1037352';
//						$array[] = '1037353';
//						$array[] = '1037354';
//						$array[] = '1037355';
//						$array[] = '1037356';
//						$array[] = '1037357';
//						$array[] = '1037358';
//						$array[] = '1037359';
//						$array[] = '1037360';
//						$array[] = '1037361';
//						$array[] = '1037362';
//						$array[] = '1037363';
//						$array[] = '1037364';
//						$array[] = '1037365';
//						$array[] = '1037366';
//						$array[] = '1037367';
//						$array[] = '1037368';
//						$array[] = '1037369';
//						$array[] = '1037370';
//						$array[] = '1037371';
//						$array[] = '1037372';
//						$array[] = '1037373';
//						$array[] = '1037374';
//						$array[] = '1037375';
//						$array[] = '1037376';
//						$array[] = '1037377';
//						$array[] = '1037378';
//						$array[] = '1037379';
//						$array[] = '1037380';
//						$array[] = '1037381';
//						$array[] = '1037382';
//						$array[] = '1037383';
//						$array[] = '1037384';
//						$array[] = '1037385';
//						$array[] = '1037386';
//						$array[] = '1037387';
//						$array[] = '1037388';
//						$array[] = '1037389';
//						$array[] = '1037390';
//						$array[] = '1037391';
//						$array[] = '1037392';
//						$array[] = '1037393';
//						$array[] = '1037394';
//						$array[] = '1037395';
//						$array[] = '1037396';
//						$array[] = '1037397';
//						$array[] = '1037398';
//						$array[] = '1037399';
//						$array[] = '1037400';
//						$array[] = '1037401';
//						$array[] = '1037402';
//						$array[] = '1037403';
//						$array[] = '1037404';
//						$array[] = '1037405';
//						$array[] = '1037406';
//						$array[] = '1037407';
//						$array[] = '1037408';
*/        

/**
 * 
// CONDOVEL MES 5
//$array[] = '1047775';
//$array[] = '1047780';
//$array[] = '1047781';
//$array[] = '1047783';
//$array[] = '1047784';
//$array[] = '1047785';
//$array[] = '1047786';
//$array[] = '1047787';
//$array[] = '1047788';
//$array[] = '1047789';
//$array[] = '1047791';
//$array[] = '1047793';
//$array[] = '1047795';
//$array[] = '1047797';
//$array[] = '1047798';
//$array[] = '1047799';
//$array[] = '1047806';
//$array[] = '1047809';
//$array[] = '1047811';
//$array[] = '1047812';
//$array[] = '1047813';
//$array[] = '1047815';
//$array[] = '1047816';
//$array[] = '1047992';
//$array[] = '1047993';
//$array[] = '1047996';
//$array[] = '1047997';
//$array[] = '1047998';
//$array[] = '1048021';
//$array[] = '1048087';
//$array[] = '1048119';
//$array[] = '1048120';
//$array[] = '1048121';
//$array[] = '1048122';
//$array[] = '1048123';
//$array[] = '1048124';
//$array[] = '1048125';
//$array[] = '1048126';
//$array[] = '1048127';
//$array[] = '1048128';
//$array[] = '1048129';
//$array[] = '1048131';
//$array[] = '1048132';
//$array[] = '1048133';
//$array[] = '1048134';
//$array[] = '1048135';
//$array[] = '1048136';
//$array[] = '1048137';
//$array[] = '1048138';
//$array[] = '1048139';
//$array[] = '1048140';
//$array[] = '1048141';
//$array[] = '1048142';
//$array[] = '1048143';
//$array[] = '1048144';
//$array[] = '1048145';
//$array[] = '1048146';
//$array[] = '1048147';
//$array[] = '1048148';
//$array[] = '1048149';
//$array[] = '1048150';
//$array[] = '1048151';
//$array[] = '1048152';
//$array[] = '1048153';
//$array[] = '1048154';
//$array[] = '1048155';
//$array[] = '1048156';
//$array[] = '1048157';
//$array[] = '1048158';
//$array[] = '1048159';
//$array[] = '1048160';
//$array[] = '1048161';
//$array[] = '1048162';
//$array[] = '1048163';
//$array[] = '1048164';
//$array[] = '1048165';
//$array[] = '1048166';
//$array[] = '1048167';
//$array[] = '1048168';
//$array[] = '1048169';
//$array[] = '1048170';
//$array[] = '1048171';
//$array[] = '1048172';
//$array[] = '1048173';
//$array[] = '1048174';
//$array[] = '1048175';
//$array[] = '1048176';
//$array[] = '1048177';
//$array[] = '1048178';
//$array[] = '1048179';
//$array[] = '1048180';
//$array[] = '1048181';
//$array[] = '1048182';
//$array[] = '1048183';
//$array[] = '1048184';
//$array[] = '1048185';
//$array[] = '1048186';
//$array[] = '1048187';
//$array[] = '1048188';
//$array[] = '1048189';
//$array[] = '1048190';
//$array[] = '1048191';
//$array[] = '1048192';
//$array[] = '1048193';
//$array[] = '1048194';
//$array[] = '1048195';
//$array[] = '1048196';
//$array[] = '1048197';
//$array[] = '1048198';
//$array[] = '1048199';
//$array[] = '1048200';
//$array[] = '1048201';
//$array[] = '1048202';
//$array[] = '1048203';
//$array[] = '1048204';
//$array[] = '1048205';
//$array[] = '1048206';
//$array[] = '1048207';
//$array[] = '1048208';
//$array[] = '1048209';
//$array[] = '1048210';
//$array[] = '1048211';
//$array[] = '1048212';
//$array[] = '1048213';
//$array[] = '1048214';
//$array[] = '1048215';
//$array[] = '1048216';
//$array[] = '1048217';
//$array[] = '1048218';
//$array[] = '1048219';
//$array[] = '1048220';
//$array[] = '1048221';
//$array[] = '1048222';
//$array[] = '1048223';
//$array[] = '1048224';
//$array[] = '1048225';
//$array[] = '1048226';
//$array[] = '1048227';
//$array[] = '1048228';
//$array[] = '1048229';
//$array[] = '1048230';
//$array[] = '1048231';
//$array[] = '1048232';
//$array[] = '1048233';
//$array[] = '1048234';
//$array[] = '1048235';
//$array[] = '1048236';
//$array[] = '1048237';
//$array[] = '1048238';
//$array[] = '1048239';
//$array[] = '1048240';
//$array[] = '1048241';
//$array[] = '1048242';
//$array[] = '1048243';
//$array[] = '1048244';
//$array[] = '1048245';
//$array[] = '1048246';
//$array[] = '1048247';
//$array[] = '1048248';
//$array[] = '1048249';
//$array[] = '1048250';
//$array[] = '1048251';
//$array[] = '1048252';
//$array[] = '1048253';
//$array[] = '1048254';
//$array[] = '1048255';
//$array[] = '1048256';
//$array[] = '1048257';
//$array[] = '1048258';
//$array[] = '1048259';
//$array[] = '1048260';
//$array[] = '1048261';
//$array[] = '1048262';
//$array[] = '1048263';
//$array[] = '1048264';
//$array[] = '1048265';
//$array[] = '1048266';
//$array[] = '1048267';
//$array[] = '1048268';
//$array[] = '1048269';
//$array[] = '1048270';
//$array[] = '1048271';
//$array[] = '1048272';
//$array[] = '1048273';
//$array[] = '1048274';
//$array[] = '1048275';
//$array[] = '1048276';
//$array[] = '1048277';
//$array[] = '1048278';
//$array[] = '1048279';
//$array[] = '1048280';
//$array[] = '1048281';
//$array[] = '1048282';
//$array[] = '1048283';
//$array[] = '1048284';
//$array[] = '1048285';
//$array[] = '1048286';
//$array[] = '1048287';
//$array[] = '1048288';
//$array[] = '1048289';
//$array[] = '1048290';
//$array[] = '1048291';
//$array[] = '1048292';
//$array[] = '1048293';
//$array[] = '1048294';
//$array[] = '1048295';
//$array[] = '1048296';
//$array[] = '1048297';
//$array[] = '1048298';
//$array[] = '1048299';
//$array[] = '1048300';
//$array[] = '1048301';
//$array[] = '1048302';
//$array[] = '1048303';
//$array[] = '1048304';
//$array[] = '1048305';
//$array[] = '1048306';
//$array[] = '1048307';
//$array[] = '1048308';
//$array[] = '1048309';
//$array[] = '1048310';
//$array[] = '1048311';
//$array[] = '1048312';
//$array[] = '1048313';
//$array[] = '1048314';
//$array[] = '1048315';
//$array[] = '1048316';
//$array[] = '1048317';
//$array[] = '1048318';
//$array[] = '1048319';
//$array[] = '1048320';
//$array[] = '1048321';
//$array[] = '1048322';
//$array[] = '1048323';
//$array[] = '1048324';
//$array[] = '1048325';
//$array[] = '1048326';
//$array[] = '1048327';
//$array[] = '1048328';
//$array[] = '1048329';
//$array[] = '1048330';
//$array[] = '1048331';
//$array[] = '1048332';
//$array[] = '1048333';
//$array[] = '1048334';
//$array[] = '1048335';
//$array[] = '1048336';
//$array[] = '1048337';
//$array[] = '1048338';
//$array[] = '1048339';
//$array[] = '1048340';
//$array[] = '1048341';
//$array[] = '1048342';
//$array[] = '1048343';
//$array[] = '1048344';
//$array[] = '1048345';
//$array[] = '1048346';
//$array[] = '1048347';
//$array[] = '1048348';
//$array[] = '1048349';
//$array[] = '1048350';
//$array[] = '1048351';
//$array[] = '1048352';
//$array[] = '1048353';
//$array[] = '1048354';
//$array[] = '1048355';
//$array[] = '1048356';
//$array[] = '1048357';
//$array[] = '1048358';
//$array[] = '1048359';
//$array[] = '1048360';
//$array[] = '1048361';
//$array[] = '1048362';
//$array[] = '1048363';
//$array[] = '1048364';
//$array[] = '1048365';
//$array[] = '1048366';
//$array[] = '1048367';
//$array[] = '1048368';
//$array[] = '1048369';
//$array[] = '1048370';
//$array[] = '1048371';
//$array[] = '1048372';
//$array[] = '1048373';
//$array[] = '1048374';
//$array[] = '1048375';
//$array[] = '1048376';
//$array[] = '1048377';
//$array[] = '1048378';
//$array[] = '1048379';
//$array[] = '1049159';
//$array[] = '1049160';
//$array[] = '1049163';
//$array[] = '1049164';
//$array[] = '1049165';
//$array[] = '1049166';
//$array[] = '1049168';
//$array[] = '1049169';
//$array[] = '1049171';
//$array[] = '1049172';
//$array[] = '1049174';
//$array[] = '1049175';
//$array[] = '1049176';
//$array[] = '1049178';
*/        

/**
 * HABITACIONAL 3001 MES 052015  
//$array[] = '1042023';
//$array[] = '1042163';
//$array[] = '1043813';
//$array[] = '1043856';
//$array[] = '1043858';
//$array[] = '1047819';
//$array[] = '1047820';
//$array[] = '1047821';
//$array[] = '1047822';
//$array[] = '1047995';
//$array[] = '1048058';
//$array[] = '1048060';
//$array[] = '1048082';
//$array[] = '1048093';
//$array[] = '1048495';
//$array[] = '1048496';
//$array[] = '1048497';
//$array[] = '1048498';
//$array[] = '1048499';
//$array[] = '1048500';
//$array[] = '1048501';
//$array[] = '1048502';
//$array[] = '1048503';
//$array[] = '1048504';
//$array[] = '1048505';
//$array[] = '1048506';
//$array[] = '1048507';
//$array[] = '1048508';
//$array[] = '1048509';
//$array[] = '1048510';
//$array[] = '1048511';
//$array[] = '1048512';
//$array[] = '1048513';
//$array[] = '1048514';
//$array[] = '1048515';
//$array[] = '1048516';
//$array[] = '1048517';
//$array[] = '1048518';
//$array[] = '1048519';
//$array[] = '1048521';
//$array[] = '1048522';
//$array[] = '1048523';
//$array[] = '1048524';
//$array[] = '1048525';
//$array[] = '1048526';
//$array[] = '1048527';
//$array[] = '1048528';
//$array[] = '1048529';
//$array[] = '1048530';
//$array[] = '1048531';
//$array[] = '1048532';
//$array[] = '1048533';
//$array[] = '1048534';
//$array[] = '1048535';
//$array[] = '1048536';
//$array[] = '1048537';
//$array[] = '1048538';
//$array[] = '1048539';
//$array[] = '1048540';
//$array[] = '1048541';
//$array[] = '1048542';
//$array[] = '1048543';
//$array[] = '1048544';
//$array[] = '1048545';
//$array[] = '1048546';
//$array[] = '1048547';
//$array[] = '1048548';
//$array[] = '1048549';
//$array[] = '1048550';
//$array[] = '1048551';
//$array[] = '1048552';
//$array[] = '1048553';
//$array[] = '1048554';
//$array[] = '1048555';
//$array[] = '1048556';
//$array[] = '1048557';
//$array[] = '1048558';
//$array[] = '1048559';
//$array[] = '1048560';
//$array[] = '1048561';
//$array[] = '1048562';
//$array[] = '1048563';
//$array[] = '1048564';
//$array[] = '1048565';
//$array[] = '1048567';
//$array[] = '1048568';
//$array[] = '1048569';
//$array[] = '1048570';
//$array[] = '1048571';
//$array[] = '1048572';
//$array[] = '1048573';
//$array[] = '1048574';
//$array[] = '1048575';
//$array[] = '1048576';
//$array[] = '1048577';
//$array[] = '1048578';
//$array[] = '1048579';
//$array[] = '1048580';
//$array[] = '1048581';
//$array[] = '1048582';
//$array[] = '1048583';
//$array[] = '1048584';
//$array[] = '1048585';
//$array[] = '1048586';
//$array[] = '1048587';
//$array[] = '1048588';
//$array[] = '1048589';
//$array[] = '1048590';
//$array[] = '1048591';
//$array[] = '1048593';
//$array[] = '1048594';
//$array[] = '1048595';
//$array[] = '1048596';
//$array[] = '1048597';
//$array[] = '1048598';
//$array[] = '1048599';
//$array[] = '1048600';
//$array[] = '1048601';
//$array[] = '1048602';
//$array[] = '1048603';
//$array[] = '1048604';
//$array[] = '1048605';
//$array[] = '1048606';
//$array[] = '1048607';
//$array[] = '1048608';
//$array[] = '1048609';
//$array[] = '1048610';
//$array[] = '1048611';
//$array[] = '1048612';
//$array[] = '1048613';
//$array[] = '1048614';
//$array[] = '1048615';
//$array[] = '1048617';
//$array[] = '1048618';
//$array[] = '1048620';
//$array[] = '1048621';
//$array[] = '1048622';
//$array[] = '1048623';
//$array[] = '1048624';
//$array[] = '1048625';
//$array[] = '1048626';
//$array[] = '1048629';
//$array[] = '1048630';
//$array[] = '1048631';
//$array[] = '1048632';
//$array[] = '1048633';
//$array[] = '1048634';
//$array[] = '1048635';
//$array[] = '1048636';
//$array[] = '1048637';
//$array[] = '1048638';
//$array[] = '1048639';
//$array[] = '1048640';
//$array[] = '1048641';
//$array[] = '1048642';
//$array[] = '1048644';
//$array[] = '1048645';
//$array[] = '1048646';
//$array[] = '1048647';
//$array[] = '1048648';
//$array[] = '1048650';
//$array[] = '1048651';
//$array[] = '1048652';
//$array[] = '1048653';
//$array[] = '1048654';
//$array[] = '1048655';
//$array[] = '1048656';
//$array[] = '1048657';
//$array[] = '1048658';
//$array[] = '1048659';
//$array[] = '1048660';
//$array[] = '1048661';
//$array[] = '1048662';
//$array[] = '1048663';
//$array[] = '1048664';
//$array[] = '1048665';
//$array[] = '1048666';
//$array[] = '1048667';
//$array[] = '1048668';
//$array[] = '1048669';
//$array[] = '1048670';
//$array[] = '1048671';
//$array[] = '1048672';
//$array[] = '1048673';
//$array[] = '1048674';
//$array[] = '1048675';
//$array[] = '1048676';
//$array[] = '1048677';
//$array[] = '1048678';
//$array[] = '1048679';
//$array[] = '1048680';
//$array[] = '1048681';
//$array[] = '1048682';
//$array[] = '1048683';
//$array[] = '1048684';
//$array[] = '1048685';
//$array[] = '1048687';
//$array[] = '1048688';
//$array[] = '1048690';
//$array[] = '1048691';
//$array[] = '1048692';
//$array[] = '1048693';
//$array[] = '1048694';
//$array[] = '1048695';
//$array[] = '1048696';
//$array[] = '1048697';
//$array[] = '1048698';
//$array[] = '1048699';
//$array[] = '1048701';
//$array[] = '1048702';
//$array[] = '1048703';
//$array[] = '1048704';
//$array[] = '1048705';
//$array[] = '1048706';
//$array[] = '1048707';
//$array[] = '1048708';
//$array[] = '1048709';
//$array[] = '1048710';
//$array[] = '1048712';
//$array[] = '1048715';
//$array[] = '1048716';
//$array[] = '1048717';
//$array[] = '1048718';
//$array[] = '1048719';
//$array[] = '1048720';
//$array[] = '1048721';
//$array[] = '1048724';
//$array[] = '1048725';
//$array[] = '1048726';
//$array[] = '1048727';
//$array[] = '1048728';
//$array[] = '1048729';
//$array[] = '1048730';
//$array[] = '1048731';
//$array[] = '1048732';
//$array[] = '1048733';
//$array[] = '1048734';
//$array[] = '1048735';
//$array[] = '1048737';
//$array[] = '1048738';
//$array[] = '1048739';
//$array[] = '1048740';
//$array[] = '1048741';
//$array[] = '1048742';
//$array[] = '1048743';
//$array[] = '1048744';
//$array[] = '1048745';
//$array[] = '1048746';
//$array[] = '1048747';
//$array[] = '1048748';
//$array[] = '1048749';
//$array[] = '1048750';
//$array[] = '1048752';
//$array[] = '1048753';
//$array[] = '1048754';
//$array[] = '1048755';
//$array[] = '1048756';
//$array[] = '1048757';
//$array[] = '1048758';
//$array[] = '1048759';
//$array[] = '1048760';
//$array[] = '1048761';
//$array[] = '1048762';
//$array[] = '1048763';
//$array[] = '1048764';
//$array[] = '1048765';
//$array[] = '1048766';
//$array[] = '1048767';
//$array[] = '1048768';
//$array[] = '1048769';
//$array[] = '1048770';
//$array[] = '1048771';
//$array[] = '1048772';
//$array[] = '1048773';
//$array[] = '1048774';
//$array[] = '1048775';
//$array[] = '1048776';
//$array[] = '1048777';
//$array[] = '1048778';
//$array[] = '1048779';
//$array[] = '1048781';
//$array[] = '1048782';
//$array[] = '1048784';
//$array[] = '1048785';
//$array[] = '1048786';
//$array[] = '1048787';
//$array[] = '1048788';
//$array[] = '1048789';
//$array[] = '1048790';
//$array[] = '1048791';
//$array[] = '1048792';
//$array[] = '1048793';
//$array[] = '1048794';
//$array[] = '1048795';
//$array[] = '1048797';
//$array[] = '1048798';
//$array[] = '1048799';
//$array[] = '1048800';
//$array[] = '1048801';
//$array[] = '1048802';
//$array[] = '1048804';
//$array[] = '1048805';
//$array[] = '1048806';
//$array[] = '1048807';
//$array[] = '1048808';
//$array[] = '1048810';
//$array[] = '1048811';
//$array[] = '1048813';
//$array[] = '1048814';
//$array[] = '1048815';
//$array[] = '1048816';
//$array[] = '1048817';
//$array[] = '1048819';
//$array[] = '1048820';
//$array[] = '1048821';
//$array[] = '1048822';
//$array[] = '1048823';
//$array[] = '1048824';
//$array[] = '1048825';
//$array[] = '1048826';
//$array[] = '1048827';
//$array[] = '1048828';
//$array[] = '1048830';
//$array[] = '1048831';
//$array[] = '1048833';
//$array[] = '1048834';
//$array[] = '1048835';
//$array[] = '1048836';
//$array[] = '1048837';
//$array[] = '1048838';
//$array[] = '1048839';
//$array[] = '1048840';
//$array[] = '1048841';
//$array[] = '1048842';
//$array[] = '1048843';
//$array[] = '1048844';
//$array[] = '1048846';
//$array[] = '1048847';
//$array[] = '1048848';
//$array[] = '1048849';
//$array[] = '1048850';
//$array[] = '1048851';
//$array[] = '1048853';
//$array[] = '1048854';
//$array[] = '1048855';
//$array[] = '1048856';
//$array[] = '1048857';
//$array[] = '1048858';
//$array[] = '1048859';
//$array[] = '1048860';
//$array[] = '1048861';
//$array[] = '1048863';
//$array[] = '1048864';
//$array[] = '1048865';
//$array[] = '1048866';
//$array[] = '1048867';
//$array[] = '1048868';
//$array[] = '1048869';
//$array[] = '1048870';
//$array[] = '1048871';
//$array[] = '1048872';
//$array[] = '1048873';
//$array[] = '1048874';
//$array[] = '1048875';
//$array[] = '1048876';
//$array[] = '1048877';
//$array[] = '1048878';
//$array[] = '1048879';
//$array[] = '1048880';
//$array[] = '1048882';
//$array[] = '1048883';
//$array[] = '1048884';
//$array[] = '1048885';
//$array[] = '1048886';
//$array[] = '1048887';
//$array[] = '1048888';
//$array[] = '1048889';
//$array[] = '1048890';
//$array[] = '1048891';
//$array[] = '1048893';
//$array[] = '1048894';
//$array[] = '1048895';
//$array[] = '1048896';
//$array[] = '1048897';
//$array[] = '1048899';
//$array[] = '1048900';
//$array[] = '1048901';
//$array[] = '1048904';
//$array[] = '1048905';
//$array[] = '1048906';
//$array[] = '1048907';
//$array[] = '1048908';
//$array[] = '1048909';
//$array[] = '1048910';
//$array[] = '1048911';
//$array[] = '1048912';
//$array[] = '1048913';
//$array[] = '1048914';
//$array[] = '1048915';
//$array[] = '1048916';
//$array[] = '1048917';
//$array[] = '1048918';
//$array[] = '1048919';
//$array[] = '1048920';
//$array[] = '1048921';
//$array[] = '1048924';
//$array[] = '1048926';
//$array[] = '1048930';
//$array[] = '1048931';
//$array[] = '1048933';
//$array[] = '1048935';
//$array[] = '1048936';
//$array[] = '1048937';
//$array[] = '1048938';
//$array[] = '1048939';
//$array[] = '1048940';
//$array[] = '1048941';
//$array[] = '1048943';
//$array[] = '1048944';
//$array[] = '1048945';
//$array[] = '1048946';
//$array[] = '1048947';
//$array[] = '1048948';
//$array[] = '1048950';
//$array[] = '1048951';
//$array[] = '1048952';
//$array[] = '1048954';
//$array[] = '1048955';
//$array[] = '1048956';
//$array[] = '1048957';
//$array[] = '1048958';
//$array[] = '1048959';
//$array[] = '1048960';
//$array[] = '1048961';
//$array[] = '1048963';
//$array[] = '1048964';
//$array[] = '1048965';
//$array[] = '1048967';
//$array[] = '1048968';
//$array[] = '1048969';
//$array[] = '1048970';
//$array[] = '1048971';
//$array[] = '1048972';
//$array[] = '1048973';
//$array[] = '1048974';
//$array[] = '1048975';
//$array[] = '1048976';
//$array[] = '1048977';
//$array[] = '1048978';
//$array[] = '1048979';
//$array[] = '1048980';
//$array[] = '1048981';
//$array[] = '1048982';
//$array[] = '1048983';
//$array[] = '1048984';
//$array[] = '1048985';
//$array[] = '1048986';
//$array[] = '1048987';
//$array[] = '1048988';
//$array[] = '1048989';
//$array[] = '1048992';
//$array[] = '1048993';
//$array[] = '1048994';
//$array[] = '1048995';
//$array[] = '1048996';
//$array[] = '1048997';
//$array[] = '1048998';
//$array[] = '1048999';
//$array[] = '1049000';
//$array[] = '1049001';
//$array[] = '1049003';
//$array[] = '1049004';
//$array[] = '1049006';
//$array[] = '1049007';
//$array[] = '1049010';
//$array[] = '1049012';
//$array[] = '1049013';
//$array[] = '1049014';
//$array[] = '1049015';
//$array[] = '1049016';
//$array[] = '1049017';
//$array[] = '1049018';
//$array[] = '1049019';
//$array[] = '1049020';
//$array[] = '1049021';
//$array[] = '1049022';
//$array[] = '1049024';
//$array[] = '1049026';
//$array[] = '1049027';
//$array[] = '1049028';
//$array[] = '1049029';
//$array[] = '1049030';
//$array[] = '1049034';
//$array[] = '1049035';
//$array[] = '1049036';
//$array[] = '1049037';
//$array[] = '1049038';
//$array[] = '1049039';
//$array[] = '1049041';
//$array[] = '1049042';
//$array[] = '1049043';
//$array[] = '1049044';
//$array[] = '1049045';
//$array[] = '1049046';
//$array[] = '1049047';
//$array[] = '1049048';
//$array[] = '1049049';
//$array[] = '1049050';
//$array[] = '1049051';
//$array[] = '1049052';
//$array[] = '1049053';
//$array[] = '1049054';
//$array[] = '1049055';
//$array[] = '1049056';
//$array[] = '1049057';
//$array[] = '1049058';
//$array[] = '1049062';
//$array[] = '1049063';
//$array[] = '1049064';
//$array[] = '1049065';
//$array[] = '1049066';
//$array[] = '1049067';
//$array[] = '1049068';
//$array[] = '1049069';
//$array[] = '1049070';
//$array[] = '1049071';
//$array[] = '1049072';
//$array[] = '1049073';
//$array[] = '1049074';
//$array[] = '1049075';
//$array[] = '1049076';
//$array[] = '1049077';
//$array[] = '1049078';
//$array[] = '1049083';
//$array[] = '1049084';
//$array[] = '1049085';
//$array[] = '1049086';
//$array[] = '1049087';
//$array[] = '1049089';
//$array[] = '1049090';
//$array[] = '1049091';
//$array[] = '1049093';
//$array[] = '1049094';
//$array[] = '1049095';
//$array[] = '1049096';
//$array[] = '1049097';
//$array[] = '1049098';
//$array[] = '1049099';
//$array[] = '1049100';
//$array[] = '1049101';
//$array[] = '1049102';
//$array[] = '1049104';
//$array[] = '1049105';
//$array[] = '1049106';
//$array[] = '1049108';
//$array[] = '1049109';
//$array[] = '1049110';
//$array[] = '1049112';
//$array[] = '1049113';
//$array[] = '1049114';
//$array[] = '1049115';
//$array[] = '1049116';
//$array[] = '1049117';
//$array[] = '1049118';
//$array[] = '1049119';
//$array[] = '1049120';
//$array[] = '1049121';
//$array[] = '1049122';
//$array[] = '1049123';
//$array[] = '1049124';
//$array[] = '1049125';
//$array[] = '1049126';
//$array[] = '1049127';
//$array[] = '1049128';
//$array[] = '1049129';
//$array[] = '1049130';
//$array[] = '1049131';
//$array[] = '1049132';
//$array[] = '1049136';
//$array[] = '1049137';
//$array[] = '1049139';
//$array[] = '1049142';
//$array[] = '1049143';
//$array[] = '1049144';
//$array[] = '1049146';
//$array[] = '1049147';
//$array[] = '1049148';
//$array[] = '1049149';
//$array[] = '1049150';
//$array[] = '1049151';
//$array[] = '1049152';
//$array[] = '1049153';
//$array[] = '1049154';
//$array[] = '1049157';
//$array[] = '1049158';        
//// FIM HABITACIONAL 3001 MES 052015     
 */

/**
 * 
// LC 22670 MES 072015                
//$array[] = '1051442';
//$array[] = '1051444';
//$array[] = '1053745';
//$array[] = '1053761';
//$array[] = '1053772';
//$array[] = '1053774';
//$array[] = '1054435';
//$array[] = '1055091';
//$array[] = '1055092';
//$array[] = '1055093';
//$array[] = '1055094';
//$array[] = '1055095';
//$array[] = '1055096';
//$array[] = '1055097';
//$array[] = '1055098';
//$array[] = '1055099';
//$array[] = '1055100';
//$array[] = '1055101';
//$array[] = '1055102';
//$array[] = '1055103';
//$array[] = '1055104';
//$array[] = '1055105';
//$array[] = '1055106';
//$array[] = '1055107';
//$array[] = '1055140';
//$array[] = '1055771';
//$array[] = '1058188';
//$array[] = '1058189';
//$array[] = '1058190';
//$array[] = '1058191';
//$array[] = '1058192';
//$array[] = '1058194';
//$array[] = '1058195';
//$array[] = '1058196';
//$array[] = '1058197';
//$array[] = '1058198';
//$array[] = '1058199';
//$array[] = '1058202';
//$array[] = '1058204';
//$array[] = '1058884';
// FIM HABITACIONAL 3001 MES 052015         
*/

/**
 * TODOS QUE ESTAO COM AS TAXAS ERRADAS DO MES 7
$array[] = '1049283'; //	30/07/2015 00:00	C	196
$array[] = '1049300'; //	16/07/2015 00:00	C	22670
$array[] = '1049269'; //	31/07/2015 00:00	A	1228
$array[] = '1049265'; //	07/07/2015 00:00	A	4566
$array[] = '1048024'; //	17/12/2015 00:00	A	3344
$array[] = '1049284'; //	30/07/2015 00:00	C	196
$array[] = '1047839'; //	01/07/2015 00:00	R	196
$array[] = '1049189'; //	02/07/2015 00:00	A	4566
$array[] = '1049238'; //	06/07/2015 00:00	A	4566
$array[] = '1049306'; //	10/07/2015 00:00	C	22670
$array[] = '1049245'; //	03/07/2015 00:00	F	4208
$array[] = '1049248'; //	31/07/2015 00:00	C	1228
$array[] = '1049251'; //	31/07/2015 00:00	F	1228
$array[] = '1049292'; //	31/07/2015 00:00	F	1228
 */

/**
 * HABITACIONAL MES 07-2015

$array[] = '1048495';
$array[] = '1048627';
$array[] = '1053958';
$array[] = '1054016';
$array[] = '1054966';
$array[] = '1058049';
$array[] = '1058072';
$array[] = '1058073';
$array[] = '1058176';
$array[] = '1058201';
$array[] = '1058209';
$array[] = '1058215';
$array[] = '1058225';
$array[] = '1058243';
$array[] = '1058244';
$array[] = '1058245';
$array[] = '1058246';
$array[] = '1058247';
$array[] = '1058248';
$array[] = '1058249';
$array[] = '1058250';
$array[] = '1058251';
$array[] = '1058252';
$array[] = '1058253';
$array[] = '1058254';
$array[] = '1058255';
$array[] = '1058256';
$array[] = '1058257';
$array[] = '1058258';
$array[] = '1058259';
$array[] = '1058260';
$array[] = '1058261';
$array[] = '1058262';
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
$array[] = '1058354';
$array[] = '1058355';
$array[] = '1058356';
$array[] = '1058357';
$array[] = '1058358';
$array[] = '1058359';
$array[] = '1058360';
$array[] = '1058361';
$array[] = '1058362';
$array[] = '1058363';
$array[] = '1058364';
$array[] = '1058365';
$array[] = '1058366';
$array[] = '1058367';
$array[] = '1058368';
$array[] = '1058369';
$array[] = '1058370';
$array[] = '1058371';
$array[] = '1058372';
$array[] = '1058373';
$array[] = '1058374';
$array[] = '1058375';
$array[] = '1058376';
$array[] = '1058377';
$array[] = '1058378';
$array[] = '1058379';
$array[] = '1058380';
$array[] = '1058381';
$array[] = '1058382';
$array[] = '1058383';
$array[] = '1058384';
$array[] = '1058385';
$array[] = '1058386';
$array[] = '1058387';
$array[] = '1058388';
$array[] = '1058389';
$array[] = '1058390';
$array[] = '1058391';
$array[] = '1058392';
$array[] = '1058393';
$array[] = '1058394';
$array[] = '1058395';
$array[] = '1058396';
$array[] = '1058397';
$array[] = '1058398';
$array[] = '1058399';
$array[] = '1058400';
$array[] = '1058401';
$array[] = '1058402';
$array[] = '1058404';
$array[] = '1058405';
$array[] = '1058406';
$array[] = '1058407';
$array[] = '1058408';
$array[] = '1058409';
$array[] = '1058410';
$array[] = '1058411';
$array[] = '1058412';
$array[] = '1058413';
$array[] = '1058414';
$array[] = '1058893';
$array[] = '1058898';
$array[] = '1058916';
$array[] = '1058945';
$array[] = '1059556';
$array[] = '1059557';
$array[] = '1059558';
$array[] = '1059559';
$array[] = '1059561';
$array[] = '1059562';
$array[] = '1059563';
$array[] = '1059564';
$array[] = '1059565';
$array[] = '1059566';
$array[] = '1059567';
$array[] = '1059568';
$array[] = '1059570';
$array[] = '1059571';
$array[] = '1059572';
$array[] = '1059573';
$array[] = '1059574';
$array[] = '1059575';
$array[] = '1059576';
$array[] = '1059577';
$array[] = '1059578';
$array[] = '1059579';
$array[] = '1059581';
$array[] = '1059582';
$array[] = '1059584';
$array[] = '1059585';
$array[] = '1059586';
$array[] = '1059587';
$array[] = '1059588';
$array[] = '1059589';
$array[] = '1059590';
$array[] = '1059591';
$array[] = '1059592';
$array[] = '1059593';
$array[] = '1059594';
$array[] = '1059595';
$array[] = '1059597';
$array[] = '1059598';
$array[] = '1059599';
$array[] = '1059600';
$array[] = '1059601';
$array[] = '1059602';
$array[] = '1059603';
$array[] = '1059604';
$array[] = '1059605';
$array[] = '1059606';
$array[] = '1059607';
$array[] = '1059609';
$array[] = '1059610';
$array[] = '1059611';
$array[] = '1059612';
$array[] = '1059613';
$array[] = '1059614';
$array[] = '1059616';
$array[] = '1059617';
$array[] = '1059618';
$array[] = '1059620';
$array[] = '1059621';
$array[] = '1059623';
$array[] = '1059624';
$array[] = '1059625';
$array[] = '1059626';
$array[] = '1059627';
$array[] = '1059628';
$array[] = '1059630';
$array[] = '1059631';
$array[] = '1059632';
$array[] = '1059633';
$array[] = '1059634';
$array[] = '1059635';
$array[] = '1059636';
$array[] = '1059637';
$array[] = '1059638';
$array[] = '1059639';
$array[] = '1059640';
$array[] = '1059641';
$array[] = '1059642';
$array[] = '1059643';
$array[] = '1059644';
$array[] = '1059645';
$array[] = '1059646';
$array[] = '1059647';
$array[] = '1059648';
$array[] = '1059651';
$array[] = '1059652';
$array[] = '1059653';
$array[] = '1059654';
$array[] = '1059656';
$array[] = '1059657';
$array[] = '1059658';
$array[] = '1059659';
$array[] = '1059660';
$array[] = '1059661';
$array[] = '1059662';
$array[] = '1059663';
$array[] = '1059664';
$array[] = '1059665';
$array[] = '1059666';
$array[] = '1059667';
$array[] = '1059668';
$array[] = '1059669';
$array[] = '1059670';
$array[] = '1059671';
$array[] = '1059672';
$array[] = '1059673';
$array[] = '1059676';
$array[] = '1059677';
$array[] = '1059678';
$array[] = '1059679';
$array[] = '1059682';
$array[] = '1059683';
$array[] = '1059684';
$array[] = '1059685';
$array[] = '1059686';
$array[] = '1059687';
$array[] = '1059688';
$array[] = '1059690';
$array[] = '1059691';
$array[] = '1059692';
$array[] = '1059693';
$array[] = '1059694';
$array[] = '1059695';
$array[] = '1059696';
$array[] = '1059698';
$array[] = '1059699';
$array[] = '1059700';
$array[] = '1059716';
$array[] = '1059717';
$array[] = '1059718';
$array[] = '1059719';
$array[] = '1059720';
$array[] = '1059721';
$array[] = '1059722';
$array[] = '1059723';
$array[] = '1059724';
$array[] = '1059725';
$array[] = '1059726';
$array[] = '1059727';
$array[] = '1059728';
$array[] = '1059729';
$array[] = '1059730';
$array[] = '1059731';
$array[] = '1059732';
$array[] = '1059733';
$array[] = '1059734';
$array[] = '1059735';
$array[] = '1059736';
$array[] = '1059737';
$array[] = '1059740';
$array[] = '1059741';
$array[] = '1059742';
$array[] = '1059745';
$array[] = '1059747';
$array[] = '1059748';
$array[] = '1059749';
$array[] = '1059750';
$array[] = '1059751';
$array[] = '1059752';
$array[] = '1059753';
$array[] = '1059754';
$array[] = '1059755';
$array[] = '1059756';
$array[] = '1059757';
$array[] = '1059758';
$array[] = '1059759';
$array[] = '1059760';
$array[] = '1059761';
$array[] = '1059762';
$array[] = '1059763';
$array[] = '1059764';
$array[] = '1059765';
$array[] = '1059766';
$array[] = '1059767';
$array[] = '1059768';
$array[] = '1059769';
$array[] = '1059770';
$array[] = '1059772';
$array[] = '1059773';
$array[] = '1059774';
$array[] = '1059775';
$array[] = '1059776';
$array[] = '1059777';
$array[] = '1059778';
$array[] = '1059779';
$array[] = '1059780';
$array[] = '1059781';
$array[] = '1059782';
$array[] = '1059783';
$array[] = '1059784';
$array[] = '1059785';
$array[] = '1059787';
$array[] = '1059788';
$array[] = '1059789';
$array[] = '1059790';
$array[] = '1059791';
$array[] = '1059792';
$array[] = '1059793';
$array[] = '1059794';
$array[] = '1059795';
$array[] = '1059796';
$array[] = '1059797';
$array[] = '1059798';
$array[] = '1059799';
$array[] = '1059800';
$array[] = '1059801';
$array[] = '1059802';
$array[] = '1059803';
$array[] = '1059804';
$array[] = '1059805';
$array[] = '1059806';
$array[] = '1059808';
$array[] = '1059809';
$array[] = '1059810';
$array[] = '1059811';
$array[] = '1059812';
$array[] = '1059813';
$array[] = '1059814';
$array[] = '1059815';
$array[] = '1059816';
$array[] = '1059817';
$array[] = '1059818';
$array[] = '1059819';
$array[] = '1059821';
$array[] = '1059822';
$array[] = '1059823';
$array[] = '1059824';
$array[] = '1059825';
$array[] = '1059828';
$array[] = '1059829';
$array[] = '1059830';
$array[] = '1059831';
$array[] = '1059832';
$array[] = '1059833';
$array[] = '1059834';
$array[] = '1059835';
$array[] = '1059836';
$array[] = '1059837';
$array[] = '1059838';
$array[] = '1059840';
$array[] = '1059841';
$array[] = '1059842';
$array[] = '1059843';
$array[] = '1059844';
$array[] = '1059846';
$array[] = '1059847';
$array[] = '1059849';
$array[] = '1059850';
$array[] = '1059851';
$array[] = '1059852';
$array[] = '1059853';
$array[] = '1059854';
$array[] = '1059856';
$array[] = '1059857';
$array[] = '1059858';
$array[] = '1059859';
$array[] = '1059860';
$array[] = '1059861';
$array[] = '1059862';
$array[] = '1059863';
$array[] = '1059864';
$array[] = '1059865';
$array[] = '1059866';
$array[] = '1059867';
$array[] = '1059868';
$array[] = '1059869';
$array[] = '1059870';
$array[] = '1059871';
$array[] = '1059872';
$array[] = '1059873';
$array[] = '1059874';
$array[] = '1059875';
$array[] = '1059879';
$array[] = '1059880';
$array[] = '1059881';
$array[] = '1059882';
$array[] = '1059884';
$array[] = '1059888';
$array[] = '1059893';
$array[] = '1059897';
$array[] = '1059898';
$array[] = '1059899';
$array[] = '1059907';
$array[] = '1059908';
$array[] = '1059909';
$array[] = '1059910';
$array[] = '1059911';
$array[] = '1059912';
$array[] = '1059913';
$array[] = '1059915';
$array[] = '1059916';
$array[] = '1059917';
$array[] = '1059918';
$array[] = '1059919';
$array[] = '1059920';
$array[] = '1059921';
$array[] = '1059922';
$array[] = '1059923';
$array[] = '1059924';
$array[] = '1059925';
$array[] = '1059926';
$array[] = '1059927';
$array[] = '1059928';
$array[] = '1059929';
$array[] = '1059930';
$array[] = '1059932';
$array[] = '1059933';
$array[] = '1059934';
$array[] = '1059935';
$array[] = '1059936';
$array[] = '1059937';
$array[] = '1059938';
$array[] = '1059939';
$array[] = '1059940';
$array[] = '1059941';
$array[] = '1059942';
$array[] = '1059943';
$array[] = '1059944';
$array[] = '1059945';
$array[] = '1059946';
$array[] = '1059947';
$array[] = '1059948';
$array[] = '1059949';
$array[] = '1059950';
$array[] = '1059951';
$array[] = '1059952';
$array[] = '1059953';
$array[] = '1059954';
$array[] = '1059955';
$array[] = '1059956';
$array[] = '1059957';
$array[] = '1059958';
$array[] = '1059959';
$array[] = '1059960';
$array[] = '1059961';
$array[] = '1059962';
$array[] = '1059963';
$array[] = '1059964';
$array[] = '1059965';
$array[] = '1059966';
$array[] = '1059967';
$array[] = '1059968';
$array[] = '1059969';
$array[] = '1059970';
$array[] = '1059971';
$array[] = '1059972';
$array[] = '1059973';
$array[] = '1059974';
$array[] = '1059976';
$array[] = '1059977';
$array[] = '1059978';
$array[] = '1059979';
$array[] = '1059980';
$array[] = '1059981';
$array[] = '1059982';
$array[] = '1059983';
$array[] = '1059984';
$array[] = '1059985';
$array[] = '1059987';
$array[] = '1059988';
$array[] = '1059989';
$array[] = '1059990';
$array[] = '1059991';
$array[] = '1059992';
$array[] = '1059993';
$array[] = '1059994';
$array[] = '1059997';
$array[] = '1059998';
$array[] = '1059999';
$array[] = '1060000';
$array[] = '1060001';
$array[] = '1060002';
$array[] = '1060003';
$array[] = '1060004';
$array[] = '1060005';
$array[] = '1060006';
$array[] = '1060007';
$array[] = '1060008';
$array[] = '1060009';
$array[] = '1060010';
$array[] = '1060011';
$array[] = '1060012';
$array[] = '1060013';
$array[] = '1060014';
$array[] = '1060016';
$array[] = '1060017';
$array[] = '1060019';
$array[] = '1060020';
$array[] = '1060023';
$array[] = '1060024';
$array[] = '1060025';
$array[] = '1060026';
$array[] = '1060027';
$array[] = '1060028';
$array[] = '1060029';
$array[] = '1060030';
$array[] = '1060031';
$array[] = '1060032';
$array[] = '1060033';
$array[] = '1060034';
$array[] = '1060035';
$array[] = '1060037';
$array[] = '1060038';
$array[] = '1060039';
$array[] = '1060040';
$array[] = '1060041';
$array[] = '1060042';
$array[] = '1060044';
$array[] = '1060045';
$array[] = '1060046';
$array[] = '1060047';
$array[] = '1060048';
$array[] = '1060049';
$array[] = '1060050';
$array[] = '1060051';
$array[] = '1060052';
$array[] = '1060053';
$array[] = '1060054';
$array[] = '1060055';
$array[] = '1060056';
$array[] = '1060058';
$array[] = '1060059';
$array[] = '1060061';
$array[] = '1060062';
$array[] = '1060063';
$array[] = '1060064';
$array[] = '1060065';
$array[] = '1060067';
$array[] = '1060068';
$array[] = '1060069';
$array[] = '1060070';
$array[] = '1060071';
$array[] = '1060072';
$array[] = '1060073';
$array[] = '1060074';
$array[] = '1060075';
$array[] = '1060076';
$array[] = '1060077';
$array[] = '1060078';
$array[] = '1060079';
$array[] = '1060080';
$array[] = '1060081';
$array[] = '1060082';
$array[] = '1060083';
$array[] = '1060084';
$array[] = '1060085';
$array[] = '1060086';
$array[] = '1060087';
$array[] = '1060088';
$array[] = '1060089';
$array[] = '1060090';
$array[] = '1060092';
$array[] = '1060093';
$array[] = '1060094';
$array[] = '1060095';
$array[] = '1060096';
$array[] = '1060098';
$array[] = '1060099';
$array[] = '1060100';
$array[] = '1060102';
$array[] = '1060103';
$array[] = '1060104';
$array[] = '1060106';
$array[] = '1060155';
        
 */
        
/** CONDOVEL mes 09 
 * 
$array[] = '1059304';
$array[] = '1059305';
$array[] = '1059306';
$array[] = '1059307';
$array[] = '1059308';
$array[] = '1059309';
$array[] = '1059310';
$array[] = '1059311';
$array[] = '1060642';
$array[] = '1065070';
$array[] = '1067286';
$array[] = '1067287';
$array[] = '1067289';
$array[] = '1067291';
$array[] = '1067292';
$array[] = '1067293';
$array[] = '1067294';
$array[] = '1067295';
$array[] = '1067297';
$array[] = '1067298';
$array[] = '1067299';
$array[] = '1068013';
$array[] = '1068091';
$array[] = '1068092';
$array[] = '1068097';
$array[] = '1070129';
$array[] = '1070774';
$array[] = '1070775';
$array[] = '1070776';
$array[] = '1070777';
$array[] = '1070778';
$array[] = '1070779';
$array[] = '1070780';
$array[] = '1070781';
$array[] = '1070782';
$array[] = '1070783';
$array[] = '1070784';
$array[] = '1070785';
$array[] = '1070786';
$array[] = '1070787';
$array[] = '1070788';
$array[] = '1070789';
$array[] = '1070790';
$array[] = '1070791';
$array[] = '1070793';
$array[] = '1070794';
$array[] = '1070795';
$array[] = '1070796';
$array[] = '1070798';
$array[] = '1070799';
$array[] = '1070800';
$array[] = '1070801';
$array[] = '1070802';
$array[] = '1070803';
$array[] = '1070804';
$array[] = '1070805';
$array[] = '1070806';
$array[] = '1070808';
$array[] = '1070809';
$array[] = '1070810';
$array[] = '1070811';
$array[] = '1070812';
$array[] = '1070813';
$array[] = '1070814';
$array[] = '1070815';
$array[] = '1070816';
$array[] = '1070817';
$array[] = '1070818';
$array[] = '1070819';
$array[] = '1070820';
$array[] = '1070821';
$array[] = '1070822';
$array[] = '1070823';
$array[] = '1070824';
$array[] = '1070825';
$array[] = '1070826';
$array[] = '1070827';
$array[] = '1070828';
$array[] = '1070829';
$array[] = '1070830';
$array[] = '1070831';
$array[] = '1070832';
$array[] = '1070833';
$array[] = '1070834';
$array[] = '1070835';
$array[] = '1070836';
$array[] = '1070837';
$array[] = '1070838';
$array[] = '1070839';
$array[] = '1070840';
$array[] = '1070841';
$array[] = '1070842';
$array[] = '1070843';
$array[] = '1070844';
$array[] = '1070845';
$array[] = '1070846';
$array[] = '1070847';
$array[] = '1070848';
$array[] = '1070849';
$array[] = '1070850';
$array[] = '1070851';
$array[] = '1070852';
$array[] = '1070853';
$array[] = '1070854';
$array[] = '1070855';
$array[] = '1070856';
$array[] = '1070857';
$array[] = '1070858';
$array[] = '1070859';
$array[] = '1070860';
$array[] = '1070861';
$array[] = '1070862';
$array[] = '1070863';
$array[] = '1070864';
$array[] = '1070865';
$array[] = '1070866';
$array[] = '1070867';
$array[] = '1070868';
$array[] = '1070869';
$array[] = '1070870';
$array[] = '1070871';
$array[] = '1070872';
$array[] = '1070873';
$array[] = '1070874';
$array[] = '1070875';
$array[] = '1070876';
$array[] = '1070877';
$array[] = '1070878';
$array[] = '1070879';
$array[] = '1070880';
$array[] = '1070881';
$array[] = '1070883';
$array[] = '1070884';
$array[] = '1070885';
$array[] = '1070886';
$array[] = '1070887';
$array[] = '1070888';
$array[] = '1070889';
$array[] = '1070890';
$array[] = '1070891';
$array[] = '1070892';
$array[] = '1070893';
$array[] = '1070894';
$array[] = '1070895';
$array[] = '1070897';
$array[] = '1070898';
$array[] = '1070899';
$array[] = '1070900';
$array[] = '1070901';
$array[] = '1070902';
$array[] = '1070903';
$array[] = '1070904';
$array[] = '1070905';
$array[] = '1070906';
$array[] = '1070907';
$array[] = '1070908';
$array[] = '1070909';
$array[] = '1070910';
$array[] = '1070911';
$array[] = '1070912';
$array[] = '1070913';
$array[] = '1070914';
$array[] = '1070917';
$array[] = '1070919';
$array[] = '1070920';
$array[] = '1070921';
$array[] = '1070922';
$array[] = '1070923';
$array[] = '1070924';
$array[] = '1070925';
$array[] = '1070926';
$array[] = '1070927';
$array[] = '1070928';
$array[] = '1070929';
$array[] = '1070930';
$array[] = '1070932';
$array[] = '1070933';
$array[] = '1070934';
$array[] = '1070935';
$array[] = '1070936';
$array[] = '1070937';
$array[] = '1070938';
$array[] = '1070939';
$array[] = '1070940';
$array[] = '1070941';
$array[] = '1070942';
$array[] = '1070943';
$array[] = '1070944';
$array[] = '1070946';
$array[] = '1070947';
$array[] = '1070948';
$array[] = '1070950';
$array[] = '1070951';
$array[] = '1070952';
$array[] = '1070953';
$array[] = '1070954';
$array[] = '1070955';
$array[] = '1070956';
$array[] = '1070957';
$array[] = '1070958';
$array[] = '1070959';
$array[] = '1070960';
$array[] = '1070961';
$array[] = '1070962';
$array[] = '1070963';
$array[] = '1070964';
$array[] = '1070965';
$array[] = '1070966';
$array[] = '1070967';
$array[] = '1070968';
$array[] = '1070970';
$array[] = '1070971';
$array[] = '1070972';
$array[] = '1070973';
$array[] = '1070974';
$array[] = '1070975';
$array[] = '1070976';
$array[] = '1070977';
$array[] = '1070978';
$array[] = '1070979';
$array[] = '1070980';
$array[] = '1070981';
$array[] = '1070982';
$array[] = '1070983';
$array[] = '1070984';
$array[] = '1070985';
$array[] = '1070986';
$array[] = '1070987';
$array[] = '1070988';
$array[] = '1070989';
$array[] = '1070990';
$array[] = '1070991';
$array[] = '1070992';
$array[] = '1070993';
$array[] = '1070995';
$array[] = '1070996';
$array[] = '1070997';
$array[] = '1070998';
$array[] = '1070999';
$array[] = '1071000';
$array[] = '1071001';
$array[] = '1071002';
$array[] = '1071003';
$array[] = '1071004';
$array[] = '1071005';
$array[] = '1071008';
$array[] = '1071009';
$array[] = '1071010';
$array[] = '1071011';
$array[] = '1071012';
$array[] = '1071013';
$array[] = '1071014';
$array[] = '1071015';
$array[] = '1071016';
$array[] = '1071017';
$array[] = '1071018';
$array[] = '1071019';
$array[] = '1071020';
$array[] = '1071021';
$array[] = '1071022';
$array[] = '1071023';
$array[] = '1071024';
$array[] = '1071025';
$array[] = '1071026';
$array[] = '1071027';
$array[] = '1071028';
$array[] = '1071029';
$array[] = '1071030';
$array[] = '1071031';
$array[] = '1071032';
$array[] = '1071033';
$array[] = '1071034';
$array[] = '1071035';
$array[] = '1071036';
$array[] = '1071038';
$array[] = '1071039';
$array[] = '1071040';
$array[] = '1071041';
$array[] = '1071042';
$array[] = '1071043';
$array[] = '1071044';
$array[] = '1071045';
$array[] = '1071046';
$array[] = '1071047';
$array[] = '1071048';
$array[] = '1071049';
$array[] = '1071050';
$array[] = '1071051';
$array[] = '1071052';
$array[] = '1071054';
$array[] = '1071055';
$array[] = '1071056';
$array[] = '1071057';
$array[] = '1071058';
$array[] = '1071059';
$array[] = '1071060';
$array[] = '1071061';
$array[] = '1071062';
$array[] = '1071063';
$array[] = '1071064';
$array[] = '1071065';
$array[] = '1071066';
$array[] = '1071067';
$array[] = '1071068';
$array[] = '1071069';
 */   
        $cont = 0 ;
        $force = 100;
        foreach ($array as $value) {
            $orca = $this->getEm()->find($this->entity, $value);
            if($orca){
                $data = $orca->toArray();                
                $result = $service->update($data);
                if($result === TRUE){
                    echo '<p> Recalculado ao procurar ', $value, '</p>';
                    $cont ++;
                }else{
                    foreach ($result as $vlr) {
                        echo '<h3> Erro ao recalcular ', $vlr, '</h3>';
                    }
                }
            }else{
                echo '<h1> Erro ao procurar ', $value, '</h1>';
            }  
            if($force >= $cont){
                flush();
                $force += 100;
            }
        }      
        echo '<h1> Total ', $cont, '</h1>';
    }
    
    public function acertaLocatarioAction() {
        $this->verificaSeUserAdmin();
        /* @var $service \Livraria\Service\Orcamento */
        $service = $this->getServiceLocator()->get($this->service);
      
        echo "<!DOCTYPE html>",
        "<html><head>",
        '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" >',
        '<meta http-equiv="content-language" content="pt-br" />',
        '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />',
        '<title>Vila Velha Seguros</title>',
        '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
        "</head><body>",
        '<h1> Acertar nome dos Locatarios </h1>'
        ;
        $service->verificaLocatario(['adm'=>'','ini'=>'01/04/2015','fim'=>'31/04/2015',]);
        
        echo                 
             "</body></html>"
        ; 
        
    }
}
