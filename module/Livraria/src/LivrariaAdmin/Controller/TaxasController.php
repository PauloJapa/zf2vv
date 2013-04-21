<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
/**
 * Taxa
 * Recebe requisição e direciona para a ação responsavel depois de validar.
 * @author Paulo Cordeiro Watakabe <watakabe05@gmail.com>
 */
class TaxasController extends CrudController {
    
    public function __construct() {
        $this->entity = "Livraria\Entity\Taxa";
        $this->form = "LivrariaAdmin\Form\Taxa";
        $this->service = "Livraria\Service\Taxa";
        $this->controller = "taxas";
        $this->route = "livraria-admin";
        
    }
    
    public function indexAction(array $filtro = array('status' => 'A')){
        $this->verificaSeUserAdmin();
        $orderBy = ['seguradora'=>'ASC','classe'=>'ASC','comissao'=>'ASC','ocupacao'=>'ASC','validade'=>'ASC','inicio' => 'DESC'];
        if(!$this->render){
            return parent::indexAction($filtro,$orderBy);
        }
        $data = $this->filtrosDaPaginacao();
        $this->formData = new \LivrariaAdmin\Form\Filtros([],  $this->getEm());
        $this->formData->setTaxas();
        if((!isset($data['subOpcao']))or(empty($data['subOpcao']))){
            return parent::indexAction(['status'=>'A'], $orderBy);
        }
        $this->formData->setData($data);
        $filtro=[];
        $campos = ['seguradora','classe','comissao','validade','ocupacao','status'];
        foreach ($data as $key => $value) {            
            if(!empty($value) AND in_array($key, $campos))
                $filtro[$key] = $value;
        }
        
        return parent::indexAction($filtro,$orderBy);
    }

    public function newAction() {
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form(null, $this->getEm());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        if(($data['subOpcao'] == 'salvar') or ($data['subOpcao'] == 'buscar')){
            if(!empty($data['classe']))    $filtro['classe']     = $data['classe'];
            if(!empty($data['seguradora']))$filtro['seguradora'] = $data['seguradora'];
            if(!empty($data['ocupacao']))  $filtro['ocupacao']   = $data['ocupacao'];
            if(!empty($data['comissao']))   $filtro['comissao']   = $data['comissao'];
            if(!empty($data['validade']))   $filtro['validade']   = $data['validade'];
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
        $this->verificaSeUserAdmin();
        $this->formData = new $this->form(null, $this->getEm());
        $this->formData->setEdit($this->getIdentidade()->getIsAdmin());
        $data = $this->getRequest()->getPost()->toArray();
        $filtro = array();
        if(!isset($data['subOpcao']))$data['subOpcao'] = '';
        
        switch ($data['subOpcao']){
        case 'editar':    
            $entity = $this->getEm()->find($this->entity,$data['id']);
            $filtro['seguradora'] = $entity->getSeguradora()->getId();
            $filtro['classe']     = $entity->getClasse()->getId();
            $filtro['ocupacao']   = $entity->getOcupacao();
            $filtro['comissao']   = $entity->floatToStr('Comissao');
            $filtro['validade']   = $entity->getValidade();
            $this->formData->setData($entity->toArray());
            break;
        case 'buscar':  
            if(!empty($data['classe']))     $filtro['classe']     = $data['classe'];
            if(!empty($data['seguradora'])) $filtro['seguradora'] = $data['seguradora'];
            if(!empty($data['ocupacao']))   $filtro['ocupacao']   = $data['ocupacao'];
            if(!empty($data['comissao']))   $filtro['comissao']   = $data['comissao'];
            if(!empty($data['validade']))   $filtro['validade']   = $data['validade'];
            $this->formData->setData($data);  
            break;
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
            
        $this->setRender(FALSE);
        $this->indexAction($filtro);

        return new ViewModel($this->getParamsForView()); 
    }
    
    public function importarAction(){
        $this->verificaSeUserAdmin();
        $data = $this->getRequest()->getFiles()->toArray();
        //Verificando a existencia do arquivo
        $content  = file($data['content']['tmp_name']);
        if(!$content){
            echo 'arquivo não encontrado!!';
            return;
        }
        // Pegando o serviço para manipular dados
        $service = $this->getServiceLocator()->get($this->service);  
        foreach ($content as $key => $value) {
            if($key == 0){
                if(!$this->validaColunas($this->csvToArray($value))){
                    echo 'Erro titulos da colunas estão incorretos!!';
                    return;
                }
                continue;
            }
         //   $resul = $service->insert($this->getDataCs($value));
         //   if($resul === TRUE){
         //       echo 'Importado Comercio; ', $value , '<br>';
         //   }
            $resul = $service->insert($this->getDataInd($value));
            if($resul === TRUE){
                echo 'Importado Industria; ', $value , '<br>';
                continue;
            }
            var_dump($resul);
        }        
    }
    
    public function getDataCs($value){
        $d = $this->csvToArray($value);
        return [
            'id' => '',
            'seguradora' => '3',
            'ocupacao' => '01',
            'classe' => $d[1],
            'incendio' => $d[3],
            'incendioConteudo' => $d[4],
            'aluguel' => $d[5],
            'eletrico' => $d[6],
            'vendaval' => $d[7],
            'inicio' => $d[13],
            'fim' => $d[14],
            'seq' => $d[15],
            'comissao' => $d[16] . ',00',
            'validade' => $d[17],
            'status' => ($d[14] == 'vigente') ? 'A' : 'C'
        ];
    }
    
    public function getDataInd($value){
        $d = $this->csvToArray($value);
        return [
            'id' => '',
            'seguradora' => '3',
            'ocupacao' => '03',
            'classe' => $d[1],
            'incendio' => $d[8],
            'incendioConteudo' => $d[9],
            'aluguel' => $d[10],
            'eletrico' => $d[11],
            'vendaval' => $d[12],
            'inicio' => $d[13],
            'fim' => $d[14],
            'seq' => $d[15],
            'comissao' => $d[16] . ',00',
            'validade' => $d[17],
            'status' => ($d[14] == 'vigente') ? 'A' : 'C'
        ];
    }

    public function validaColunas($cols){
        $titStr = 'cod;classe;descricao;taxinc;taxinc_con;taxalu;taxele;taxven;ind_taxinc;ind_taxinc_con;ind_taxalu;ind_taxele;ind_taxven;inicio;fim;sequencia;comissao;validade';
        $tit = explode(';', $titStr);
        if($tit !== $cols){
            var_dump($tit);
            var_dump($cols);
            return FALSE;
        }
        return TRUE;
    }
    
    public function csvToArray($str){
        $linha = str_replace("\r\n","",trim($str));
        return explode(';',  $linha);
    }

}
