<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class RenovacaosController  extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Renovacao";
        $this->form = "LivrariaAdmin\Form\Renovacao";
        $this->service = "Livraria\Service\Renovacao";
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
    
}
