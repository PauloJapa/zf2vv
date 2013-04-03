<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator,
    Zend\Paginator\Adapter\ArrayAdapter;
/**
 * ClasseAtividade
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ClasseAtividadesController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\ClasseAtividade";
        $this->form = "LivrariaAdmin\Form\ClasseAtividade";
        $this->service = "Livraria\Service\ClasseAtividade";
        $this->controller = "classeAtividades";
        $this->route = "livraria-admin";
        
    }
    
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function indexAction(array $filtro = array()){
        $this->verificaSeUserAdmin();
        $orderBy = array('atividade' => 'ASC', 'inicio'=>'DESC');
        if(!$this->render){
            return parent::indexAction($filtro, $orderBy);
        }
        $data = $this->getRequest()->getPost()->toArray();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setForClasseAtividade();
        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
            return parent::indexAction(['status'=>'A'], $orderBy);
        }
        $filtro=[];
        if(!empty($data['atividade'])){
            $filtro['atividade'] = $data['atividade'];
        }
        
        return parent::indexAction($filtro, $orderBy);
    }
   
    /**
     * Tenta incluir o registro e exibe a listagem ou erros ao incluir
     * @return \Zend\View\Model\ViewModel
     */ 
    public function newAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['atividade'])) $filtro['atividade'] = $data['atividade'];
        }
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        $this->formData->setData($data);
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

    /**
     * Edita o registro, Salva o registro, exibi o registro ou a listagem
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        $filtroForm = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'editar'){ 
            $repository = $this->getEm()->getRepository($this->entity);
            $entity = $repository->find($data['id']);
            $filtro['atividade']  = $entity->getAtividade()->getId();
        }
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['atividade'])) $filtro['atividade'] = $data['atividade'];
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
        // Pegando o serviço para manipular dados
        $service = $this->getServiceLocator()->get($this->service); 
        $repository = $this->getEm()->getRepository('Livraria\Entity\Atividade');
        $acumula = [];
        foreach ($content as $key => $value) {
            if($key == 0){
                if(!$this->validaColunas($this->csvToArray($value))){
                    echo 'Erro titulos da colunas estão incorretos!!';
                    var_dump($value);
                    return;
                }
                continue;
            }
            
            //Acumulando dado iguais para fazer uma inserção unica!!!
            $d = $this->csvToArray($value);
            if(empty($acumula) OR $acumula[0][2] == $d[2]){
                $acumula[] = $d;
                continue;
            }
            
            foreach ($acumula as $key => $value) {
                if(isset($acumula[$key + 1])){
                    $dd['id'] = '' ;
                    $dd['inicio'] = $value[5] ;
                    $dd['fim'] = $acumula[$key + 1][5] ;
                    $dd['status'] = 'C' ;
                    if($value[3] == 'EX' or $value[3] == 'SC')$value[3] = 12 ;
                    $dd['classeTaxas'] = $value[3];
                    $dd['atividade'] = $this->getAtividade($value,$repository);
                    $dd['codOld'] =  $value[0];
                    $dd['codciaOld'] = $value[2] ;
                    $dd['seq'] = $value[6] ;
                    $this->inclui($service,$dd);
                    $acumula = [];
                    continue;
                }
                $dd['id'] = '' ;
                $dd['inicio'] = $value[5] ;
                $dd['fim'] = '01/01/0001' ;
                $dd['status'] = 'A' ;
                if($value[3] == 'EX' or $value[3] == 'SC')$value[3] = 12 ;
                $dd['classeTaxas'] = $value[3];
                $dd['atividade'] = $this->getAtividade($value,$repository);
                $dd['codOld'] =  $value[0];
                $dd['codciaOld'] = $value[2] ;
                $dd['seq'] = $value[6] ;              
                $this->inclui($service,$dd);
                $acumula = [];
            }
        }        
    }
    
    public function getAtividade(&$d, &$r){
        $entity = $r->findBy(['codSeguradora' => $d[2]]);
        if($entity){
            return $entity[0]->getId();
        }
        // Não encontrou entao inclui atividade como excluida
        $service = $this->getServiceLocator()->get("Livraria\Service\Atividade"); 
        $dados = [
            'id' => '',
            'descricao' => $d[1],
            'equipEletro' => '',
            'danosEletricos' => '',
            'vendavalFumaca' => '',
            'codSeguradora' => $d[2],
            'basica' => '',
            'roubo' => '',
            'status' => 'C',
            'ocupacao' => ($d[4] == 'Indústria') ? '03' : '01',
        ];
        $rs = $service->insert($dados);
        if($rs === TRUE){
            echo 'Importado Atividade; ', implode(';', $dados) , '<br>';
            return $service->getEntity()->getId();
        }else                
            var_dump($rs);
            return $rs[1]->getId();
    }

    public function inclui(&$s,&$dd){
        $resul = $s->insert($dd);
        if($resul === TRUE){
            echo 'Importado; ', implode(';', $dd) , '<br>';
        }else                
            var_dump($resul);
    }

    public function validaColunas($cols){
        $titStr = 'cod;descricao;codcia;classe;tipo;data;sequencia';
        $tit = explode(';', $titStr);
        if($tit !== $cols){
            var_dump($tit);
            var_dump($cols);
            return FALSE;
        }
        return TRUE;
    }
    
    public function csvToArray($str){
        //var_dump(utf8_decode($str));
        $linha = str_replace("\r\n","",trim($str));
        return explode(';', $linha);
    }

}
