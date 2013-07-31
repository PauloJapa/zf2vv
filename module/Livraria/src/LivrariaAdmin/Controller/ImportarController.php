<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

class ImportarController extends CrudController {    
    
    public function __construct() {
        $this->entity = "Livraria\Entity\Orcamento";
        $this->form = "LivrariaAdmin\Form\Importar";
        $this->service = "Livraria\Service\Importar";
        $this->controller = "importar";
        $this->route = "livraria-admin";        
    }
    
    /**
     * Tela com Seleção do arquivo a ser importado.
     * @return \Zend\View\Model\ViewModel
     */
    public function selecionarAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());
    }
    
    public function uploadAction(){
        $this->verificaSeUserAdmin();
        // Pegando o serviço para salvar o arquivo
        $service =  new $this->service($this->getEm());  
        $service->upload($this->getRequest()->getFiles()->toArray());
        // Redirecionar para listar conteudo do arquivo
        return $this->redirect()->toRoute($this->route, array('controller' => $this->controller, 'action' => 'lista'));
    }
    
    public function listaAction(){
        $this->verificaSeUserAdmin();
        // Pegando o serviço ler o arquivo
        $service =  new $this->service($this->getEm()); 
        $data = $service->fileToArray();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(), ['data' => $data]));
    }
    
    public function gerarOrcamentoAction(){
        $this->verificaSeUserAdmin();
        // Pegando o serviço para gerar orçamento
        $service =  new $this->service($this->getEm()); 
        $data = $service->importar();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge($this->getParamsForView(), ['data' => $data]));        
    }
    
    public function toExcelImpResulAction(){
        $this->verificaSeUserAdmin();
        // Pegando o serviço para gerar orçamento
        $service =  new $this->service($this->getEm()); 
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $service->getImpResul()));
        $viewModel->setTerminal(true);
        return $viewModel;            
    }
    
    public function toExcelImpResulErrAction(){
        $this->verificaSeUserAdmin();
        // Pegando o serviço para gerar orçamento
        $service =  new $this->service($this->getEm()); 
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $service->getImpResul()));
        $viewModel->setTerminal(true);
        return $viewModel;         
    }
    
}
