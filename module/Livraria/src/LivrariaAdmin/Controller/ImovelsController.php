<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use SisBase\Conexao\Mysql;
/**
 * Imovel
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ImovelsController extends CrudController {

    /**
     * Parmetros do Crudcontroller
     */
    public function __construct() {
        $this->entity = "Livraria\Entity\Imovel";
        $this->form = "LivrariaAdmin\Form\Imovel";
        $this->service = "Livraria\Service\Imovel";
        $this->controller = "imovels";
        $this->route = "livraria-admin";
        
    }
    
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function indexAction(array $filtro = array()){
        $this->verificaSeUserAdmin();
        $orderBy = array('rua' => 'ASC', 'numero' => 'ASC');
        if(!$this->render){
            return parent::indexAction($filtro, $orderBy);
        }
        $data = $this->filtrosDaPaginacao();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setLocadorLocatario();
        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
            return parent::indexAction($filtro, $orderBy);
        }
        $this->formData->setData($data);
        $filtro=[];
        
        if(!empty($data['rua'])){
            $filtro['rua'] = $data['rua'];
        }
        if(!empty($data['refImovel'])){
            $filtro['refImovel'] = $data['refImovel'];
        }
        if(!empty($data['locador'])){
            $filtro['locador'] = $data['locador'];
        }
        if(!empty($data['locatario'])){
            $filtro['locatario'] = $data['locatario'];
        }
        
        $list = $this->getEm()
                    ->getRepository($this->entity)
                    ->pesquisa($filtro);
        
        return parent::indexAction($filtro, $orderBy, $list);
    }

    /**
     * Tenta incluir o registro e exibe a listagem ou erros ao incluir
     * @return \Zend\View\Model\ViewModel
     */
    public function newAction() {
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            $filtro['locador']= $data['locador'];
            if(!empty($data['rua']))   $filtro['rua']    = $data['rua'];
            if(!empty($data['numero']))$filtro['numero'] = $data['numero'];
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

    /**
     * Edita o registro, Salva o registro, exibi o registro ou a listagem
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction() {
        $this->formData = new $this->form(null, $this->getEm());
        $this->formData->setEdit();
        $data = $this->getRequest()->getPost()->toArray();
        $repository = $this->getEm()->getRepository($this->entity);
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        switch ($data['subOpcao']){
        case 'editar':    
            $entity = $repository->find($data['id']);
            $filtro['locador'] = $entity->getLocador()->getId();
            $filtro['rua'] = $entity->getRua();
            $filtro['numero'] = $entity->getNumero();
            $this->formData->setData($entity->toArray());
            break;
        case 'buscar':  
            $filtro['locador']= $data['locador'];
            if(!empty($data['rua']))   $filtro['rua']    = $data['rua'];
            if(!empty($data['numero']))$filtro['numero'] = $data['numero'];
            $this->formData->setData($data);  
            break;
        case 'salvar':   
            $filtro['locador']= $data['locador'];
            if(!empty($data['rua']))   $filtro['rua']    = $data['rua'];
            if(!empty($data['numero']))$filtro['numero'] = $data['numero'];
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
     * Na view retorna os dados no formato texto para o js exibir para o usuario
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function autoCompRuaAction(){
        $subOpcao = $this->getRequest()->getPost('subOpcao','');
        $autoComp = $this->getRequest()->getPost('autoComp');
        $param = trim($this->getRequest()->getPost($autoComp,''));
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($param .'%');
        if(!$resultSet)// Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoCompRua('%'. $param .'%');
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet, 'subOpcao'=>$subOpcao));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    public function acertarefAction(){
        $this->mypdo = new Mysql();
        $this->achou = 0;
        $this->nachou = 0;
        $this->tachou = 0;
        $sql = "SELECT id, ref_imovel, locador_id, locatario_id, imovel_id "
        . "FROM `orcamento` "
        . "WHERE `inicio` "
        . "BETWEEN '2014-09-01 00:00:00' AND '2014-09-31 00:00:00'"
        . "AND (`ref_imovel` LIKE ''OR `ref_imovel` LIKE ' ')"
        . "AND `administradoras_id` = 196";
        $this->mypdo->p($sql);
        $this->mypdo->e();
        $data = $this->mypdo->fAll();
        echo '<table>';
        echo '<tr><td>id</td><td>ref_imovel</td><td>locador_id</td><td>locatario_id</td><td>imovel_id</td></tr>';
        foreach ($data as $key => $value) {
            echo '<tr><td>', $value['id'], '</td><td>', $value['ref_imovel'], '</td><td>', $value['locador_id'], '</td><td>', $value['locatario_id'], '</td><td>', $value['imovel_id'], '</td></tr>';
            echo '<tr><td colspan=4>';
            $this->findRef($value);
            echo '</td></tr>';
        }
        echo '</table>';        
        echo '<p>Achou total de ', $this->achou;        
        echo '<p>Não achou total de', $this->nachou;        
        echo '<p>Talvez achou total de', $this->tachou;        
    }
    
    public function findRef($d){
        $sql = "SELECT id, ref_imovel, locador_id, locatario_id, imovel_id 
                FROM  `orcamento` 
                WHERE  `inicio` >  '2012-01-01 00:00:00'
                AND  `imovel_id` = " . $d['imovel_id'] . "
                AND  `ref_imovel` NOT LIKE '' 
                AND  `ref_imovel` NOT LIKE ' '
                AND  `ref_imovel` NOT LIKE '  '
                AND  `administradoras_id` =196
                ORDER BY  `inicio` DESC limit 2";
        $this->mypdo->p($sql);
        $this->mypdo->e();
        $data = $this->mypdo->fAll();
        $this->head = 'ACHOU id';
        if (empty($data)){
            $data = $this->findRefByLocadorLocatario($d);
            if (empty($data)){
                echo 'não achou';
                $this->nachou ++;
                return;
            }                
            $this->tachou ++;
        }else{            
            $this->achou ++;
        }
        echo '<table>';
        echo '<tr><td>', $this->head, '</td><td>ref_imovel</td><td>locador_id</td><td>locatario_id</td><td>imovel_id</td></tr>';
        foreach ($data as $key => $value) {
            echo '<tr><td>', $value['id'], '</td><td>', $value['ref_imovel'], '</td><td>', $value['locador_id'], '</td><td>', $value['locatario_id'], '</td><td>', $value['imovel_id'], '</td></tr>';
            if (!empty($value['ref_imovel']) AND $this->gravaRef($d, $value)){                
                break;
            }
        }
        echo '</table>';  
    }
    
    public function findRefByLocadorLocatario($d){        
        $this->head = 'Talvez ACHOU id';
        $sql = "SELECT id, ref_imovel, locador_id, locatario_id, imovel_id 
                FROM  `orcamento` 
                WHERE  `inicio` >  '2012-01-01 00:00:00'
                AND  `locador_id` = " . $d['locador_id'] . "
                AND  `locatario_id` = " . $d['locatario_id'] . "
                AND  `ref_imovel` NOT LIKE '' 
                AND  `ref_imovel` NOT LIKE ' '
                AND  `ref_imovel` NOT LIKE '  '
                AND  `administradoras_id` =196
                ORDER BY  `inicio` DESC limit 2";
        $this->mypdo->p($sql);
        $this->mypdo->e();
        $data = $this->mypdo->fAll();
return $data;
        if(!empty($data)){
            return $data;
        }        
        $this->head = 'Talvez ACHOU sem locatario id';
        $sql = "SELECT id, ref_imovel, locador_id, locatario_id, imovel_id 
                FROM  `orcamento` 
                WHERE  `inicio` >  '2012-01-01 00:00:00'
                AND  `locador_id` = " . $d['locador_id'] . "
                AND  `ref_imovel` NOT LIKE '' 
                AND  `ref_imovel` NOT LIKE ' '
                AND  `ref_imovel` NOT LIKE '  '
                AND  `administradoras_id` =196
                ORDER BY  `inicio` DESC limit 2";
        $this->mypdo->p($sql);
        $this->mypdo->e();
        return $this->mypdo->fAll();
    }
    
    public function gravaRef($tar, $get) {
        $sql = "UPDATE imovel set ref_imovel = '" . $get['ref_imovel'] . "' where id = " . $tar['imovel_id'];
        $imovel = $this->mypdo->q($sql);
        $sql = "UPDATE orcamento set ref_imovel = '" . $get['ref_imovel'] . "' where id = " . $tar['id'];
        $orcame = $this->mypdo->q($sql);
        $sql = "UPDATE fechados  set ref_imovel = '" . $get['ref_imovel'] . "' where imovel_id = " . $tar['imovel_id'];
        $fechad = $this->mypdo->q($sql);
        if($imovel AND $orcame AND $fechad){
            return true;
        }
        echo '<pre>';
        var_dump($imovel);
        var_dump($orcame);
        var_dump($fechad);
        echo '</pre>';
    }
}