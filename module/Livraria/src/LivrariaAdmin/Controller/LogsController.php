<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator,
    Zend\Paginator\Adapter\ArrayAdapter;
/**
 * Log
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class LogsController extends CrudController {

    /**
     * Contem o caminho para Entity
     * @var string 
     */
    protected $entityOrc;
    
    /**
     * Contem o caminho para Entity
     * @var string 
     */
    protected $entityFec;
    
    /**
     * Contem o caminho para Entity
     * @var string 
     */
    protected $entityRen;
    
    /**
     * Contem o caminho para o Serviço da Entity
     * @var string 
     */
    protected $serviceOrc;
    
    /**
     * Contem o caminho para o Serviço da Entity
     * @var string 
     */
    protected $serviceFec;
    
    /**
     * Contem o caminho para o Serviço da Entity
     * @var string 
     */
    protected $serviceRen;
    
    public function __construct() {
        $this->entity     = "Livraria\Entity\Log";
        $this->entityOrc  = "Livraria\Entity\LogOrcamento";
        $this->entityFec  = "Livraria\Entity\LogFechados";
        $this->entityRen  = "Livraria\Entity\LogRenovacao";
        $this->form       = "LivrariaAdmin\Form\Log";
        $this->service    = "Livraria\Service\Log";
        $this->serviceOrc = "Livraria\Service\LogOrcamento";
        $this->serviceFec = "Livraria\Service\LogFechados";
        $this->serviceRen = "Livraria\Service\LogRenovacao";
        $this->controller = "logs";
        $this->route      = "livraria-admin";
    }
    
    public function indexAction(array $filtro = array()){
        return parent::indexAction($filtro,array('data' => 'DESC'));
    }
    
    public function logOrcamentoAction($filtro=[]){
        $list = $this->getEm()
                     ->getRepository($this->entityOrc)
                     ->findLogOrcamento($filtro);
        
        $this->page = $this->params()->fromRoute('page');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        $this->paginator = new Paginator(new ArrayAdapter($list));
        $this->paginator->setCurrentPageNumber($this->page);
        $this->paginator->setDefaultItemCountPerPage(20);
        $this->paginator->setPageRange(15);
        if($this->render)
            return new ViewModel($this->getParamsForView());
    }
    
    public function logFechadosAction($filtro=[]){
        $list = $this->getEm()
                     ->getRepository($this->entityFec)
                     ->findLogFechados($filtro);
        
        $this->page = $this->params()->fromRoute('page');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        $this->paginator = new Paginator(new ArrayAdapter($list));
        $this->paginator->setCurrentPageNumber($this->page);
        $this->paginator->setDefaultItemCountPerPage(20);
        $this->paginator->setPageRange(15);
        if($this->render)
            return new ViewModel($this->getParamsForView());
    }
    
    public function logRenovacaoAction($filtro=[]){
        $list = $this->getEm()
                     ->getRepository($this->entityRen)
                     ->findLogRenovacao($filtro);
        
        $this->page = $this->params()->fromRoute('page');
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();

        $this->paginator = new Paginator(new ArrayAdapter($list));
        $this->paginator->setCurrentPageNumber($this->page);
        $this->paginator->setDefaultItemCountPerPage(20);
        $this->paginator->setPageRange(15);
        if($this->render)
            return new ViewModel($this->getParamsForView());
    }

    public function newAction() {
        $this->formData = new $this->form();
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['user']))    $filtro['user']     = $data['user'];
            $this->formData->setData($data);
        }
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
        
        $this->setRender(FALSE);
        $this->indexAction($filtro);
        
        return new ViewModel($this->getParamsForView()); 
    }

    public function editAction() {
        $this->formData = new $this->form();
        $this->formData->setEdit();
        $data = $this->getRequest()->getPost()->toArray();
        $repository = $this->getEm()->getRepository($this->entity);
        $filtro = array();
        
        switch ($data['subOpcao']){
        case 'editar':    
            $entity = $repository->find($data['id']);
            $filtro['user'] = $entity->getUser()->getId();
            $this->formData->setData($entity->toArray());
            break;
        case 'buscar':  
            if(!empty($data['user']))    
                $filtro['user']     = $data['user'];
            $this->formData->setData($data);  
            break;
        case 'salvar': 
            if(!empty($data['user']))    
                $filtro['user']     = $data['user'];
            $this->formData->setData($data);
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
            break;
        }
            
        $this->setRender(FALSE);
        $this->indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }

}
