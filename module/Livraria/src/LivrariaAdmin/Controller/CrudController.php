<?php

namespace LivrariaAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator,
    Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator,
    DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Session\Container as SessionContainer;

abstract class CrudController extends AbstractActionController {

    /**
     * Para Manipular Entity e outras coisas no BD
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    
    /**
     * Objeto que pega os dados do usuario armazenado
     * @var \Zend\Authentication\AuthenticationService
     */
    protected $authService;
    
    /**
     * Objeto que manipula os dados do usuario armazenado
     * @var \Zend\Session\Container
     */
    protected $sc;
    
    /**
     *
     * @var type 
     */
    protected $service;
    protected $entity;
    protected $form;
    protected $formData;
    protected $route;
    protected $controller;
    protected $paginator;
    protected $page;
    protected $route2;
    protected $autoCompParams;
    protected $render = TRUE;
    protected $data;

    /**
     * Faz listagem dos dados baseado nos parametros passados
     * @param array $filtro
     * @param array $orderBy
     * @param array $list
     * @return \Zend\View\Model\ViewModel | no return
     */
    public function indexAction(array $filtro = [],array $orderBy = [], $list = []) {
        if (empty($list)) {
            $list = $this->getEm()
                    ->createQueryBuilder()
                    ->select('e')
                    ->from($this->entity, 'e');
            //Montar Filtros
            if(!empty($filtro)){
                $and = '';
                $where = '';
                foreach ($filtro as $key => $value) {
                    $where .= $and . ' e.' . $key . ' = :' . $key ;
                    $and = ' AND';
                }
                $list->where($where)
                     ->setParameters($filtro);
            }
            //Montar Ordenação
            foreach ($orderBy as $key => $value) {
                $list->addOrderBy('e.' . $key,$value);
            }
        }

        $this->page = $this->params()->fromRoute('page');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        
        
        $doctrinePaginator = new DoctrinePaginator($list);
        $paginatorAdapter = new PaginatorAdapter($doctrinePaginator);
        $this->paginator = new Paginator($paginatorAdapter);
        $this->paginator->setCurrentPageNumber($this->page);
        $this->paginator->setDefaultItemCountPerPage(150);
        $this->paginator->setPageRange(20);
        if ($this->render) {
            return new ViewModel($this->getParamsForView());
        }
    }

    public function newAction() {
        $this->formData = new $this->form(NULL, $this->getEm());

        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->formData->setData($request->getPost());
            if ($this->formData->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->insert($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        if ($this->render) {
            return new ViewModel($this->getParamsForView());
        }
    }

    public function editAction() {
        $this->formData = new $this->form(NULL, $this->getEm());
        $request = $this->getRequest();

        $repository = $this->getEm()->getRepository($this->entity);
        $entity = $repository->find($this->params()->fromRoute('id', 0));

        if ($this->params()->fromRoute('id', 0)) {
            $this->formData->setData($entity->toArray());
        }

        if ($request->isPost()) {
            $this->formData->setData($request->getPost());
            if ($this->formData->isValid()) {
                $service = $this->getServiceLocator()->get($this->service);
                $service->update($request->getPost()->toArray());

                return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
            }
        }
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        if ($this->render) {
            return new ViewModel($this->getParamsForView());
        }
    }

    public function deleteAction() {
        $service = new $this->service($this->getEm());
        
        if ($this->params()->fromRoute('id', 0)) {
            $data['id'] = $this->params()->fromRoute('id', 0);
        } else {
            $data = $this->getRequest()->getPost()->toArray();
        }

        $result = $service->delete($data['id'],$data);
        if ($result === TRUE){
            return $this->redirect()->toRoute($this->route, array('controller' => $this->controller));
        }
        foreach ($result as $value) {
            $this->flashMessenger()->addMessage($value);
        }
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEm() {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }
    
    /**
     * Setar o controller para retorna ou não um view para tela
     * @param boolean $render default TRUE
     */
    public function setRender($render) {
        $this->render = $render;
    }

    /**
     * Junta os paramentros basicos para as actions new ou edit
     * @return array 
     */
    public function getParamsForView() {
        if($this->formData){
            $viewData['form'] = $this->formData;
            $viewData['formName'] = $this->formData->getName();
        }
        $viewData['data'] = $this->paginator ;
        $viewData['page'] = $this->page;
        if($this->route2){
            $viewData['route'] = $this->route2;
            $viewData['params'] = $this->route2->getParams();
            $viewData['matchedRouteName'] = $this->route2->getMatchedRouteName();
        }
        $viewData['flashMessages']    = $this->flashMessenger()->getCurrentMessages();
        return $viewData;    
    }
    
   /**
     * Metodo usado para converte os prefix do form html em array simples
     * Necessario para validaçao dos filters em forms que tem dependencia de outros forms
     * Percorre todos os itens do post desmontando array em itens comuns do post
     *
     * @author Paulo Watakabe <watakabe05@gmail.com>
     * @version 1.0
     * @param array multidimencional $data
     * @return array simples
     */
    public function getFormatFormData(array $data) {
        $newData = [];
        $this->extractData($newData, $data);
        return $newData;
    }

    /**
     * Metodo resursivo para transformar array multidimensional em array simples baseado em seus prefix
     *
     * @author Paulo Watakabe <watakabe05@gmail.com>
     * @version 1.0
     * @param array $newData
     * @param array $data
     * @param string $prefix
     */
    public function extractData(&$newData, $data, $prefix = '') {
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                if (empty($prefix)) {
                    $newData[$key] = $value;
                } else {
                    $newData[$prefix . '[' . $key . ']'] = $value;
                }
                continue;
            }
            if (empty($prefix)) {
                $pf = $key;
            } else {
                $pf = $prefix . '[' . $key . ']';
            }
            $this->extractData($newData, $value, $pf);
        }
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
        $subOpcao = $this->getRequest()->getPost('subOpcao','');
        $autoComp = $this->getRequest()->getPost('autoComp');
        $param = trim($this->getRequest()->getPost($autoComp,''));
        $repository = $this->getEm()->getRepository($this->entity);
        $resultSet = $repository->autoComp($param .'%', $this->getIdentidade());
        if (!$resultSet) {// Caso não encontre nada ele tenta pesquisar em toda a string
            $resultSet = $repository->autoComp('%' . $param . '%');
        }
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('resultSet' => $resultSet, 'subOpcao'=>$subOpcao));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
 
    /** 
     * Busca os dados do usuario da storage session
     * Retorna a entity com os dados do usuario
     * @param Array $data com os campos do registro
     * @return \Livraria\Entity\User | boolean
     */     
    public function getIdentidade() { 
        if (is_object($this->authService)) {
            return $this->authService->getIdentity();
        }else{
            $sessionStorage = new SessionStorage("LivrariaAdmin");
            $this->authService = new AuthenticationService;
            $this->authService->setStorage($sessionStorage);
            if ($this->authService->hasIdentity()) {
                return $this->authService->getIdentity();
            }
        }
        return FALSE;
    }
    
    /**
     * Verifica se usuario é do tipo admin se não for redireciona para tela de login
     * @return \Livraria\Entity\User 
     */
    public function verificaSeUserAdmin(){
        $user = $this->getIdentidade();
        if ($user->getTipo() != 'admin') {
            return $this->redirect()->toRoute($this->route, array('controller' => 'auth'));
        }
        return $user;
    }
    
    public function filtrosDaPaginacao(){
        //Guardar dados dos filtros para paginação
        $this->sc = new SessionContainer("LivrariaAdmin");
        $post = $this->getRequest()->isPost();
        if(is_int($this->params()->fromRoute('page')) AND $post){
            $data = $this->getRequest()->getPost()->toArray();
            if($this->getIdentidade()->getTipo() == 'admin'){
                $this->configParamsAdm($data);
            }
//            $this->setDatePeriodo($data);
            $this->sc->data = $data;
        }
        if (is_array($this->sc->data)){
            return $this->sc->data;            
        }else{
            return [];
        }
    }

    public function configParamsAdm(&$data) {
        if (!isset($data['administradora']) OR empty($data['administradora'])){
            $this->getDefaultAdm($data);
        }else{
            $this->setDefaultAdm($data);            
        }        
    }       

    public function getDefaultAdm(&$data) {
        //Verifica se esta registrado a administradora na sessao
        if(is_null($this->sc->administradora)){
            $this->sc->administradora = ['id' => '3234', 'nome' => 'LELLO LOCA'];
        }           
        $data['administradora']     = $this->sc->administradora['id'];
        $data['administradoraDesc'] = $this->sc->administradora['nome'];
    }

    public function setDefaultAdm(&$data) {
        if(is_null($this->sc->administradora)){
            $this->sc->administradora = ['id' => $data['administradora'], 'nome' => $data['administradoraDesc']];
        }
        if($this->sc->administradora['id'] != $data['administradora']){
            $this->sc->administradora = ['id' => $data['administradora'], 'nome' => $data['administradoraDesc']];
        }
    }

    public function setDatePeriodo(&$data) {
        if(isset($data['dataI'])){
            return ;
        }
        $dataAgora = new \DateTime('now');
        $data['dataI'] = '01/' . $dataAgora->format('m/Y');  
        $data['dataF'] = $dataAgora->format('d/m/Y');  
    }

}
