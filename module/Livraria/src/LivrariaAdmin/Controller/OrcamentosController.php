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
        $data = $this->filtrosDaPaginacao();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setOrcamento();
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
        $inputs = ['id','locador','locatario','refImovel', 'administradora', 'status', 'user','dataI','dataF','validade'];
        foreach ($inputs as $input) {
            if ((isset($data[$input])) AND (!empty($data[$input]))) {
                $filtro[$input] = $data[$input];
            }
        }
        
        $list = $this->getEm()
                     ->getRepository($this->entity)
                     ->findOrcamento($filtro,$operadores);
        
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
        
        
        if($data['subOpcao'] == 'calcular'){
            if ($this->formData->isValid()){
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->insert($data, TRUE);
                if($result === TRUE){
                    $this->formData->setData($service->getNewInputs());
                    $this->flashMessenger()->clearMessages();
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
            if ($this->formData->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->insert($data);
                if($result[0] === TRUE){
                    $sessionContainer->idOrcamento = $result[1];
                    unset($sessionContainer->administradora);
                    $this->flashMessenger()->clearMessages();
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
        
        return new ViewModel(array_merge($this->getParamsForView(),['administradora'=>$sessionContainer->administradora, 'imprimeProp' => '0'])); 
    }
    
    /**
     * Fecha o orçamento e copia os dados para a tabela de fechados
     * @return View
     */
    public function fecharSegurosAction() {
        $data = $this->getRequest()->getPost()->toArray();
        //Pegar Servico de fechados $sf
        //Fechar a lista de orçamentos selecionados.
        foreach ($data['Checkeds'] as $idOrc) {
            $sf = new $this->serviceFechado($this->getEm());
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
        
        $this->formData->setEdit();
        if($data['subOpcao'] == 'calcular'){
            if ($this->formData->isValid()){
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->update($data, TRUE);
                if($result === TRUE){
                    $this->formData->setData($service->getNewInputs());
                    $this->flashMessenger()->clearMessages();
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
        
        return new ViewModel(array_merge($this->getParamsForView(),['administradora'=>$sessionContainer->administradora, 'imprimeProp'=>$imprimeProp])); 
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
    
    
    /*                          FUNÇOES DE IMPORTAÇÃO
    public function importarAction(){
        echo
        '<html><head>',
        '<meta http-equiv="content-language" content="pt-br" />',
        '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />',
        '</head><body>';
        $data = $this->getRequest()->getFiles()->toArray();
        //Verificando a existencia do arquivo
        $content  = file($data['content']['tmp_name']);
        if(!$content){
            echo 'arquivo não encontrado!!';
            return;
        }
        // montar array de comissão das administradoras
        $list = $this->getEm()->getRepository('Livraria\Entity\Comissao')->findAll();
        foreach ($list as $ent) {
            $this->comissao[$ent->getAdministradora()->getId()] = $ent->getId();
        }
        // montar array de com locadores
        $loc = $this->getEm()->getRepository('Livraria\Entity\Locador')->findAll();
        foreach ($loc as $ent) {
            $this->locador[$ent->getId()] = $ent->getNome();
        }
        // ferramentas para locatario
        $this->repLoct = $this->getEm()->getRepository('Livraria\Entity\Locatario');
        //$this->serLoct = $this->getServiceLocator()->get('Livraria\Service\Locatario');
        $this->serLoct = new \Livraria\Service\Locatario($this->getEm());
        $this->serLoct->notValidateNew();
        $this->serLoct->setFlush(FALSE);
        
        // ferramentas para imovel
        $this->repImovel = $this->getEm()->getRepository('Livraria\Entity\Imovel');
        $this->paises  = $this->getEm()->getRepository('Livraria\Entity\Pais')->fetchPairs();
        
        // ferramentas para Atividade
        $this->repAtivid = $this->getEm()->getRepository('Livraria\Entity\Atividade');
        
        // Pegando o serviço para manipular dados
        $service = new $this->service($this->getEm());
        $service->notValidateNew();
        $service->setFlush(FALSE);
        $cont = 500  ;
        echo 'inicio' , date('d/m/Y - h:i');
        foreach ($content as $key => $value) {
            if($key == 0){
                if(!$this->validaColunas($this->csvToArray($value))){
                    echo 'Erro titulos da colunas estão incorretos!!';
                    var_dump($value);
                    return;
                }
                continue;
            }
            $resul = $service->insert($this->getData($value,$key));
            if($resul[0] === TRUE){
                if($cont < $key){
                    echo '<br> fim', date('d/m/Y - h:i');
                    echo '<p>Importado + 1000; ', $cont , '</p>';
                    $this->getEm()->flush();
                    $cont += 1000;
                    unset($service);
                    // Pegando o serviço para manipular dados
                    $service = new $this->service($this->getEm());
                    $service->notValidateNew();
                    $service->setFlush(FALSE);
                }
                continue;
            }
            echo '<h2>Erro ao importar; ', $value , '</h2>';
            var_dump($resul);
        }        
        $this->getEm()->flush();
        echo '<br> fim', date('d/m/Y - h:i');
    }
    
    public function getTaxa(){
        $this->data['taxa'] = '';
    }
    
    public function getAtividade(){
        $atividade = $this->repAtivid->findByCodSeguradora($this->d[20]);
        foreach ($atividade as $entity) {
            $this->data['ocupacao']  = $entity->getOcupacao();
            $this->data['atividade'] = $entity;
            return;
        }
        echo '<h2>Erro ao procurar Atividade; ', $this->d[21] , '</h2>';
        var_dump($this->d[20]);
        $this->data['ocupacao']  = '01';
        $this->data['atividade'] = '486';
    }
    
    public function getLocatario(){
        //procurar locatrio pelo nome
        $loc = $this->repLoct->findByNome($this->d[4]);
        foreach ($loc as $enty) {
            $this->data['locatario']      = $enty;
            $this->data['locatarioNome']  = $enty->getNome();
            return;
        }
        //Nao encontrou entao insere ele no BD
        $d['id'] = '';
        $d['nome'] = $this->d[4];
        $d['tipo'] = ($this->d[5] == 'F') ? 'fisica' : 'juridica' ;
        $d['cpf'] = ($this->d[5] == 'F') ? $this->d[6] : '' ;
        $d['cnpj'] = ($this->d[5] == 'F') ? '' : $this->d[6] ;
        $d['email'] = '';
        $d['tel'] = '';
        $d['status'] = 'A';
        $d['enderecos'] = '';
        $rs = $this->serLoct->insert($d);
        if($rs === TRUE){
            $loc = $this->serLoct->getEntity();
            $this->data['locatario']      = $loc;
            $this->data['locatarioNome']  = $loc->getNome();
        }else{
            echo '<h2>Erro ao inserir locatario; </h2>';
            var_dump($rs);
            var_dump($d);
            $this->data['locatario']      = '1';
            $this->data['locatarioNome']  = $this->d[4];
        }
    }
    
    public function getImovel(){
        //procurar imovel pelo rua, numero e locador
        $filtro['rua'] = $this->d[7];
        $filtro['numero'] = $this->d[8];
        $filtro['locador'] = $this->d[2];
        $entitys = $this->repImovel->findBy($filtro);
        foreach ($entitys as $entity) {
            $this->data['imovel'] = $entity;
            return;
        }
        $this->data['imovel'] = '';
        $this->data['cep'] = $this->d[13];
        $this->data['rua'] = $this->d[7];
        $this->data['numero'] = $this->d[8];
        $this->data['apto'] = '';
        $this->data['bloco'] = '';
        $this->data['compl'] = $this->d[9];
        $this->data['bairro'] = '';
        $this->data['bairroDesc'] = $this->d[10];
        $this->data['cidade'] = '';
        $this->data['cidadeDesc'] = $this->d[11];
        if ($this->d[12] == 'SP')
            $this->data['estado'] = '27';
        else {
            foreach ($this->paises as $key => $value) {
                if($this->d[12] == $value){
                    $this->data['estado'] = $key;
                    break;                    
                }
            }
        }
        $this->data['pais'] = '1';
    }
    
    public function setDate($dt){
        $d = explode('-', substr($dt, 0, 10));
        if (count($d) < 3){
            echo '<h2>Erro ao setar Data ; </h2>';
            var_dump($dt);
            var_dump($d);
            die;
        }
        return $d[2] . "/" . $d[1] . "/" . $d[0]; //retornar mes/dia/ano
    }

    public function getData($value,$key){
        $this->d = $this->csvToArray($value); 
        if(count($this->d) < 47){
            echo 'Erro qtd de campos incorreto !! registro ', $key;
            var_dump($value);
            die;
        }
        if (!isset($this->comissao[intval($this->d[33])])){
            $this->d[33] = 1 ;
        }    
        if (!isset($this->locador[intval($this->d[2])])){
            $this->d[2] = 1 ;
        }          
        $this->data = [];
        $this->data['id']             = $this->d[0];
        $this->data['inicio']         = $this->setDate($this->d[15]);
        $this->data['fim']            = $this->setDate($this->d[16]);
        $this->data['status']         = $this->d[45];
        if(strlen($this->d[39]) > 10){
            $s = explode(' ', $this->d[39]);
            $d = explode('/', $s[0]);
            if($key <= 6361){
                if($d[0] <= 12)
                    $this->data['criadoEm']       = new \DateTime($d[0] . '/' . $d[1] . '/' . $d[2]);
                else
                    $this->data['criadoEm']       = new \DateTime($d[1] . '/' . $d[0] . '/' . $d[2]);
            }else{
                if($d[1] <= 12)
                    $this->data['criadoEm']       = new \DateTime($d[1] . '/' . $d[0] . '/' . $d[2]);
                else
                    $this->data['criadoEm']       = new \DateTime($d[0] . '/' . $d[1] . '/' . $d[2]);
            }
        }else{
            $d = explode('/', $this->d[39]);
            if($d[0] <= 12)
                $this->data['criadoEm']       = new \DateTime($d[0] . '/' . $d[1] . '/' . $d[2]);
            else
                $this->data['criadoEm']       = new \DateTime($d[1] . '/' . $d[0] . '/' . $d[2]);
        }
        if ($this->d[40] != '' and $this->d[40] != "NULL"){
            if(strlen($this->d[40]) > 10){
                $s = explode(' ', $this->d[40]);
                $c = explode('/', $s[0]);
            }else{
                $c = explode('/', $this->d[40]);
            }
            if($key <= 6361){
                if($c[0] <= 12)
                    $this->data['canceladoEm']    = new \DateTime($c[0] . '/' . $c[1] . '/' . $c[2]);
                else
                    $this->data['canceladoEm']    = new \DateTime($c[1] . '/' . $c[0] . '/' . $c[2]);
            }else
                if($c[1] <= 12)
                    $this->data['canceladoEm']    = new \DateTime($c[1] . '/' . $c[0] . '/' . $c[2]);
                else
                    $this->data['canceladoEm']    = new \DateTime($c[0] . '/' . $c[1] . '/' . $c[2]);
        }else{
            $this->data['canceladoEm']    = new \DateTime('01/01/1000');
        }
        $this->data['alteradoEm']     = $this->data['criadoEm'];
        $this->data['codano']         = $this->d[1];
        $this->data['locador']        = $this->d[2];
        $this->data['locadorNome']    = $this->d[3];
        $this->getLocatario();
        $this->data['valorAluguel']   = $this->d[14];
        $this->data['tipoCobertura']  = $this->getTipoCob($this->d[28]);
        $this->data['seguroEmNome']   = ($this->d[17]=='LOCADOR') ? '01' : '02' ;
        $this->data['codigoGerente']  = $this->d[18];
        $this->data['refImovel']      = $this->d[19];
        $this->data['formaPagto']     = $this->getFomaPag($this->d[22]);
        $this->data['incendio']       = $this->d[23];
        $this->data['conteudo']       = $this->d[24];
        $this->data['aluguel']        = $this->d[25];
        $this->data['eletrico']       = $this->d[26];
        $this->data['vendaval']       = $this->d[27];
        $this->data['numeroParcela']  = $this->d[29];
        $this->data['premioLiquido']  = $this->d[30];
        $this->data['premio']         = $this->d[31];
        $this->data['premioTotal']    = $this->d[32];
        $this->data['observacao']     = $this->d[41];
        $this->data['gerado']         = '';
        $this->data['comissao']       = '';
        $this->data['mesNiver']       = $this->d[46];
        $this->getImovel();
        $this->getAtividade();
        $this->getTaxa();
        $this->data['seguradora']     = ($this->d[42] == 'M') ? 2 : 3 ;
        $this->data['administradora'] = $this->d[33];
       $this->data['multiplosMinimos']= ($this->d[42] == 'M') ? 1 : 2 ;
        $this->data['comissaoEnt']    = $this->comissao[intval($this->d[33])];
        $this->data['user']           = '1';
        $this->data['validade']       = $this->calcValidade($this->data['inicio'],$this->data['fim']);
              
        $this->data['taxaIof']        = '0,0738';
        $this->data['status']        = 'I';
        $this->data['codFechado']        = ($this->d[47] == 'NULL') ? '' : $this->d[47] ;
      //  $this->data['cobIncendio']    = $this->d[];
      //  $this->data['cobConteudo']    = $this->d[];
      //  $this->data['cobAluguel']     = $this->d[];
     //   $this->data['cobEletrico']    = $this->d[];
     //   $this->data['cobVendaval']    = $this->d[];
     //   $this->data['codFechado']     = $this->d[];
        return $this->data;
    }
    
    public function getTipoCob($d){
        switch ($d) {
            case '1':
                return '01';
                break;
            case '2':
                return '02';
                break;
            case '3':
                return '03';
                break;
            default:
                return '00';
                break;
        }
    }
    
    public function getFomaPag($forma){
        switch ($forma) {
            case 'ato':
                return '01';
                break;
            case '1-1':
                return '02';
                break;
            case '1-2':
                return '03';
                break;
            case 'mensal':
                return '04';
                break;
            default:
                echo '<h2>Erro ao pegar forma de pagamento; </h2>';
                var_dump($forma);
                return '';
        }
    }

    public function calcValidade($i,$f){
        $d = explode('/', $i);
        $i = (int) ($d[2] . $d[1] . $d[0]);
        $d = explode('/', $f);
        $f = (int) ($d[2] . $d[1] . $d[0]);
        return (($f - $i) > 100) ? 'anual' : 'mensal' ;
    }

    public function validaColunas($cols){
        $titStr = 'cod;codano;codcli;cliente;locatario;tipo;cpf_cnpj;endereco;num;comple;bairro;cidade;estado;cep;valor;ini_vig;fim_vig;seguro_no;cod_ger;refimo;ocupacao;desc_ocup;forma_pagto;inc;inc_con;alu;ele;ven;cobertura;n_parc;premioliq;premio;totpremio;ue;ui;uc;orca;pre_fec;fecha;data_inc;data_can;obs;cia;renovado;cartao;status;mes_aniver;codanterior;comissao';
        $tit = explode(';', $titStr);
        if($tit !== $cols){
            var_dump($tit);
            var_dump($cols);
            foreach ($cols as $key => $value) {
                if(isset($tit[$key]) and $value != $tit[$key]){
                    var_dump ($value);
                    var_dump ($tit[$key]);
                }
                
            }
            return FALSE;
        }
        return TRUE;
    }
    
    public function csvToArray($str){
        $linha = str_replace("\r\n","",trim($str));
        return explode(';',  $linha);
    }
 FIM DAS FUNÇOES DE IMPORTAÇÃO
 */     


}
