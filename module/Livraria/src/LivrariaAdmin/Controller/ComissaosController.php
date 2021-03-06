<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Comissao
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class ComissaosController extends CrudController {

    public function __construct() {
        $this->entity = "Livraria\Entity\Comissao";
        $this->form = "LivrariaAdmin\Form\Comissao";
        $this->service = "Livraria\Service\Comissao";
        $this->controller = "comissaos";
        $this->route = "livraria-admin";
        
    }
    
    public function indexAction(){
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setForAdministradora();
        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
            return parent::indexAction(['status'=>'A'], ['inicio'=>'DESC']);
        }
        $filtro=[];
        if(!empty($data['administradora'])){
            $filtro['administradora'] = $data['administradora'];
        }
        
        return parent::indexAction($filtro, ['inicio'=>'DESC']);
    }
    
    public function newAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        $this->formData = new $this->form(null, $this->getEm());
        
        if(isset($data['administradora'])){
            $this->formData->setComissaoOptions($data['administradora']);
        }
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

    public function editAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        if (!isset($data['subOpcao'])) {
            $data['subOpcao'] = '';
        }

        if($data['subOpcao'] == 'editar'){ 
            $repository = $this->getEm()->getRepository($this->entity);
            $entity = $repository->find($data['id']);
        }
        
        $this->formData = new $this->form(null, $this->getEm());
        //Metodo que bloqueia campos da edição caso houver
        //$this->formData->setEdit();
        if($data['subOpcao'] == 'editar'){ 
            $this->formData->setComissaoOptions($entity->getAdministradora()->getId());
            $this->formData->setData($entity->toArray());
        }else{
            $this->formData->setComissaoOptions($data['administradora']);
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
        
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        return new ViewModel($this->getParamsForView()); 
    }

    public function getLastAdmComissaoAction() {
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getPost()->toArray();
        $repository = $this->getEm()->getRepository($this->entity);
        $entity = $repository->findOneBy(['administradora' => $data['administradora']],['inicio'=>'DESC']);
        if($entity){
            $id = $entity->getId();
        }else{
            $id = 'novo';
        }
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        $view = new ViewModel(array_merge(['id' => $id, 'administradora' => $data['administradora']],$this->getParamsForView()));
        return $view;
    }

    public function acertaAction() {
        /* @var $repository \Livraria\Entity\ComissaoRepository */        
        $this->verificaSeUserAdmin();
        $rp = $this->getEm()->getRepository($this->entity);
        $all = $rp->findAll();
        /* @var $commisao \Livraria\Entity\Comissao */
        $int = 0;
        foreach ($all as $commisao){
            $commisao->setMultAluguelRes($commisao->getMultAluguel());
            $commisao->setMultConteudoRes($commisao->getMultConteudo());
            $commisao->setMultEletricoRes($commisao->getMultEletrico());
            $commisao->setMultIncendioRes($commisao->getMultIncendio());
            $commisao->setMultVendavalRes($commisao->getMultVendaval());
            $this->getEm()->persist($commisao);
            $int++;
            
            echo '<h3> ok', $commisao->getAdministradora()->getId() , '</h3>';
            echo '<pre>', var_dump($commisao->toArray()), '</pre>';
        }
        $this->getEm()->flush();
        echo '<h1> Total', $int , '</h1>';
        die;
    }
}
