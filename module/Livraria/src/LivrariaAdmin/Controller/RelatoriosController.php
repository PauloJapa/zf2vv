<?php

namespace LivrariaAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;

require '/var/www/zf2vv/module/Livraria/src/Livraria/Service/PHPExcel.php';

class RelatoriosController extends CrudController {

    public function __construct() {
        $this->verificaSeUserAdmin();
        $this->entity = "Livraria\Entity\Orcamento";
        $this->form = "LivrariaAdmin\Form\Relatorio";
        $this->service = "Livraria\Service\Relatorio";
        $this->controller = "relatorios";
        $this->route = "livraria-admin";
    }
    
    public function queryAction() {
        $this->formData = new $this->form($this->getEm());
        $this->formData->setQuery();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());
    }
    
    public function gerarQueryAction(){
        $label['c1']  = "Vigência Inicio";       
        $label['c2']  = "Vigência Fim";        
        $label['c3']  = "Locador";             
        $label['c4']  = "Tipo";                
        $label['c5']  = "CPF";                 
        $label['c6']  = "CNPJ";                
        $label['c7']  = "Locatario";           
        $label['c8']  = "Tipo";                
        $label['c9']  = "CPF";                 
        $label['c10'] = "CNPJ";                
        $label['c11'] = "Endereço";            
        $label['c12'] = "Num";                 
        $label['c13'] = "Bairro";              
        $label['c14'] = "Cidade";              
        $label['c15'] = "Seg em Nome";         
        $label['c16'] = "Incêndio";            
        $label['c17'] = "Perda Aluguel";       
        $label['c18'] = "Eletrico";            
        $label['c19'] = "Vendaval";            
        $label['c20'] = "Valor Aluguel";       
        $label['c21'] = "Atividade";           
        $label['c22'] = "Premio liquido";       
        $label['c23'] = "Premio Total";        
        $label['c24'] = "Fechado";             
        $label['c25'] = "Segurado";            
        $label['c26'] = "Status";              
        $label['c27'] = "UE";                  
        $label['c28'] = "Ref. Imovel";         
        $label['c29'] = "Mês Niver"; 
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->montaQuery($data);
        //Guardar dados dos registro para montar planilha excel
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->montaquery = $this->paginator;
        $sc->label      = $label;
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel(array_merge(['label' => $label], $this->getParamsForView()));
    }
    
    public function toExcelAction(){
        //ler Dados do cacheado da ultima consulta.
        $sc = new SessionContainer("LivrariaAdmin");
        $excel = new  \PHPExcel();
        
        // instancia uma view sem o layout da tela
        $viewModel = new ViewModel(array('data' => $sc->montaquery, 'label' => $sc->label, 'excel' => $excel));
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    public function orcarenoAction() {
        $this->verificaSeUserAdmin();
        //Pegar os parametros que em de post
        $this->data = $this->getRequest()->getPost()->toArray();
        if ((isset($this->data['subOpcao']))&&($this->data['subOpcao'] == 'buscar'))  {
            $sessionContainer = new SessionContainer("LivrariaAdmin");
            $sessionContainer->data = $this->data;
        }
        $this->formData = new \LivrariaAdmin\Form\Renovacao();
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());
    }
    
    public function gerarOrcarenoAction(){
        $data = $this->getRequest()->getPost()->toArray();
        $service = new $this->service($this->getEm());
        $this->paginator = $service->orcareno($data);
        //Guardar dados do resultado 
        $sc = new SessionContainer("LivrariaAdmin");
        $sc->dataOrcareno = $this->paginator;
        // Pegar a rota atual do controler
        $this->route2 = $this->getEvent()->getRouteMatch();
        return new ViewModel($this->getParamsForView());        
    }
    
    public function sendEmailAction(){
        
        echo 'buuu';
    }
    
    public function printPropostaAction(){
        //Pegar os parametros que em de post
        $data = $this->getRequest()->getPost()->toArray();
        echo $data['subOpcao'];
                
        //Ler dados guardados
        $sc = new SessionContainer("LivrariaAdmin");
        if(!empty($sc->dataOrcareno))
            return;

        $admCod = $data['subOpcao'];
        $srvOrcamento = $this->getServiceLocator()->get("Livraria\Service\Orcamento");
        $srvRenovacao = $this->getServiceLocator()->get("Livraria\Service\Renovacao");
        foreach($sc->dataOrcareno as $arrayResul){
            if(!empty($admCod) AND $admCod != $arrayResul['administradora']['id']){
                continue;
            }
            // Imprimi orçamento ou renovação
            if ( isset($arrayResul['fechadoOrigemId'])){
                $renov ++; $totRenov ++;
            }else{
                $orcam ++; $totOrcam ++;
            }
        }    
    }
    
}
