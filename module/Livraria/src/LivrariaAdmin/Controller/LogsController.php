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
        $this->verificaSeUserAdmin();
        return parent::indexAction($filtro,array('data' => 'DESC'));
    }
    
    public function logOrcamentoAction($filtro=[], $operadores=[]){
        $data = $this->getRequest()->getPost()->toArray();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setOrcamento();
        if(isset($data['proposta']))$data['orcamento'] = $data['proposta'];
        $this->formData->setData($data);
        $inputs = ['orcamento', 'user','dataI','dataF'];
        foreach ($inputs as $input) {
            if ((isset($data[$input])) AND (!empty($data[$input]))) {
                $filtro[$input] = $data[$input];
            }
        }
        $this->verificaSeUserAdmin();
        $list = $this->getEm()
                     ->getRepository($this->entityOrc)
                     ->findLogOrcamento($filtro,$operadores);
        
        if(empty($list))$list[0] = FALSE;
        
        return parent::indexAction([],[],$list);
    }
    
    public function logFechadosAction($filtro=[], $operadores=[]){
        $data = $this->getRequest()->getPost()->toArray();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setFechados();
        if(isset($data['proposta']))$data['fechados'] = $data['proposta'];
        $this->formData->setData($data);
        $inputs = ['fechados', 'user','dataI','dataF'];
        foreach ($inputs as $input) {
            if ((isset($data[$input])) AND (!empty($data[$input]))) {
                $filtro[$input] = $data[$input];
            }
        }
        $this->verificaSeUserAdmin();
        $list = $this->getEm()
                     ->getRepository($this->entityFec)
                     ->findLogFechados($filtro,$operadores);
        
        if(empty($list))$list[0] = FALSE;
        
        return parent::indexAction([],[],$list);
    }
    
    public function logRenovacaoAction($filtro=[], $operadores=[]){
        $data = $this->getRequest()->getPost()->toArray();
        $this->formData = new \LivrariaAdmin\Form\Filtros();
        $this->formData->setRenovacao();
        if(isset($data['proposta']))$data['renovacao'] = $data['proposta'];
        $this->formData->setData($data);
        $inputs = ['renovacao', 'user','dataI','dataF'];
        foreach ($inputs as $input) {
            if ((isset($data[$input])) AND (!empty($data[$input]))) {
                $filtro[$input] = $data[$input];
            }
        }
        $this->verificaSeUserAdmin();
        $list = $this->getEm()
                     ->getRepository($this->entityRen)
                     ->findLogRenovacao($filtro,$operadores);
        
        if(empty($list))$list[0] = FALSE;
        
        return parent::indexAction([],[],$list);
    }

    //Não usa não se inclui registro log pelo front-end
    public function newAction() {
        $this->verificaSeUserAdmin();
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

    //Não usa não se edita registro de log
    public function editAction() {
        $this->verificaSeUserAdmin();
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
