<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;

class EnderecosController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Endereco";
        $this->form = "LivrariaAdmin\Form\Endereco";
        $this->service = "Livraria\Service\Endereco";
        $this->controller = "enderecos";
        $this->route = "livraria-admin";
    }
    
    public function newAction() {
        $form = $this->getServiceLocator()->get($this->form);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->insert($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function editAction() {
        $form = $this->getServiceLocator()->get($this->form);
        $request = $this->getRequest();

        $repository = $this->getEm()->getRepository($this->entity);
        $entity = $repository->find($this->params()->fromRoute('id', 0));

        if ($this->params()->fromRoute('id', 0))
            $form->setData($entity->toArray());

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->update($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }

        return new ViewModel(array('form' => $form));
    }
    
    public function buscaCepAction(){
        $cep = $this->getRequest()->getPost('cep');         
        $retorno = @file_get_contents('https://newsis.tcmed.com.br/cep-ajax?ajax=ok&cep='.urlencode($cep).'&format=json'); 
        if(!$retorno){
            $retorno = @file_get_contents('http://cep.republicavirtual.com.br/web_cep.php?cep='.urlencode($cep).'&formato=json'); 
        }
//        $retorno = @file_get_contents('http://177.185.194.123/web_cep.php?cep='.urlencode($cep).'&formato=json'); 
        if(!$retorno){ 
            $retorno = '{"resultado":"0","resultado_txt":"erro ao buscar cep"}'; 
        }
        $resultado = json_decode($retorno, true);
        $resultado['cep'] = $cep;
        $resultado['pais'] = 'Brasil';        
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultado' => $resultado,'cep'=> $cep));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

}
