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
}
