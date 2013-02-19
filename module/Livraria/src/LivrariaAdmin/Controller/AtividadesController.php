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
