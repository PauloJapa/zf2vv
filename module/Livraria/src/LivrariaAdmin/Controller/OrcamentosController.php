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
    public function atualizaSegAction() {
        /* @var $service \Livraria\Service\Orcamento */
        /* @var $orca \Livraria\Entity\Orcamento */
        $service = $this->getServiceLocator()->get($this->service);
        
        $array = [];
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
                
//        
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

        
        					$array[] = '1026812';
						$array[] = '1032238';
						$array[] = '1032239';
						$array[] = '1035194';
						$array[] = '1035742';
						$array[] = '1035746';
						$array[] = '1037294';
						$array[] = '1037295';
						$array[] = '1037296';
						$array[] = '1037297';
						$array[] = '1037298';
						$array[] = '1037299';
						$array[] = '1037301';
						$array[] = '1037302';
						$array[] = '1037303';
						$array[] = '1037304';
						$array[] = '1037305';
						$array[] = '1037306';
						$array[] = '1037307';
						$array[] = '1037308';
						$array[] = '1037309';
						$array[] = '1037310';
						$array[] = '1037311';
						$array[] = '1037312';
						$array[] = '1037313';
						$array[] = '1037314';
						$array[] = '1037315';
						$array[] = '1037316';
						$array[] = '1037317';
						$array[] = '1037318';
						$array[] = '1037319';
						$array[] = '1037320';
						$array[] = '1037321';
						$array[] = '1037322';
						$array[] = '1037323';
						$array[] = '1037324';
						$array[] = '1037325';
						$array[] = '1037326';
						$array[] = '1037327';
						$array[] = '1037328';
						$array[] = '1037329';
						$array[] = '1037330';
						$array[] = '1037331';
						$array[] = '1037332';
						$array[] = '1037333';
						$array[] = '1037334';
						$array[] = '1037335';
						$array[] = '1037336';
						$array[] = '1037337';
						$array[] = '1037338';
						$array[] = '1037339';
						$array[] = '1037340';
						$array[] = '1037341';
						$array[] = '1037342';
						$array[] = '1037343';
						$array[] = '1037344';
						$array[] = '1037345';
						$array[] = '1037346';
						$array[] = '1037347';
						$array[] = '1037348';
						$array[] = '1037349';
						$array[] = '1037350';
						$array[] = '1037351';
						$array[] = '1037352';
						$array[] = '1037353';
						$array[] = '1037354';
						$array[] = '1037355';
						$array[] = '1037356';
						$array[] = '1037357';
						$array[] = '1037358';
						$array[] = '1037359';
						$array[] = '1037360';
						$array[] = '1037361';
						$array[] = '1037362';
						$array[] = '1037363';
						$array[] = '1037364';
						$array[] = '1037365';
						$array[] = '1037366';
						$array[] = '1037367';
						$array[] = '1037368';
						$array[] = '1037369';
						$array[] = '1037370';
						$array[] = '1037371';
						$array[] = '1037372';
						$array[] = '1037373';
						$array[] = '1037374';
						$array[] = '1037375';
						$array[] = '1037376';
						$array[] = '1037377';
						$array[] = '1037378';
						$array[] = '1037379';
						$array[] = '1037380';
						$array[] = '1037381';
						$array[] = '1037382';
						$array[] = '1037383';
						$array[] = '1037384';
						$array[] = '1037385';
						$array[] = '1037386';
						$array[] = '1037387';
						$array[] = '1037388';
						$array[] = '1037389';
						$array[] = '1037390';
						$array[] = '1037391';
						$array[] = '1037392';
						$array[] = '1037393';
						$array[] = '1037394';
						$array[] = '1037395';
						$array[] = '1037396';
						$array[] = '1037397';
						$array[] = '1037398';
						$array[] = '1037399';
						$array[] = '1037400';
						$array[] = '1037401';
						$array[] = '1037402';
						$array[] = '1037403';
						$array[] = '1037404';
						$array[] = '1037405';
						$array[] = '1037406';
						$array[] = '1037407';
						$array[] = '1037408';



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
}
