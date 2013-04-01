<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

class AdministradorasController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Administradora";
        $this->form = "LivrariaAdmin\Form\Administradora";
        $this->service = "Livraria\Service\Administradora";
        $this->controller = "administradoras";
        $this->route = "livraria-admin";
        
    }
    
    public function indexAction(array $filtro = array()){
        return parent::indexAction($filtro);
    }
    
    public function newAction() {
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
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
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'editar'){ 
            $repository = $this->getEm()->getRepository($this->entity);
            $entity = $repository->find($data['id']);
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
    
    public function autoCompAction(){
        $administradora = $this->getRequest()->getPost('administradoraDesc');
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($administradora .'%');
        if(!$resultSet)// Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoComp('%'. $administradora .'%');
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    public function importarAction(){
        $data = $this->getRequest()->getFiles()->toArray();
        //Verificando a existencia do arquivo
        $content  = file($data['content']['tmp_name']);
        if(!$content){
            echo 'arquivo não encontrado!!';
            return;
        }
        // Pegando o serviço para manipular dados
        $serviceAdm = $this->getServiceLocator()->get($this->service);   
        $serviceCom = $this->getServiceLocator()->get('Livraria\Service\Comissao');   
        foreach ($content as $key => $value) {
            if($key == 0){
                if(!$this->validaColunas($this->csvToArray($value))){
                    echo 'Erro titulos da colunas estão incorretos!!';
                    return;
                }
                continue;
            }
            $resul = $serviceAdm->insert($this->getDataAdm($value));
            if($resul === TRUE){
                $resul = $serviceCom->insert($this->getDataCom($value));
                if($resul === TRUE){
                    echo 'Importado; ', $value , '<br>';
                    continue;
                }                
            }
            var_dump($resul);
        }        
    }
    
    public function getDataAdm($value){
        $d = $this->csvToArray($value);
        return [
            'id' => $d[0],
            'nome' => $d[2],
            'email' => $d[3],
            'seguradora' => ($d[7] == 'M') ? '2' : '3',
            'cnpj' => $d[0],
            'status' => 'A'
        ];
    }

    public function getDataCom($value) {
        $d = $this->csvToArray($value);
        return [
            'id' => '',
            'administradora' => $d[0],
            'multIncendio' => $d[4],
            'multConteudo' => $d[5],
            'multAluguel' => $d[6],
            'multEletrico' => $d[9],
            'multVendaval' => $d[8],
            'inicio' => '01/01/2000',
            'fim' => '',
            'comissao' => '50,00',
            'status' => 'A'
        ];
    }

    public function validaColunas($cols){
        $titStr = 'cod_ue;sen_ue;nome;email;vezes_inc;vezes_inc_con;vezes_alu;cia;vezes_alu_A;vezes_ele_A';
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

}
