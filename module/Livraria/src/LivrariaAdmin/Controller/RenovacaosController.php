<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class RenovacaosController  extends CrudController {

    private $serviceFechado;
    
    public function __construct() {
        $this->entity = "Livraria\Entity\Renovacao";
        $this->form = "LivrariaAdmin\Form\Orcamento";
        $this->service = "Livraria\Service\Renovacao";
        $this->serviceFechado = "Livraria\Service\Fechados";
        $this->controller = "renovacaos";
        $this->route = "livraria-admin";
    }
    
    public function IndexAction() { 
        $this->verificaSeUserAdmin();
        return new ViewModel();
    }
    
    public function listarRenovadosAction() { 
        $this->verificaSeUserAdmin();
        $this->setRender(FALSE);
        parent::indexAction();
        return new ViewModel($this->getParamsForView());
    }
    
    public function buscarAction() { 
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $this->data = $this->getRequest()->getPost()->toArray();
        if ((isset($this->data['subOpcao']))&&($this->data['subOpcao'] == 'buscar'))  {
            $sessionContainer = new SessionContainer("LivrariaAdmin");
            $sessionContainer->data = $this->data;
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'lista'));
        }
        $this->formData = new $this->form();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());
    }
    
    /**
     * Lista os seguros fechados que ainda não foram renovados 
     * @return \Zend\View\Model\ViewModel
     */
    public function listaAction(){
        $this->verificaSeUserAdmin();
        $this->getDadosAnterior();
        $fechados = $this->getEm()
                ->getRepository($this->entity)
                ->findRenovar($this->data['inicio'], $this->data['fim'], $this->data['administradora']);
        return new ViewModel(['data' => $fechados]);
    }
    
    public function gerarRenovacaoAction(){
        $this->verificaSeUserAdmin();
        $this->getDadosAnterior();
        $fechados = $this->getEm()
                ->getRepository($this->entity)
                ->findRenovar($this->data['inicio'], $this->data['fim'], $this->data['administradora']);
        $service = new $this->service($this->getEm());
        foreach ($fechados as $fechado) {
            $resul = $service->renovar($fechado);
            if($resul[0] !== TRUE){
                foreach ($resul as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }
        }
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller,'action'=>'listarRenovados'));
    }
    
    /**
     * Busca os dados do formulario guardado na sessão
     */
    public function getDadosAnterior(){
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        $this->data = $sessionContainer->data;
    }
    
    public function editAction() {
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $sessionContainer = new SessionContainer("LivrariaAdmin");
        
        //Verifica se usuario tem registrado a administradora na sessao
        if(!isset($sessionContainer->administradora['id']) && ($this->getIdentidade()->getTipo() != 'admin')){
            if(!$this->verificaUserAction(FALSE))
                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
        }
        
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if($data['subOpcao'] == 'fechar'){ 
            $servicoFechado = new $this->serviceFechado($this->getEm());
            $resul = $servicoFechado->fechaRenovacao($data['id']);
            if($resul[0] === TRUE){
                $this->flashMessenger()->addMessage('Seguro fechado com sucesso!!!');
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
        
        $this->formData = new $this->form(null, $this->getEm(),$filtroForm);
        $this->formData->setForRenovacao();
        if($data['subOpcao'] == 'editar'){ 
            $this->formData->setData($entity->toArray());
            $data['administradora'] = $entity->getAdministradora()->getId();
            $data['status'] = $entity->getStatus();
        }else{
            $this->formData->setData($data);
        }
        
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
        
        //Metodo que bloqueia campos da edição caso houver
        $this->formData->setEdit();
        if($data['subOpcao'] == 'calcular'){
            if ($this->formData->isValid()){
                $service = new $this->service($this->getEm());
                $result = $service->update($data,'OnlyCalc');
                $this->formData->setData($service->getNewInputs());
                foreach ($result as $value) {
                    $this->flashMessenger()->addMessage($value);
                }
            }else{
                $this->flashMessenger()->addMessage('Primeiro Acerte os erros antes de calcular!!!');
            }
        }
        
        if($data['subOpcao'] == 'salvar'){
            if ($this->formData->isValid()){
                $service = new $this->service($this->getEm());
                $result = $service->update($data);
                if($result === TRUE){
                    $this->flashMessenger()->addMessage('Registro salvo com sucesso!!!');
                }else{
                    foreach ($result as $value) {
                        $this->flashMessenger()->addMessage($value);
                    }
                }
            }  
        }
          
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        
        $param['log']= 'logRenovacao';
        $param['tar']= '/admin/fechados';
        $param['prt']= '/admin/renovacaos/printPdf';
        $param['bak']= 'listarRenovados';
        
        return new ViewModel(array_merge($this->getParamsForView(),['param'=>$param])); 
    }
    
    
    public function printPdfAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        if(!isset($data['id']))
            $data['id'] = '1';
        
        $service = new $this->service($this->getEm());
        $service->getPdfRenovacao($data['id']);
    }
    
}
