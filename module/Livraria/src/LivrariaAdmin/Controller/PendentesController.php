<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class PendentesController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\\";
        $this->form = "LivrariaAdmin\Form\Relatorio";
        $this->service = "Livraria\Service\Pendentes";
        $this->controller = "pendentes";
        $this->route = "livraria-admin";
    }
    
    public function indexAction(){
        $data = $this->filtrosDaPaginacao();
        //usuario admin pode ver tudo os outros são filtrados
        $user = $this->getIdentidade();
        if($user->getTipo() != 'admin'){
            $sessionContainer = new SessionContainer("LivrariaAdmin");
            //Verifica se usuario tem registrado a administradora na sessao
            if(!isset($sessionContainer->administradora['id'])){
                $sessionContainer->administradora = $user->getAdministradora();
                //$this->verificaUserAction(FALSE);
            }
            $data['administradora'] = $sessionContainer->administradora['id'];
            $data['administradoraDesc'] = $sessionContainer->administradora['nome'];
        }
        $this->formData = new $this->form($this->getEm());
        $this->formData->setMapaRenovacao();
        $this->formData->setData((is_null($data)) ? [] : $data);
        
        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView()); 
    }
    
    public function listarPendentesAction(){
        $data = $this->filtrosDaPaginacao();
        $srv = new $this->service($this->getEm());
        $this->paginator = $srv->getPendentes($data);
        $formaPagto = $this->getEm()->getRepository('Livraria\Entity\ParametroSis')->fetchPairs('formaPagto');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data, 'formaPagto' => $formaPagto]));         
    }
    
}
