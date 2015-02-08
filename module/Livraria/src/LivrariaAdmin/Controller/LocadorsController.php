<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Locador
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LocadorsController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Locador";
        $this->form = "LivrariaAdmin\Form\Locador";
        $this->service = "Livraria\Service\Locador";
        $this->controller = "locadors";
        $this->route = "livraria-admin";
        
    }
    
    public function indexAction(array $filtro = array()){
        $this->verificaSeUserAdmin();
        $orderBy = array('nome' => 'ASC');
        if(!$this->render){
            return parent::indexAction($filtro, $orderBy);
        }
        $data = $this->filtrosDaPaginacao();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
            return parent::indexAction(['status'=>'A'], $orderBy);
        }
        $this->formData->setData($data);
        $filtro=[];
        if(!empty($data['nome'])){
            $filtro['nome'] = $data['nome'];
        }
        if(!empty($data['documento'])){
            $filtro[$data['cpfOuCnpj']] = $data['documento'];
        }
        
        $list = $this->getEm()
                    ->getRepository($this->entity)
                    ->pesquisa($filtro);
        
        return parent::indexAction($filtro, $orderBy, $list);
    }

    public function newAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['cpf']))           $filtro['cpf']           = $data['cpf'];
            if(!empty($data['cnpj']))          $filtro['cnpj']          = $data['cnpj'];
            if(!empty($data['administradora']))$filtro['administradora']= $data['administradora'];
            $this->formData->setData($data);
        }
        
        if($data['subOpcao'] == 'salvar'){
            if ($this->formData->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $result = $service->insert($data);
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

    public function editAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form(null, $this->getEm());
        $this->formData->setEdit();
        $data = $this->getRequest()->getPost()->toArray();
        $repository = $this->getEm()->getRepository($this->entity);
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        switch ($data['subOpcao']){
        case 'editar':    
            $entity = $repository->find($data['id']);
            $filtro['cpf']   = $entity->getCpf();
            $filtro['cnpj']  = $entity->getCnpj();
            $this->formData->setData($entity->toArray());
            break;
        case 'buscar': 
            if(!empty($data['cpf']))       $filtro['cpf']           = $data['cpf'];
            if(!empty($data['cnpj']))      $filtro['cnpj']          = $data['cnpj'];
            $this->formData->setData($data);  
            break;
        case 'salvar': 
            $this->formData->setData($data);
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
            break;
        }
            
        $this->setRender(FALSE);
        $this->indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }
    
    /**
     * 
     * Configura um chamada para o repositorio que
     * Faz uma busca no BD pela requisição Ajax com parametro de busca
     * Na view retorna os dados no formato texto para o js exibir para o locators
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function autoCompAction(){
        
        $subOpcao = $this->getRequest()->getPost('subOpcao','');
        $locadorNome = trim($this->getRequest()->getPost('locadorNome',''));
        if (empty($locadorNome)) {
            $locadorNome = trim($this->getRequest()->getPost('locadorDesc', ''));
        }
        $administradora = trim($this->getRequest()->getPost('administradora',''));
        
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($locadorNome .'%',$administradora);
        if (!$resultSet) { // Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoComp('%' . $locadorNome . '%', $administradora);
        }
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet, 'subOpcao'=>$subOpcao));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    
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
        $list = $this->getEm()->getRepository('Livraria\Entity\Administradora')->findAll();
        foreach ($list as $ent) {
            $this->adm[$ent->getId()] = $ent->getNome();
        }
        // Pegando o serviço para manipular dados
        $service = new $this->service($this->getEm()); 
        $service->notValidateNew();
        $service->setFlush(FALSE);
        $cont = 200  ;
        foreach ($content as $key => $value) {
            if($key == 0){
                if(!$this->validaColunas($this->csvToArray($value))){
                    echo 'Erro titulos da colunas estão incorretos!!';
                    var_dump($value);
                    return;
                }
                continue;
            }
            $resul = $service->insert($this->getData($value));
            if($resul === TRUE){
                echo '<p>Importado; ', $value , '</p>';
                if($cont < $key){
                    $this->getEm()->flush();
                    $cont += 400;
                }
                continue;
            }
            set_time_limit(0);
            echo '<h2>Erro ao importar; ', $value , '</h2>';
            var_dump($value);
            var_dump($resul);
        }        
        $this->getEm()->flush();
    }
    
    public function getData($value){
        $d = $this->csvToArray($value);
        $data['id']                 = $d[0];
        $data['nome']               = trim($d[1]);
        $data['tipo']               = ($d[2] == 'F') ? 'fisica' : 'juridica' ;
        $data['cpf']                = ($d[2] == 'F') ? $d[3] : '' ;
        $data['cnpj']               = ($d[2] == 'F') ? '' : $d[3] ;
        $data['tel']                = '';
        $data['email']              = '';
        $data['status']             = $d[5];
        $data['administradora']     = isset($this->adm[(int)$d[4]]) ? $d[4] : '2022' ;
        $data['endereco']           = '';
        return $data ;
    }
    
    public function validaColunas($cols){
        $titStr = 'cod;cliente;tipo;cpf_cnpj;ue;status';
        $tit = explode(';', $titStr);
        if($tit !== $cols){
            var_dump($tit);
            var_dump($cols);
            return FALSE;
        }
        return TRUE;
    }
    
    public function csvToArray($str){
        $linha = str_replace("\r\n","",trim($str));
        return explode(';',  $linha);
    }
    
    /**
     * Função que altera ou inclui o Locador recebendo os dados via ajax.
     * @return \Zend\View\Model\ViewModel
     */    
    public function saveAction(){
        /* @var $service \Livraria\Service\Locador */
        $service = $this->getServiceLocator()->get($this->service);
        $data = $this->getRequest()->getPost()->toArray();
        
        $data['nome'] = $data['locadorNome'];
        $data['tipo'] = $data['tipoLoc'];
        $data['cpf']  = $data['cpfLoc'];
        $data['cnpj'] = $data['cnpjLoc'];
        if(empty($data['locador'])){
            $data['id'] = '';
            $resul = $service->insert($data);            
            $ret['ok']['msg'] = "Locador incluido com sucesso!!";
            if($resul === TRUE){
                $ret['ok']['locador'] = $service->getEntity()->getId();
            }
        }else{
            $data['id'] = $data['locador'];
            $resul = $service->update($data);
            $ret['ok']['msg'] = "Locador alterado com sucesso!!";
        }
        if($resul !== TRUE){
            $ret = [];
            $ret['msg'] = 'Não foi possivel salvar este Locador';
            $ret['erro'] = $resul;
        }
        
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(['ret' => $ret]);
        $viewModel->setTerminal(true);
        return $viewModel;        
    }

}
