<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

class ExportarController extends CrudController {    
    
    public function __construct() {
        $this->entity = "Livraria\Entity\Fechados";
        $this->form = "LivrariaAdmin\Form\Relatorio";
        $this->service = "Livraria\Service\Exporta";
        $this->controller = "exportar";
        $this->route = "livraria-admin";
        
    }
    public function colAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form($this->getEm());
        $this->formData->setCOL();
        $this->formData->setData($this->filtrosDaPaginacao());
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());  
    }
    
    public function listaExptColAction(){
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->listaExpt($data);
        $data['inicio'] = $service->getFiltroTratado('inicio')->format('d/m/Y');
        $data['fim']    = $service->getFiltroTratado('fim')->format('d/m/Y');
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data]));         
    }
    
    public function geraExportaColAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $this->service .= "Col";
        $service = new $this->service($this->getEm());
        $resul = $service->geraTxtForCOL($data['id']);
        /* Cancelado pois não irá gravar direto no Col e sim gerar arquivo txt 16/09/2013
        $resul = $service->geraExpForCOL($data['id']);
         */
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $resul,'admFiltro' => $data['id']));
        $viewModel->setTerminal(true);
        return $viewModel;           
    }
    
    /**
     * Tela com filtro para pesquisa dos dados
     * @return \Zend\View\Model\ViewModel
     */
    public function maritimaAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form($this->getEm());
        $this->formData->setCOL();
        $this->formData->setData($this->filtrosDaPaginacao());
        $this->formData->get('seguradora')->setValue('2');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());
    }
    
    /**
     * Exibe os registros filtrados para conferencia
     * @return \Zend\View\Model\ViewModel
     */
    public function listaExptMarAction(){
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->listaExpt($data);
        $data['inicio'] = $service->getFiltroTratado('inicio')->format('d/m/Y');
        $data['fim']    = $service->getFiltroTratado('fim')->format('d/m/Y');
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data]));        
    }
    
    /**
     * Gera um arquivo compactado com os registros selecionados da tela zipado
     * separado pelo tipo comercial e residencial que por sua vez é sub dividido pelo tipo de parcelamento
     * @return \Zend\View\Model\ViewModel
     */
    public function geraExportaAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $resul = $service->geraArqsForMaritima($data['id']);
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $resul,'admFiltro' => $data['id']));
        $viewModel->setTerminal(true);
        return $viewModel;           
    }
    
    /**
     * Filtros para gerar arquivo para emissão de cartão tela de seleção de filtros
     * @return \Zend\View\Model\ViewModel
     */
    public function cartaoAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form($this->getEm());
        $this->formData->setCartao();
        $this->formData->setData($this->filtrosDaPaginacao());
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());  
    }
    
    /**
     * Exibir listagem para verificação do usuario baseado nos filtros escolhidos.
     * @return \Zend\View\Model\ViewModel
     */
    public function listaCartaoAction(){
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->listaCartao($data);
        $data['inicio'] = $service->getFiltroTratado('inicio')->format('d/m/Y');
        $data['fim']    = $service->getFiltroTratado('fim')->format('d/m/Y');
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(),['date' => $data]));        
    }
    
    /**
     * Gera um arquivo texto com os registros dos cartão a serem gerados
     * @return \Zend\View\Model\ViewModel
     */
    public function geraCartaoAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $resul = $service->geraArqsForCartao($data['id']);
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $resul,'admFiltro' => $data['id']));
        $viewModel->setTerminal(true);
        return $viewModel;           
    }
}
