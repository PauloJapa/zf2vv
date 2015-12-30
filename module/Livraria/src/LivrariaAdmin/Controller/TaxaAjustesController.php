<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * TaxaAjuste
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class TaxaAjustesController extends CrudController {
    
    public function __construct() {
        $this->entity = "Livraria\Entity\TaxaAjuste";
        $this->form = "LivrariaAdmin\Form\TaxaAjuste";
        $this->service = "Livraria\Service\TaxaAjuste";
        $this->controller = "taxaAjustes";
        $this->route = "livraria-admin";
        
    }
    
    public function indexAction(array $filtro = array('status' => 'A')){
        $this->verificaSeUserAdmin();
        $orderBy = ['seguradora'=>'ASC','administradora'=>'ASC','ocupacao'=>'ASC','classe'=>'ASC','validade'=>'ASC','inicio' => 'DESC'];
        if(!$this->render){
            return parent::indexAction($filtro,$orderBy);
        }
//        $data = $this->filtrosDaPaginacao();
//        $this->formData = new \LivrariaAdmin\Form\Filtros([],  $this->getEm());
//        $this->formData->setTaxaAjustes();
//        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
//            return parent::indexAction(['status'=>'A'], $orderBy);
//        }
//        $this->formData->setData($data);
        $filtro=[];
//        $campos = ['seguradora','classe','administradora','validade','ocupacao','status'];
//        foreach ($data as $key => $value) {            
//            if(!empty($value) AND in_array($key, $campos))
//                $filtro[$key] = $value;
//        }
        return parent::indexAction($filtro,$orderBy);
    }

    public function newAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
//            if(!empty($data['classe']))    $filtro['classe']     = $data['classe'];
//            if(!empty($data['seguradora']))$filtro['seguradora'] = $data['seguradora'];
//            if(!empty($data['ocupacao']))  $filtro['ocupacao']   = $data['ocupacao'];
//            if(!empty($data['validade']))   $filtro['validade']   = $data['validade'];
            $this->formData->setData($this->getFormatFormData($data));
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
            }else{
                echo '<pre>isvalid false ', var_dump($this->formData->getMessages()), '</pre>';
            }
        }
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        
        return new ViewModel($this->getParamsForView()); 
    }

    public function editAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form(null, $this->getEm());
        $this->formData->setEdit($this->getIdentidade()->getIsAdmin());
        $this->formData->setEditDisabled();
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        switch ($data['subOpcao']){
        case 'editar':    
            $data = $this->getEm()->getRepository($this->entity)->getData($data['id']);
//            $filtro['seguradora']     = $entity->getSeguradora()->getId();
//            $filtro['classe']         = $entity->getClasse()->getId();
//            $filtro['administradora'] = $entity->getAdministradora();
//            $filtro['validade']       = $entity->getValidade();
            $this->formData->setData($data);
            break;
//        case 'buscar':  
//            if(!empty($data['classe']))           $filtro['classe']          = $data['classe'];
//            if(!empty($data['seguradora']))       $filtro['seguradora']      = $data['seguradora'];
//            if(!empty($data['ocupacao']))         $filtro['ocupacao']        = $data['ocupacao'];
//            if(!empty($data['administradora']))   $filtro['administradora']  = $data['administradora'];
//            if(!empty($data['validade']))         $filtro['validade']        = $data['validade'];
//            $this->formData->setData($data);  
//            break;
        case 'salvar': 
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
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch(); 
            
//        $this->setRender(FALSE);
//        $this->indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }
    
}
