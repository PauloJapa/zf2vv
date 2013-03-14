<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Atividade
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class AtividadesController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Atividade";
        $this->form = "LivrariaAdmin\Form\Atividade";
        $this->service = "Livraria\Service\Atividade";
        $this->controller = "atividades";
        $this->route = "livraria-admin";
        
    }
    
    /**
     * Faz pesquisa no BD e retorna as variaveis de exbição
     * @param array $filtro
     * @return \Zend\View\Model\ViewModel|no return
     */
    public function indexAction(array $filtro = array()){
        $this->verificaSeUserAdmin();
        return parent::indexAction($filtro,array('descricao'=>'ASC'));
    }
    
    public function newAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        $this->formData = new $this->form(null, $this->getEm());
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
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        
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
    public function autoCompAction(){
        $descricao = trim($this->getRequest()->getPost('atividadeDesc'));
        $ocupacao = trim($this->getRequest()->getPost('autoComp'));
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($descricao .'%',$ocupacao);
        if(!$resultSet)// Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoComp('%'. $descricao .'%', $ocupacao);
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
}
